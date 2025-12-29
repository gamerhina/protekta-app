<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJenis extends Model
{
    use HasFactory;

    protected $table = 'surat_jenis';

    protected $fillable = [
        'nama',
        'kode',
        'keterangan',
        'form_fields',
        'template_id',
        'aktif',
        'allow_download',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'allow_download' => 'boolean',
        'template_id' => 'integer',
        'form_fields' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(SuratTemplate::class, 'template_id');
    }

    public function templates()
    {
        return $this->hasMany(SuratTemplate::class, 'surat_jenis_id');
    }

    public function surats()
    {
        return $this->hasMany(Surat::class, 'surat_jenis_id');
    }
}
