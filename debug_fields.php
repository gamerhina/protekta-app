<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
use App\Models\SuratJenis;
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$items = SuratJenis::all()->map(function($j) {
    return [
        'id' => $j->id,
        'nama' => $j->nama,
        'form_fields' => $j->form_fields
    ];
});
echo json_encode($items, JSON_PRETTY_PRINT);
