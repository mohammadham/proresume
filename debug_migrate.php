<?php
echo "Starting...\n";
require 'vendor/autoload.php';
echo "Autoload OK\n";
$app = require 'bootstrap/app.php';
echo "App loaded\n";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo "Kernel loaded\n";
