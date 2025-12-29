<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_jenis_id',
        'pemohon_type',
        'pemohon_dosen_id',
        'pemohon_mahasiswa_id',
        'mahasiswa_id',
        'untuk_type',
        'no_surat',
        'tanggal_surat',
        'tujuan',
        'perihal',
        'isi',
        'data',
        'penerima_email',
        'status',
        'generated_file_path',
        'sent_at',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'sent_at' => 'datetime',
        'data' => 'array',
    ];

    public function jenis()
    {
        return $this->belongsTo(SuratJenis::class, 'surat_jenis_id');
    }

    public function pemohonDosen()
    {
        return $this->belongsTo(Dosen::class, 'pemohon_dosen_id');
    }

    public function pemohonMahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'pemohon_mahasiswa_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
