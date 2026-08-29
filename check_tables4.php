<?php
$host = 'localhost';
$port = '3306';
$db = 'profilex';
$user = 'root';
$pass = 'root';
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $targets = ['personal_access_tokens', 'api_integrations', 'provinces', 'cities', 'basic_settings', 'users'];
    foreach ($targets as $t) {
        echo $t . ': ' . (in_array($t, $tables) ? 'EXISTS' : 'MISSING') . "\n";
    }
    echo "\nTotal tables: " . count($tables) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
