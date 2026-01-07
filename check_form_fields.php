<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$jenis = App\Models\SuratJenis::where('aktif', true)->first();

if ($jenis) {
    echo "ID: " . $jenis->id . PHP_EOL;
    echo "Nama: " . $jenis->nama . PHP_EOL;
    echo "Kode: " . $jenis->kode . PHP_EOL;
    echo "Form Fields Type: " . gettype($jenis->form_fields) . PHP_EOL;
    echo "Form Fields: " . json_encode($jenis->form_fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo "Tidak ada jenis surat aktif ditemukan." . PHP_EOL;
}
