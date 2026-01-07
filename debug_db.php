<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function checkIndexes($table) {
    echo "--- Indexes for $table ---\n";
    try {
        $indexes = DB::select("SHOW INDEX FROM $table");
        foreach ($indexes as $i) {
            echo "Key: {$i->Key_name}, Column: {$i->Column_name}, Unique: " . ($i->Non_unique ? "No" : "Yes") . "\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

checkIndexes('seminars');
checkIndexes('surats');
