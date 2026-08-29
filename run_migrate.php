<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput([
        'command' => 'migrate',
        '--path' => 'database/migrations',
        '--force' => true,
    ]),
    new Symfony\Component\Console\Output\StreamOutput(fopen('php://stdout', 'w'))
);
$kernel->terminate($input, $status);
file_put_contents('migrate_result.txt', "Exit status: $status\n", FILE_APPEND);
