<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\SuratJenis::all() as $j) {
    echo "[$j->id] $j->nama:\n";
    foreach ((array) $j->form_fields as $f) {
        $key = $f['key'] ?? '';
        $type = $f['type'] ?? '';
        echo "  - $key ($type)\n";
        if ($type === 'table') {
            foreach ((array) ($f['columns'] ?? []) as $col) {
                $ck = $col['key'] ?? '';
                $ct = $col['type'] ?? '';
                echo "    + $ck ($ct)\n";
            }
        }
    }
    echo "\n";
}
