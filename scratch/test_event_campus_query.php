<?php
require_once __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$eventModel = new \App\Models\EventModel();
$raw1 = $eventModel->find(1447);
echo "raw1 campus_name: " . var_export($raw1['campus_name'] ?? null, true) . "\n";
echo "raw1 campus_id: " . var_export($raw1['campus_id'] ?? null, true) . "\n";

$raw2 = $eventModel->withCreator()->where('events.id', 1447)->first();
echo "raw2 campus_name: " . var_export($raw2['campus_name'] ?? null, true) . "\n";

