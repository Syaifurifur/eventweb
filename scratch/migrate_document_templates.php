<?php
require __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Creating document_templates table if not exists...\n";

DB::statement("
CREATE TABLE IF NOT EXISTS `document_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(191) NOT NULL,
    `doc_type` VARCHAR(30) NOT NULL DEFAULT 'proposal',
    `event_category` VARCHAR(100) NOT NULL DEFAULT 'Semua',
    `description` TEXT NULL,
    `file_path` VARCHAR(255) NULL,
    `file_format` VARCHAR(20) NOT NULL DEFAULT 'DOCX',
    `thumbnail_path` VARCHAR(255) NULL,
    `structure_json` TEXT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    INDEX `idx_doc_type_status` (`doc_type`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Check if reports table has content and generated fields
$reportCols = array_column(DB::select('DESCRIBE reports'), 'Field');
if (!in_array('content', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `content` MEDIUMTEXT NULL AFTER `notes`");
}
if (!in_array('template_name', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `template_name` VARCHAR(180) NULL AFTER `document_link`");
}
if (!in_array('template_path', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `template_path` VARCHAR(255) NULL AFTER `template_name`");
}
if (!in_array('generated_path', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `generated_path` VARCHAR(255) NULL AFTER `template_path`");
}
if (!in_array('generated_at', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `generated_at` DATETIME NULL AFTER `generated_path`");
}
if (!in_array('ai_model', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `ai_model` VARCHAR(80) NULL AFTER `generated_at`");
}
if (!in_array('ai_notes', $reportCols, true)) {
    DB::statement("ALTER TABLE `reports` ADD COLUMN `ai_notes` TEXT NULL AFTER `ai_model`");
}

echo "Seeding default templates from screenshots...\n";
$defaultTemplates = [
    [
        'name' => 'Template Proposal Sosialisasi',
        'doc_type' => 'proposal',
        'event_category' => 'Sosialisasi',
        'description' => 'Template resmi proposal untuk kegiatan sosialisasi PMB, BJU, kunjungan sekolah, dll.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/proposal_sosialisasi.png',
        'status' => 'active',
        'created_at' => '2025-01-12 10:00:00',
    ],
    [
        'name' => 'Template Proposal Seminar',
        'doc_type' => 'proposal',
        'event_category' => 'Seminar',
        'description' => 'Untuk seminar, workshop, talkshow, kuliah umum, dll.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/proposal_seminar.png',
        'status' => 'active',
        'created_at' => '2025-01-10 09:30:00',
    ],
    [
        'name' => 'Template Proposal Lomba',
        'doc_type' => 'proposal',
        'event_category' => 'Lomba',
        'description' => 'Untuk kegiatan lomba, kompetisi, dan festival.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/proposal_lomba.png',
        'status' => 'active',
        'created_at' => '2025-01-08 14:15:00',
    ],
    [
        'name' => 'Template Proposal Lainnya',
        'doc_type' => 'proposal',
        'event_category' => 'Lainnya',
        'description' => 'Template umum untuk jenis kegiatan lainnya.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/proposal_lainnya.png',
        'status' => 'inactive',
        'created_at' => '2025-01-05 11:20:00',
    ],
    [
        'name' => 'Template Laporan Sosialisasi & Kunjungan',
        'doc_type' => 'report',
        'event_category' => 'Sosialisasi',
        'description' => 'Template resmi laporan pertanggungjawaban kegiatan sosialisasi sekolah & PMB.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/report_sosialisasi.png',
        'status' => 'active',
        'created_at' => '2025-01-12 11:00:00',
    ],
    [
        'name' => 'Template Laporan Seminar & Workshop',
        'doc_type' => 'report',
        'event_category' => 'Seminar',
        'description' => 'Template resmi laporan kegiatan seminar, workshop akademik & non akademik.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/report_seminar.png',
        'status' => 'active',
        'created_at' => '2025-01-11 13:00:00',
    ],
    [
        'name' => 'Template Laporan Lomba & Festival',
        'doc_type' => 'report',
        'event_category' => 'Lomba',
        'description' => 'Template resmi laporan kompetisi, evaluasi juara, dan dokumentasi.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/report_lomba.png',
        'status' => 'active',
        'created_at' => '2025-01-09 15:45:00',
    ],
    [
        'name' => 'Template Laporan Lainnya',
        'doc_type' => 'report',
        'event_category' => 'Lainnya',
        'description' => 'Template umum laporan pertanggungjawaban kegiatan event lainnya.',
        'file_format' => 'DOCX',
        'thumbnail_path' => 'assets/img/templates/report_lainnya.png',
        'status' => 'active',
        'created_at' => '2025-01-06 08:30:00',
    ],
];

foreach ($defaultTemplates as $tpl) {
    $exists = DB::table('document_templates')
        ->where('name', $tpl['name'])
        ->where('doc_type', $tpl['doc_type'])
        ->first();
    if (!$exists) {
        DB::table('document_templates')->insert($tpl);
        echo "Inserted template: {$tpl['name']}\n";
    }
}

echo "Migration finished successfully!\n";

