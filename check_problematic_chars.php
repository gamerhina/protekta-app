<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$jenisList = App\Models\SuratJenis::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'kode', 'form_fields']);

echo "Checking for problematic characters in form_fields..." . PHP_EOL;

foreach ($jenisList as $jenis) {
    $json = json_encode($jenis->form_fields);
    
    if (str_contains($json, '</script>')) {
        echo "FOUND </script> in {$jenis->nama}!" . PHP_EOL;
    }
    
    if (str_contains($json, '<script')) {
        echo "FOUND <script in {$jenis->nama}!" . PHP_EOL;
    }
    
    // Check for other problematic patterns
    if (preg_match('/<\/[a-z]+>/i', $json)) {
        echo "FOUND HTML closing tag in {$jenis->nama}: " . substr($json, 0, 200) . PHP_EOL;
    }
}

echo PHP_EOL . "Done checking." . PHP_EOL;
