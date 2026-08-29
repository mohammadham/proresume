<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
$stmt = $pdo->query("SHOW TABLES LIKE '%api%'");
foreach ($stmt->fetchAll() as $row) {
    echo implode(', ', $row) . "\n";
}
