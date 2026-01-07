<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Surat;
use App\Models\SuratTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratTemplateService
{
    public function generateDocx(SuratTemplate $template, Surat $surat, string $outputPath): void
    {
        $processor = new TemplateProcessor($this->getTemplateFilePath($template));

        $fields = $this->buildFields($surat);
        $mappings = is_array($template->tag_mappings) ? $template->tag_mappings : [];
        $types = is_array($template->tag_types) ? $template->tag_types : [];
        $props = is_array($template->tag_properties) ? $template->tag_properties : [];
        $availableTags = is_array($template->available_tags) ? $template->available_tags : [];

        // Identify table keys from SuratJenis
        $formFields = is_array($surat->jenis?->form_fields) ? $surat->jenis->form_fields : [];
        $tableKeys = [];
        foreach ($formFields as $f) {
            if (isset($f['type']) && $f['type'] === 'table' && !empty($f['key'])) {
                $tableKeys[] = $f['key'];
            }
        }

        // Group tags that belong to tables
        $tableGroups = []; // prefix => [[tag => '', column => ''], ...]
        $processedTags = [];

        foreach ($availableTags as $tag) {
            $mapped = $mappings[$tag] ?? $tag;
            if (str_contains($mapped, '.')) {
                $parts = explode('.', $mapped);
                $prefix = $parts[0];
                $column = $parts[1] ?? '';

                if (in_array($prefix, $tableKeys)) {
                    $tableGroups[$prefix][] = [
                        'tag' => $tag,
                        'column' => $column
                    ];
                    continue;
                }
            }
        }

        // Process Table Groups first using cloneRow
        foreach ($tableGroups as $prefix => $group) {
            $dataRows = $surat->data[$prefix] ?? [];
            if (!is_array($dataRows)) $dataRows = [];
            
            $rowCount = count($dataRows);
            if ($rowCount > 0) {
                // We pick the first tag in the group to serve as the anchor for cloneRow
                $anchorTag = $group[0]['tag'];
                try {
                    $processor->cloneRow($anchorTag, $rowCount);
                    
                    foreach ($dataRows as $index => $rowData) {
                        $rowNum = $index + 1;
                        foreach ($group as $gt) {
                            $tag = $gt['tag'];
                            $col = $gt['column'];
                            $val = $rowData[$col] ?? '';

                            // Automatically handle 'no' tag for row numbering
                            if ($col === 'no' && empty($val)) {
                                $val = $rowNum;
                            }

                            // Handle pemohon type column
                            if (is_array($val) && (isset($val['type']) || isset($val['id']))) {
                                $val = $this->resolvePemohonName($val, $col);
                            }

                            $processor->setValue($tag . '#' . $rowNum, is_scalar($val) ? (string) $val : '');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to cloneRow for tag {$anchorTag}", ['error' => $e->getMessage()]);
                }
            } else {
                // If no data, just clear the tags in the original template row
                foreach ($group as $gt) {
                    $processor->setValue($gt['tag'], '');
                }
            }

            // Mark these tags as processed so we don't try to fill them as standard tags
            foreach ($group as $gt) {
                $processedTags[] = $gt['tag'];
            }
        }

        // Process remaining standard tags
        foreach ($availableTags as $tag) {
            if (in_array($tag, $processedTags)) {
                continue;
            }

            $mapped = $mappings[$tag] ?? $tag;
            $value = $fields[$mapped] ?? '';
            $type = $types[$tag] ?? 'text';

            if ($type === 'image') {
                $this->applyImageValue($processor, $tag, is_string($value) ? $value : null, $props[$tag] ?? []);
            } else {
                $processor->setValue($tag, is_scalar($value) ? (string) $value : '');
            }
        }

        $processor->saveAs($outputPath);
    }

    public function buildFields(Surat $surat): array
    {
        $jenis = $surat->jenis;
        $dosen = $surat->pemohonDosen;
        $mhs = $surat->mahasiswa;

        $custom = is_array($surat->data) ? $surat->data : [];

        $tanggal = $surat->tanggal_surat ? Carbon::parse($surat->tanggal_surat) : now();
        $tanggalJakarta = $tanggal->timezone('Asia/Jakarta');

        $base = [
            'surat_no' => $surat->no_surat ?? '',
            'surat_tanggal' => $tanggalJakarta->translatedFormat('d F Y'),
            'surat_hari' => $tanggalJakarta->translatedFormat('l'),
            'surat_tahun' => $tanggalJakarta->translatedFormat('Y'),
            'surat_tujuan' => $surat->tujuan ?? '',
            'surat_perihal' => $surat->perihal ?? '',
            'surat_isi' => $surat->isi ?? '',
            'surat_email' => $surat->penerima_email ?? '',
            'surat_jenis_nama' => $jenis?->nama ?? '',

            'dosen_nama' => $dosen?->nama ?? '',
            'dosen_nip' => $dosen?->nip ?? '',
            'dosen_email' => $dosen?->email ?? '',

            'mahasiswa_nama' => $mhs?->nama ?? '',
            'mahasiswa_npm' => $mhs?->npm ?? '',
            'mahasiswa_prodi' => $mhs?->prodi ?? '',
            'mahasiswa_email' => $mhs?->email ?? '',
        ];

        // Include dynamic custom fields stored on surats.data
        foreach ($custom as $k => $v) {
            if (!is_string($k) || $k === '') {
                continue;
            }
            // Keep original value if it's a string (could be a path)
            $base[$k] = $v;
        }

        // Aliases for common/legacy tag keys (to ease mapping when templates reuse seminar-style tags)
        $aliases = [
            'nomor_surat' => $base['surat_no'],
            'surat_nomor' => $base['surat_no'],
            'tanggal_surat' => $base['surat_tanggal'],
            'tahun' => $base['surat_tahun'],
            'hari' => $base['surat_hari'],
            'tujuan' => $base['surat_tujuan'],
            'perihal' => $base['surat_perihal'],
            'isi' => $base['surat_isi'],

            // Mahasiswa aliases
            'nama' => $base['mahasiswa_nama'],
            'npm' => $base['mahasiswa_npm'],
            'prodi' => $base['mahasiswa_prodi'],
            'email' => $base['mahasiswa_email'],
        ];

        return array_merge($base, $aliases);
    }

    private function applyImageValue(TemplateProcessor $processor, string $tag, ?string $value, array $properties = []): void
    {
        $path = $this->resolveImagePath($value);

        if (!$path || !file_exists($path)) {
            $processor->setValue($tag, '');
            return;
        }

        // Validate image
        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            Log::warning('Invalid image file for SuratTemplate', ['path' => $path]);
            $processor->setValue($tag, '');
            return;
        }

        $width = (int) ($properties['width'] ?? $properties['image_width'] ?? 120);
        $height = (int) ($properties['height'] ?? $properties['image_height'] ?? 60);

        try {
            $processor->setImageValue($tag, [
                'path' => $path,
                'width' => $width,
                'height' => $height,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set image value in SuratTemplate', [
                'path' => $path,
                'error' => $e->getMessage(),
                'tag' => $tag,
            ]);
            $processor->setValue($tag, '');
        }
    }

    private function resolveImagePath(?string $value): ?string
    {
        if (!$value) return null;

        $possiblePaths = [
            $value,
            storage_path('app/' . ltrim($value, '/')),
            storage_path('app/public/' . ltrim($value, '/')),
            public_path('storage/' . ltrim($value, '/')),
            base_path('uploads/' . ltrim($value, '/')),
            public_path('uploads/' . ltrim($value, '/')),
        ];

        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolvePemohonName($val, string $columnKey = ''): string
    {
        $type = $val['type'] ?? '';
        $id = $val['id'] ?? '';

        if (!$type || !$id) return '';

        $person = null;
        if ($type === 'dosen') {
            $person = Dosen::find($id);
        } elseif ($type === 'mahasiswa') {
            $person = Mahasiswa::find($id);
        }

        if (!$person) return '';

        $colKey = strtolower($columnKey);
        if (str_contains($colKey, 'nip') || str_contains($colKey, 'npm')) {
            return ($type === 'dosen' ? $person->nip : $person->npm) ?? '';
        }

        return $person->nama ?? '';
    }

    private function getTemplateFilePath(SuratTemplate $template): string
    {
        return base_path('uploads/' . ltrim($template->file_path, '/'));
    }
}
