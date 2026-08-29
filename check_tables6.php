<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Starting...\n";
require 'vendor/autoload.php';
echo "Autoload OK\n";
$app = require 'bootstrap/app.php';
echo "App loaded\n";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "Bootstrap OK\n";
$pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
echo "DB connected\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
$targets = ['personal_access_tokens', 'api_integrations', 'provinces', 'cities', 'basic_settings', 'users', 'migrations'];
foreach ($targets as $t) {
    echo $t . ': ' . (in_array($t, $tables) ? 'EXISTS' : 'MISSING') . "\n";
}
echo "\nTotal tables: " . count($tables) . "\n";
