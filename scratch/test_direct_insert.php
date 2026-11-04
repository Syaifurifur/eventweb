<?php
define('FCPATH', __DIR__ . '/../public/');
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require_once 'system/bootstrap.php';

$model = new \App\Models\AccreditationDocumentModel();
$id = $model->insert([
    'event_id' => 1,
    'campus_id' => 1,
    'title' => 'Test Bukti Akreditasi',
    'event_year' => 2026,
    'accreditation_standard' => 'Kriteria 9 - Luaran dan Capaian Tridharma',
    'doc_type' => 'all',
    'status' => 'menunggu_kelengkapan',
    'admin_notes' => 'Catatan test',
    'designated_by' => 1,
    'designated_at' => date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

echo "Insert result: " . var_export($id, true) . "\n";
if ($id === false) {
    echo "Model errors:\n";
    print_r($model->errors());
}

$rows = $model->findAll();
echo "Total rows in accreditation_documents: " . count($rows) . "\n";
print_r($rows);

