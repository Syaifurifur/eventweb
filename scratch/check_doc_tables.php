<?php
require __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- proposals columns ---\n";
print_r(DB::select('DESCRIBE proposals'));

echo "\n--- reports columns ---\n";
print_r(DB::select('DESCRIBE reports'));

