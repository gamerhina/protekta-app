<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ZipArchive;

class SuratTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_jenis_id',
        'nama',
        'file_path',
        'keterangan',
        'available_tags',
        'tag_mappings',
        'tag_types',
        'tag_properties',
        'email_subject_template',
        'email_body_template',
        'aktif',
    ];

    protected $casts = [
        'available_tags' => 'array',
        'tag_mappings' => 'array',
        'tag_types' => 'array',
        'tag_properties' => 'array',
        'aktif' => 'boolean',
    ];

    public function jenis()
    {
        return $this->belongsTo(SuratJenis::class, 'surat_jenis_id');
    }

    public static function extractTagsFromDocx(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $segments = [
            'word/document.xml',
            'word/header1.xml',
            'word/header2.xml',
            'word/header3.xml',
            'word/footer1.xml',
            'word/footer2.xml',
            'word/footer3.xml',
        ];

        $rawTags = [];

        foreach ($segments as $segment) {
            $content = $zip->getFromName($segment);
            if (!$content) {
                continue;
            }

            $fragments = [];
            $fragments[] = preg_replace('/<[^>]+>/', '', $content);

            if (preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $content, $matches) && !empty($matches[1])) {
                $fragments[] = implode('', $matches[1]);
            }

            foreach ($fragments as $text) {
                if (preg_match_all('/\$\{([^}]+)\}/u', $text, $tagMatches)) {
                    foreach ($tagMatches[1] as $tag) {
                        $clean = trim($tag);
                        if ($clean !== '') {
                            $rawTags[] = $clean;
                        }
                    }
                }
            }
        }

        $zip->close();

        $rawTags = array_values(array_unique(array_filter($rawTags)));
        sort($rawTags);

        return $rawTags;
    }

    public static function getAvailableFields(?SuratJenis $jenis = null): array
    {
        $fields = [
            'Surat' => [
                'surat_no' => 'Nomor Surat',
                'surat_tanggal' => 'Tanggal Surat (DD MMMM YYYY)',
                'surat_hari' => 'Hari (Indonesia)',
                'surat_tahun' => 'Tahun',
                'surat_tujuan' => 'Tujuan/Instansi',
                'surat_perihal' => 'Perihal',
                'surat_isi' => 'Isi/Keterangan',
                'surat_email' => 'Email Penerima',
                'surat_jenis_nama' => 'Jenis Surat',
            ],
        ];

        $allowMahasiswa = true;
        $allowDosen = true;
        $customFields = [];

        if ($jenis && is_array($jenis->form_fields)) {
            $allowMahasiswa = false;
            $allowDosen = false;

            foreach ($jenis->form_fields as $f) {
                if (!is_array($f)) continue;

                $type = (string) ($f['type'] ?? '');
                if ($type === 'pemohon') {
                    $sources = $f['pemohon_sources'] ?? $f['sources'] ?? ['mahasiswa', 'dosen'];
                    if (!is_array($sources)) {
                        $sources = ['mahasiswa', 'dosen'];
                    }
                    $allowMahasiswa = $allowMahasiswa || in_array('mahasiswa', $sources, true);
                    $allowDosen = $allowDosen || in_array('dosen', $sources, true);
                }

                $key = trim((string) ($f['key'] ?? ''));
                $label = trim((string) ($f['label'] ?? ''));
                if ($key === '' || $label === '') continue;

                // auto_no_surat maps to surat_no, pemohon uses built-in dosen/mahasiswa blocks
                if (in_array($type, ['auto_no_surat', 'pemohon'], true)) {
                    continue;
                }

                $customFields[$key] = $label;
            }
        }

        if ($allowDosen) {
            $fields['Pemohon (Dosen)'] = [
                'dosen_nama' => 'Nama Dosen',
                'dosen_nip' => 'NIP Dosen',
                'dosen_email' => 'Email Dosen',
            ];
        }

        if ($allowMahasiswa) {
            $fields['Mahasiswa'] = [
                'mahasiswa_nama' => 'Nama Mahasiswa',
                'mahasiswa_npm' => 'NPM Mahasiswa',
                'mahasiswa_prodi' => 'Program Studi',
                'mahasiswa_email' => 'Email Mahasiswa',
            ];
        }

        if (!empty($customFields)) {
            $fields['Custom Fields'] = $customFields;
        }

        return $fields;
    }
}
