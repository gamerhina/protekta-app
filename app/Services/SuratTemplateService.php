<?php

namespace App\Services;

use App\Models\Surat;
use App\Models\SuratTemplate;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratTemplateService
{
    public function generateDocx(SuratTemplate $template, Surat $surat, string $outputPath): void
    {
        $processor = new TemplateProcessor($this->getTemplateFilePath($template));

        $fields = $this->buildFields($surat);
        $mappings = is_array($template->tag_mappings) ? $template->tag_mappings : [];

        foreach ((array) ($template->available_tags ?? []) as $tag) {
            $mapped = $mappings[$tag] ?? null;
            if (!$mapped) {
                $processor->setValue($tag, '');
                continue;
            }

            $value = $fields[$mapped] ?? '';
            $processor->setValue($tag, is_scalar($value) ? (string) $value : '');
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
            $base[$k] = is_scalar($v) ? (string) $v : '';
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

    private function getTemplateFilePath(SuratTemplate $template): string
    {
        return storage_path('app/private/' . ltrim($template->file_path, '/'));
    }
}
