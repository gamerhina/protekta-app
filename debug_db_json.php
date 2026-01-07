<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$res = [
    'seminars' => DB::select("SHOW INDEX FROM seminars"),
    'surats' => DB::select("SHOW INDEX FROM surats")
];
echo json_encode($res, JSON_PRETTY_PRINT);
