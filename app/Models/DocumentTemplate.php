<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ZipArchive;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'seminar_jenis_id',
        'file_path',
        'keterangan',
        'mapping_fields',
        'available_tags',
        'tag_mappings',
        'tag_types',
        'tag_properties',
        'email_subject_template',
        'email_body_template',
        'aktif',
    ];

    protected $casts = [
        'mapping_fields' => 'array',
        'available_tags' => 'array',
        'tag_mappings' => 'array',
        'tag_types' => 'array',
        'tag_properties' => 'array',
        'aktif' => 'boolean',
    ];

    public function seminarJenis()
    {
        return $this->belongsTo(SeminarJenis::class, 'seminar_jenis_id');
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

        $rawTags = array_filter($rawTags);
        $rawTags = array_unique($rawTags);
        sort($rawTags);

        return $rawTags;
    }

    public static function getAvailableFields(): array
    {
        return [
            'Mahasiswa' => [
                'mahasiswa_nama' => 'Nama Mahasiswa',
                'mahasiswa_npm' => 'NPM Mahasiswa',
                'mahasiswa_prodi' => 'Program Studi',
                'mahasiswa_email' => 'Email Mahasiswa',
                'mahasiswa_no_hp' => 'No HP Mahasiswa',
            ],
            'Seminar' => [
                'seminar_no_surat' => 'Nomor Surat',
                'seminar_judul' => 'Judul Seminar/Skripsi',
                'seminar_tanggal' => 'Tanggal Seminar (DD MMMM YYYY)',
                'seminar_tahun' => 'Tahun Seminar',
                'seminar_hari' => 'Hari Seminar',
                'seminar_waktu_mulai' => 'Waktu Mulai',
                'seminar_lokasi' => 'Lokasi Seminar',
                'seminar_status' => 'Status Seminar',
                'seminar_jenis_nama' => 'Jenis Seminar',
            ],
            'Dosen' => [
                'p1_nama' => 'Nama Pembimbing Utama',
                'p1_nip' => 'NIP Pembimbing Utama',
                'p1_email' => 'Email Pembimbing Utama',
                'p1_ttd' => 'Tanda Tangan Pembimbing Utama (Image)',
                'p2_nama' => 'Nama Pembimbing Kedua',
                'p2_nip' => 'NIP Pembimbing Kedua',
                'p2_email' => 'Email Pembimbing Kedua',
                'p2_ttd' => 'Tanda Tangan Pembimbing Kedua (Image)',
                'pembahas_nama' => 'Nama Pembahas',
                'pembahas_nip' => 'NIP Pembahas',
                'pembahas_email' => 'Email Pembahas',
                'pembahas_ttd' => 'Tanda Tangan Pembahas (Image)',
            ],
            'Nilai' => [
                'nilai_akhir' => 'Nilai akhir (angka)',
                'nilai_huruf' => 'Nilai huruf',
                'nilai_p1' => 'Nilai Pembimbing Utama',
                'nilai_p2' => 'Nilai Pembimbing Kedua',
                'nilai_pembahas' => 'Nilai Pembahas',
                'nilai_bobot' => 'Nilai akhir berbobot',
                'nilai_akhir_terbilang' => 'Nilai akhir dalam terbilang',
                'bbtp1' => 'Bobot Pembimbing 1 (%)',
                'bbtp2' => 'Bobot Pembimbing 2 (%)',
                'bbtp3' => 'Bobot Pembahas (%)',
                'nialip1xbbt1' => 'Nilai Pembimbing 1 x Bobot',
                'nilaip2xbbt2' => 'Nilai Pembimbing 2 x Bobot',
                'nilaipmbxbbtpmb' => 'Nilai Pembahas x Bobot',
                'dinyatakan' => 'Status kelulusan (Lulus/Tidak Lulus)',
                'diperkenankan' => 'Status perkenan (Diperkenankan/Tidak Diperkenankan)',
            ],
        ];
    }
}
