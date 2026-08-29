<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
$targets = ['personal_access_tokens', 'api_integrations', 'provinces', 'cities', 'basic_settings'];
foreach ($targets as $t) {
    echo $t . ': ' . (in_array($t, $tables) ? 'EXISTS' : 'MISSING') . "\n";
}
