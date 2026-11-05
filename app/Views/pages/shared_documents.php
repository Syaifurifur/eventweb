<?php
$documents = $documents ?? [];
$isAdmin = $isAdmin ?? false;
$stats = $stats ?? [
    'total_documents' => 0,
    'total_downloads' => 0,
    'total_bytes' => 0,
    'categories_count' => 0,
];
$categories = $categories ?? [
    'Panduan & SOP',
    'Template & Format',
    'SK & Regulasi',
    'Brosur & Materi Promosi',
    'Dokumen Akreditasi',
    'Formulir & Administrasi',
    'Lainnya',
];
$filters = $filters ?? [
    'q' => '',
    'category' => '',
    'format' => '',
    'status' => '',
    'sort' => 'newest',
];

$formatBytes = static function ($bytes): string {
    $bytes = (int) $bytes;
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
};

$getFileTheme = static function (string $ext): array {
    $e = strtolower($ext);
    return match (true) {
        $e === 'pdf' => [
            'icon' => 'solar:file-text-bold-duotone',
            'badge_bg' => 'background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fca5a5 !important;',
            'btn_bg' => 'background: #dc2626 !important; color: #ffffff !important;',
            'label' => 'PDF',
            'color' => '#dc2626',
        ],
        in_array($e, ['doc', 'docx', 'dotx'], true) => [
            'icon' => 'solar:document-bold-duotone',
            'badge_bg' => 'background: #dbeafe !important; color: #1d4ed8 !important; border: 1px solid #93c5fd !important;',
            'btn_bg' => 'background: #2563eb !important; color: #ffffff !important;',
            'label' => 'Word',
            'color' => '#2563eb',
        ],
        in_array($e, ['xls', 'xlsx', 'csv'], true) => [
            'icon' => 'solar:file-spreadsheet-bold-duotone',
            'badge_bg' => 'background: #d1fae5 !important; color: #047857 !important; border: 1px solid #6ee7b7 !important;',
            'btn_bg' => 'background: #059669 !important; color: #ffffff !important;',
            'label' => 'Excel',
            'color' => '#059669',
        ],
        in_array($e, ['ppt', 'pptx'], true) => [
            'icon' => 'solar:presentation-graph-bold-duotone',
            'badge_bg' => 'background: #fef3c7 !important; color: #b45309 !important; border: 1px solid #fcd34d !important;',
            'btn_bg' => 'background: #d97706 !important; color: #ffffff !important;',
            'label' => 'PPT',
            'color' => '#d97706',
        ],
        in_array($e, ['zip', 'rar', '7z', 'tar', 'gz'], true) => [
            'icon' => 'solar:archive-bold-duotone',
            'badge_bg' => 'background: #f3e8ff !important; color: #7e22ce !important; border: 1px solid #d8b4fe !important;',
            'btn_bg' => 'background: #9333ea !important; color: #ffffff !important;',
            'label' => 'Arsip',
            'color' => '#9333ea',
        ],
        in_array($e, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) => [
            'icon' => 'solar:gallery-bold-duotone',
            'badge_bg' => 'background: #cffafe !important; color: #0e7490 !important; border: 1px solid #67e8f9 !important;',
            'btn_bg' => 'background: #0891b2 !important; color: #ffffff !important;',
            'label' => 'Gambar',
            'color' => '#0891b2',
        ],
        default => [
            'icon' => 'solar:file-download-bold-duotone',
            'badge_bg' => 'background: #f1f5f9 !important; color: #334155 !important; border: 1px solid #cbd5e1 !important;',
            'btn_bg' => 'background: #475569 !important; color: #ffffff !important;',
            'label' => strtoupper($e ?: 'FILE'),
            'color' => '#475569',
        ],
    };
};
?>

<style>
/* Embedded resilient styling */
.sd-hero {
    position: relative;
    border-radius: 20px;
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 35%, #2563eb 70%, #3b82f6 100%) !important;
    color: #ffffff !important;
    padding: 30px 32px;
    box-shadow: 0 14px 28px -6px rgba(37, 99, 235, 0.35), 0 4px 10px -2px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
.sd-hero * {
    box-sizing: border-box;
}
.sd-hero-title {
    margin: 0 0 8px 0;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #ffffff !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    line-height: 1.25;
}
@media (min-width: 768px) {
    .sd-hero-title {
        font-size: 30px;
    }
}
.sd-hero-desc {
    margin: 0;
    color: #e0e7ff !important;
    font-size: 14px;
    line-height: 1.6;
    max-width: 680px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}
.sd-hero-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 13px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(255, 255, 255, 0.22) !important;
    border: 1px solid rgba(255, 255, 255, 0.45) !important;
    color: #ffffff !important;
    backdrop-filter: blur(8px);
}
.sd-hero-badge-pill.emerald {
    background: rgba(16, 185, 129, 0.28) !important;
    border-color: rgba(52, 211, 153, 0.55) !important;
    color: #f0fdf4 !important;
}
.sd-hero-badge-pill.amber {
    background: rgba(245, 158, 11, 0.32) !important;
    border-color: rgba(251, 191, 36, 0.6) !important;
    color: #fffbeb !important;
}
.sd-hero-btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 22px;
    border-radius: 12px;
    background: #ffffff !important;
    color: #1d4ed8 !important;
    font-size: 13.5px;
    font-weight: 800;
    border: none !important;
    cursor: pointer;
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.25);
    transition: all 0.2s ease;
    white-space: nowrap;
    text-decoration: none;
}
.sd-hero-btn-upload:hover {
    background: #eff6ff !important;
    color: #1e40af !important;
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.3);
}

/* Stat Cards */
.sd-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px;
    box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.05);
}
html.dark .sd-stat-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
.sd-stat-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b !important;
    margin-bottom: 2px;
}
html.dark .sd-stat-label {
    color: #94a3b8 !important;
}
.sd-stat-val {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a !important;
    line-height: 1.1;
}
html.dark .sd-stat-val {
    color: #f8fafc !important;
}

/* Toolbar */
.sd-toolbar {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.05);
}
html.dark .sd-toolbar {
    background: #1e293b !important;
    border-color: #334155 !important;
}
.sd-control {
    width: 100%;
    height: 42px;
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid #cbd5e1 !important;
    background: #f8fafc !important;
    color: #0f172a !important;
    font-size: 13.5px;
    box-sizing: border-box;
}
.sd-control:focus {
    outline: none;
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
html.dark .sd-control {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
}

/* Cards */
.sd-doc-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.sd-doc-card:hover {
    transform: translateY(-2px);
    border-color: #cbd5e1 !important;
    box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08);
}
html.dark .sd-doc-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
.sd-doc-title {
    margin: 0 0 6px 0;
    font-size: 15.5px;
    font-weight: 800;
    line-height: 1.35;
    color: #0f172a !important;
}
html.dark .sd-doc-title {
    color: #f8fafc !important;
}
.sd-doc-desc {
    font-size: 12.5px;
    line-height: 1.55;
    color: #475569 !important;
    margin: 0 0 14px 0;
}
html.dark .sd-doc-desc {
    color: #cbd5e1 !important;
}

.sd-btn-download {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    background: #2563eb !important;
    color: #ffffff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border: none !important;
    box-shadow: 0 4px 10px -2px rgba(37, 99, 235, 0.35);
    cursor: pointer;
    flex: 1;
}
.sd-btn-download:hover {
    background: #1d4ed8 !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}
.sd-btn-preview {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 14px;
    border-radius: 10px;
    background: #f8fafc !important;
    color: #1e293b !important;
    border: 1px solid #cbd5e1 !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
html.dark .sd-btn-preview {
    background: #0f172a !important;
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}
.sd-btn-copy {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 12px;
    border-radius: 10px;
    background: #f8fafc !important;
    color: #64748b !important;
    border: 1px solid #cbd5e1 !important;
    cursor: pointer;
}
html.dark .sd-btn-copy {
    background: #0f172a !important;
    color: #94a3b8 !important;
    border-color: #334155 !important;
}
</style>

<div class="space-y-6">
    <!-- Header Hero Card (Ultra High Contrast Background & Crisp White Typography) -->
    <div class="sd-hero">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="sd-hero-badge-pill">
                        <iconify-icon icon="solar:folder-with-files-bold" class="text-sm"></iconify-icon>
                        Pusat Unduhan & Repositori
                    </span>
                    <span class="sd-hero-badge-pill emerald">
                        <iconify-icon icon="solar:users-group-rounded-bold" class="text-sm"></iconify-icon>
                        Akses Terbuka Seluruh Pengguna
                    </span>
                    <?php if ($isAdmin): ?>
                        <span class="sd-hero-badge-pill amber">
                            <iconify-icon icon="solar:shield-check-bold" class="text-sm"></iconify-icon>
                            Mode Pengelola Admin
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="sd-hero-title">
                    Pusat Unduhan Berkas & Dokumen
                </h1>

                <p class="sd-hero-desc">
                    Akses terpusat dokumen resmi, panduan operasional (SOP), format proposal baku, format LPJ, SK rektorat, dan materi promosi kampus. Seluruh user dapat mencari, mempratinjau, dan mengunduh berkas dengan cepat.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <?php if ($isAdmin): ?>
                    <button type="button" onclick="openUploadModal()" class="sd-hero-btn-upload">
                        <iconify-icon icon="solar:upload-track-2-bold-duotone" style="font-size: 20px; color: #1d4ed8;"></iconify-icon>
                        <span>Upload Dokumen Baru</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ambient glow effects -->
        <div class="sd-hero-glow-1"></div>
        <div class="sd-hero-glow-2"></div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="sd-stat-card">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 text-2xl font-bold">
                <iconify-icon icon="solar:documents-bold-duotone"></iconify-icon>
            </div>
            <div>
                <span class="sd-stat-label">Total Dokumen</span>
                <div class="sd-stat-val"><?= (int) ($stats['total_documents'] ?? 0) ?></div>
            </div>
        </div>

        <div class="sd-stat-card">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-2xl font-bold">
                <iconify-icon icon="solar:download-square-bold-duotone"></iconify-icon>
            </div>
            <div>
                <span class="sd-stat-label">Total Diunduh</span>
                <div class="sd-stat-val" style="color: #059669 !important;"><?= number_format((int) ($stats['total_downloads'] ?? 0)) ?>x</div>
            </div>
        </div>

        <div class="sd-stat-card">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 text-2xl font-bold">
                <iconify-icon icon="solar:folder-with-files-bold-duotone"></iconify-icon>
            </div>
            <div>
                <span class="sd-stat-label">Kategori</span>
                <div class="sd-stat-val"><?= (int) ($stats['categories_count'] ?? 0) ?></div>
            </div>
        </div>

        <div class="sd-stat-card">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-2xl font-bold">
                <iconify-icon icon="solar:database-bold-duotone"></iconify-icon>
            </div>
            <div>
                <span class="sd-stat-label">Kapasitas Berkas</span>
                <div class="sd-stat-val"><?= $formatBytes($stats['total_bytes'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="sd-toolbar">
        <form method="get" action="<?= site_url('shared-documents') ?>" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <!-- Search Keyword -->
                <div class="md:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <iconify-icon icon="solar:magnifer-outline" class="text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Cari judul dokumen, deskripsi, atau nama file..." class="sd-control" style="padding-left: 38px;">
                </div>

                <!-- Category Filter -->
                <div class="md:col-span-3">
                    <select name="category" class="sd-control">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>" <?= ($filters['category'] === $cat) ? 'selected' : '' ?>>
                                <?= esc($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Format Filter -->
                <div class="md:col-span-2">
                    <select name="format" class="sd-control">
                        <option value="">Semua Format</option>
                        <option value="pdf" <?= ($filters['format'] === 'pdf') ? 'selected' : '' ?>>PDF (.pdf)</option>
                        <option value="word" <?= ($filters['format'] === 'word') ? 'selected' : '' ?>>Word (.docx/.doc)</option>
                        <option value="excel" <?= ($filters['format'] === 'excel') ? 'selected' : '' ?>>Excel (.xlsx/.csv)</option>
                        <option value="ppt" <?= ($filters['format'] === 'ppt') ? 'selected' : '' ?>>Presentasi (.pptx)</option>
                        <option value="image" <?= ($filters['format'] === 'image') ? 'selected' : '' ?>>Gambar (PNG/JPG)</option>
                        <option value="archive" <?= ($filters['format'] === 'archive') ? 'selected' : '' ?>>Arsip (.zip/.rar)</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="md:col-span-2">
                    <select name="sort" class="sd-control">
                        <option value="newest" <?= ($filters['sort'] === 'newest') ? 'selected' : '' ?>>Terbaru Diunggah</option>
                        <option value="popular" <?= ($filters['sort'] === 'popular') ? 'selected' : '' ?>>Terbanyak Diunduh</option>
                        <option value="name_asc" <?= ($filters['sort'] === 'name_asc') ? 'selected' : '' ?>>Nama (A-Z)</option>
                        <option value="name_desc" <?= ($filters['sort'] === 'name_desc') ? 'selected' : '' ?>>Nama (Z-A)</option>
                        <option value="size_desc" <?= ($filters['sort'] === 'size_desc') ? 'selected' : '' ?>>Ukuran Terbesar</option>
                    </select>
                </div>
            </div>

            <!-- Additional options bar -->
            <div class="flex items-center justify-between flex-wrap gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <?php if ($isAdmin): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Status:</span>
                            <a href="<?= site_url('shared-documents?' . http_build_query(array_merge($filters, ['status' => '']))) ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold <?= empty($filters['status']) ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">Semua</a>
                            <a href="<?= site_url('shared-documents?' . http_build_query(array_merge($filters, ['status' => 'active']))) ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold <?= ($filters['status'] === 'active') ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">Aktif</a>
                            <a href="<?= site_url('shared-documents?' . http_build_query(array_merge($filters, ['status' => 'archived']))) ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold <?= ($filters['status'] === 'archived') ? 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">Arsip</a>
                        </div>
                    <?php endif; ?>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Menampilkan <strong><?= count($documents) ?></strong> dokumen
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <?php if (! empty($filters['q']) || ! empty($filters['category']) || ! empty($filters['format']) || ! empty($filters['status']) || $filters['sort'] !== 'newest'): ?>
                        <a href="<?= site_url('shared-documents') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                            <iconify-icon icon="solar:restart-outline"></iconify-icon>
                            Reset Filter
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-primary-600 dark:hover:bg-primary-700 font-bold text-xs shadow-sm transition cursor-pointer">
                        <iconify-icon icon="solar:filter-bold"></iconify-icon>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Documents Grid -->
    <?php if (empty($documents)): ?>
        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4">
                <iconify-icon icon="solar:folder-error-bold-duotone" class="text-3xl"></iconify-icon>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">Tidak ada dokumen yang ditemukan</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-5">
                Coba sesuaikan kata kunci pencarian, ubah filter kategori, atau reset filter untuk melihat dokumen lainnya.
            </p>
            <div class="flex items-center justify-center gap-3">
                <a href="<?= site_url('shared-documents') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <iconify-icon icon="solar:restart-outline"></iconify-icon>
                    Reset Filter
                </a>
                <?php if ($isAdmin): ?>
                    <button type="button" onclick="openUploadModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        <iconify-icon icon="solar:upload-outline"></iconify-icon>
                        Upload Dokumen Sekarang
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="sd-doc-grid">
            <?php foreach ($documents as $doc): ?>
                <?php
                $theme = $getFileTheme($doc['file_extension'] ?? '');
                $isArchived = ($doc['status'] ?? 'active') === 'archived';
                $uploadedAt = ! empty($doc['created_at']) ? date('d M Y', strtotime((string) $doc['created_at'])) : '-';
                $previewUrl = site_url('shared-documents/' . $doc['id'] . '/preview');
                $downloadUrl = site_url('shared-documents/' . $doc['id'] . '/download');
                ?>
                <div class="sd-doc-card <?= $isArchived ? 'opacity-70 ring-1 ring-amber-400/40' : '' ?>">
                    <div>
                        <!-- Header Bar of Card -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 shadow-sm" style="<?= $theme['badge_bg'] ?>">
                                    <iconify-icon icon="<?= $theme['icon'] ?>" style="font-size: 24px;"></iconify-icon>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wide uppercase" style="<?= $theme['badge_bg'] ?>">
                                        <?= esc($theme['label']) ?> &bull; <?= esc($doc['formatted_size'] ?? '0 KB') ?>
                                    </span>
                                    <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                                        <?= esc($doc['category'] ?? 'Lainnya') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <?php if ($isArchived): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-300/40">
                                        Arsip
                                    </span>
                                <?php endif; ?>

                                <?php if ($isAdmin): ?>
                                    <div class="relative inline-block text-left">
                                        <button type="button" onclick="toggleDropdown('docMenu_<?= $doc['id'] ?>')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            <iconify-icon icon="solar:menu-dots-bold" class="text-lg"></iconify-icon>
                                        </button>
                                        <div id="docMenu_<?= $doc['id'] ?>" class="hidden absolute right-0 mt-1 w-44 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl py-1 z-30">
                                            <button type="button" onclick='openEditModal(<?= json_encode($doc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="w-full text-left px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 flex items-center gap-2">
                                                <iconify-icon icon="solar:pen-new-square-outline" class="text-base text-blue-500"></iconify-icon>
                                                Edit Informasi
                                            </button>
                                            <form method="post" action="<?= site_url('shared-documents/' . $doc['id'] . '/toggle-status') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="w-full text-left px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 flex items-center gap-2">
                                                    <iconify-icon icon="<?= $isArchived ? 'solar:check-circle-outline' : 'solar:archive-outline' ?>" class="text-base text-amber-500"></iconify-icon>
                                                    <?= $isArchived ? 'Aktifkan Dokumen' : 'Arsipkan Dokumen' ?>
                                                </button>
                                            </form>
                                            <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                                            <button type="button" onclick="openDeleteModal(<?= (int) $doc['id'] ?>, '<?= esc($doc['title'], 'js') ?>')" class="w-full text-left px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 flex items-center gap-2">
                                                <iconify-icon icon="solar:trash-bin-trash-outline" class="text-base"></iconify-icon>
                                                Hapus Dokumen
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Document Title -->
                        <h4 class="sd-doc-title" title="<?= esc($doc['title']) ?>">
                            <?= esc($doc['title']) ?>
                        </h4>

                        <!-- Description -->
                        <p class="sd-doc-desc">
                            <?= ! empty($doc['description']) ? esc($doc['description']) : '<span class="italic text-slate-400">Tidak ada catatan tambahan untuk berkas ini.</span>' ?>
                        </p>

                        <!-- Metadata Badges -->
                        <div class="space-y-1.5 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11.5px] text-slate-500 dark:text-slate-400">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 truncate pr-2">
                                    <iconify-icon icon="solar:file-outline" class="shrink-0 text-slate-400"></iconify-icon>
                                    <span class="truncate" title="<?= esc($doc['file_name']) ?>"><?= esc($doc['file_name']) ?></span>
                                </span>
                                <span class="font-bold text-slate-700 dark:text-slate-300 shrink-0">
                                    <?= esc($doc['formatted_size'] ?? '') ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <iconify-icon icon="solar:calendar-date-outline" class="shrink-0 text-slate-400"></iconify-icon>
                                    <span><?= esc($uploadedAt) ?></span>
                                </span>
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full text-[10.5px] border border-emerald-200 dark:border-emerald-800">
                                    <iconify-icon icon="solar:download-square-outline"></iconify-icon>
                                    <?= number_format((int) ($doc['download_count'] ?? 0)) ?>x diunduh
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <!-- Download Button (Available for ALL users) -->
                        <a href="<?= $downloadUrl ?>" class="sd-btn-download" title="Download file ke perangkat">
                            <iconify-icon icon="solar:download-square-bold" style="font-size: 17px;"></iconify-icon>
                            <span>Unduh Berkas</span>
                        </a>

                        <!-- Preview Button if supported -->
                        <?php if (! empty($doc['can_preview'])): ?>
                            <a href="<?= $previewUrl ?>" target="_blank" class="sd-btn-preview" title="Lihat pratinjau dokumen">
                                <iconify-icon icon="solar:eye-outline" style="font-size: 16px; color: #2563eb;"></iconify-icon>
                                <span>Preview</span>
                            </a>
                        <?php endif; ?>

                        <!-- Copy Link Button -->
                        <button type="button" onclick="copyDownloadLink('<?= $downloadUrl ?>', this)" class="sd-btn-copy" title="Salin tautan unduhan">
                            <iconify-icon icon="solar:copy-outline" style="font-size: 16px;"></iconify-icon>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ================= MODAL UPLOAD DOKUMEN (ADMIN ONLY) ================= -->
<?php if ($isAdmin): ?>
    <dialog id="modalUploadDocument" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl p-0 w-full max-w-xl backdrop:bg-black/60 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <iconify-icon icon="solar:upload-track-2-bold-duotone" class="text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-slate-100">Upload Dokumen Baru</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Berkas akan langsung tersedia untuk diunduh seluruh user</p>
                </div>
            </div>
            <button type="button" onclick="closeUploadModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <iconify-icon icon="solar:close-circle-outline" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form method="post" action="<?= site_url('shared-documents/upload') ?>" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <!-- File Dropzone -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Pilih Berkas Dokumen <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-5 text-center hover:border-blue-500 dark:hover:border-blue-400 bg-slate-50/50 dark:bg-slate-800/50 transition cursor-pointer" onclick="document.getElementById('inputUploadFile').click()">
                    <iconify-icon icon="solar:cloud-upload-bold-duotone" class="text-4xl text-blue-600 dark:text-blue-400 mb-2"></iconify-icon>
                    <div id="uploadFileText" class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                        Klik untuk memilih file atau drag & drop berkas ke sini
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        Format yang didukung: PDF, DOCX, DOC, XLSX, XLS, PPTX, ZIP, RAR, PNG, JPG (Maks 50MB)
                    </div>
                </div>
                <input type="file" id="inputUploadFile" name="document_file" required class="hidden" onchange="handleFileSelect(this, 'uploadFileText')">
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Judul Dokumen <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required placeholder="Contoh: Buku Pedoman Event Sosialisasi 2026" class="sd-control">
            </div>

            <!-- Category & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Kategori Dokumen <span class="text-rose-500">*</span>
                    </label>
                    <select name="category" required class="sd-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Status Dokumen
                    </label>
                    <select name="status" class="sd-control">
                        <option value="active" selected>Aktif (Dapat diunduh semua user)</option>
                        <option value="archived">Arsip (Disembunyikan dari user biasa)</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Deskripsi / Catatan Tambahan (Opsional)
                </label>
                <textarea name="description" rows="3" placeholder="Jelaskan ringkasan isi dokumen atau petunjuk penggunaan..." class="sd-control" style="height: auto;"></textarea>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer">
                    <iconify-icon icon="solar:check-circle-bold" class="text-base"></iconify-icon>
                    <span>Upload & Publikasikan</span>
                </button>
            </div>
        </form>
    </dialog>

    <!-- ================= MODAL EDIT DOKUMEN (ADMIN ONLY) ================= -->
    <dialog id="modalEditDocument" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl p-0 w-full max-w-xl backdrop:bg-black/60 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <iconify-icon icon="solar:pen-new-square-bold-duotone" class="text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-slate-100">Edit Dokumen</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui rincian atau ganti file dokumen</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <iconify-icon icon="solar:close-circle-outline" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="formEditDocument" method="post" action="" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <!-- Title -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Judul Dokumen <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="editTitle" name="title" required class="sd-control">
            </div>

            <!-- Category & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Kategori Dokumen <span class="text-rose-500">*</span>
                    </label>
                    <select id="editCategory" name="category" required class="sd-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Status Dokumen
                    </label>
                    <select id="editStatus" name="status" class="sd-control">
                        <option value="active">Aktif (Dapat diunduh seluruh user)</option>
                        <option value="archived">Arsip (Disembunyikan dari user biasa)</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Deskripsi / Catatan Tambahan
                </label>
                <textarea id="editDescription" name="description" rows="3" class="sd-control" style="height: auto;"></textarea>
            </div>

            <!-- Optional File Replacement -->
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Ganti Berkas Dokumen (Opsional)
                </label>
                <p class="text-[11px] text-slate-400 mb-2">Biarkan kosong jika tetap menggunakan berkas saat ini (<span id="editCurrentFile" class="font-bold text-slate-700 dark:text-slate-300"></span>).</p>
                <input type="file" name="document_file" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-slate-700 dark:file:text-slate-200">
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition cursor-pointer">
                    <iconify-icon icon="solar:diskette-bold" class="text-base"></iconify-icon>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </dialog>

    <!-- ================= MODAL DELETE DOKUMEN (ADMIN ONLY) ================= -->
    <dialog id="modalDeleteDocument" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl p-0 w-full max-w-md backdrop:bg-black/60 overflow-hidden">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
                <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="text-3xl"></iconify-icon>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900 dark:text-slate-100">Konfirmasi Hapus Dokumen</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Apakah Anda yakin ingin menghapus dokumen <strong id="deleteDocTitle" class="text-slate-800 dark:text-slate-200"></strong>? Berkas fisik di server juga akan dihapus permanen.
                </p>
            </div>

            <form id="formDeleteDocument" method="post" action="" class="flex items-center justify-center gap-2.5 pt-2">
                <?= csrf_field() ?>
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/20 transition cursor-pointer">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                    <span>Ya, Hapus</span>
                </button>
            </form>
        </div>
    </dialog>
<?php endif; ?>

<!-- Toast Notification container -->
<div id="copyToast" class="fixed bottom-6 right-6 z-50 hidden bg-slate-900 text-white px-4 py-3 rounded-xl shadow-2xl text-xs font-semibold items-center gap-2 border border-slate-700">
    <iconify-icon icon="solar:check-circle-bold" class="text-emerald-400 text-base"></iconify-icon>
    <span id="copyToastMsg">Tautan berhasil disalin ke clipboard!</span>
</div>

<script>
function toggleDropdown(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = el.classList.contains('hidden');
    document.querySelectorAll('[id^="docMenu_"]').forEach(m => m.classList.add('hidden'));
    if (isHidden) {
        el.classList.remove('hidden');
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick*="toggleDropdown"]') && !e.target.closest('[id^="docMenu_"]')) {
        document.querySelectorAll('[id^="docMenu_"]').forEach(m => m.classList.add('hidden'));
    }
});

function handleFileSelect(input, textId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        document.getElementById(textId).innerHTML = '<span class="text-blue-600 dark:text-blue-400 font-bold">' + file.name + '</span> (' + sizeMb + ' MB)';
    }
}

function copyDownloadLink(url, btn) {
    const fullUrl = window.location.origin + (url.startsWith('/') ? url : '/' + url);
    navigator.clipboard.writeText(fullUrl).then(() => {
        showToast('Tautan unduhan berhasil disalin ke clipboard!');
        if (btn) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" style="font-size: 16px; color: #10b981;"></iconify-icon>';
            setTimeout(() => { btn.innerHTML = originalHtml; }, 1800);
        }
    }).catch(err => {
        const ta = document.createElement('textarea');
        ta.value = fullUrl;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('Tautan unduhan berhasil disalin!');
    });
}

function showToast(msg) {
    const toast = document.getElementById('copyToast');
    const msgEl = document.getElementById('copyToastMsg');
    if (!toast || !msgEl) return;
    msgEl.textContent = msg;
    toast.style.display = 'inline-flex';
    if (window._toastTimer) clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        toast.style.display = 'none';
    }, 2500);
}

<?php if ($isAdmin): ?>
function openUploadModal() {
    const modal = document.getElementById('modalUploadDocument');
    if (modal) modal.showModal();
}
function closeUploadModal() {
    const modal = document.getElementById('modalUploadDocument');
    if (modal) modal.close();
}

function openEditModal(doc) {
    const modal = document.getElementById('modalEditDocument');
    const form = document.getElementById('formEditDocument');
    if (!modal || !form) return;

    form.action = '<?= site_url('shared-documents') ?>/' + doc.id + '/update';
    document.getElementById('editTitle').value = doc.title || '';
    document.getElementById('editCategory').value = doc.category || 'Lainnya';
    document.getElementById('editStatus').value = doc.status || 'active';
    document.getElementById('editDescription').value = doc.description || '';
    document.getElementById('editCurrentFile').textContent = doc.file_name || 'Berkas saat ini';

    modal.showModal();
}
function closeEditModal() {
    const modal = document.getElementById('modalEditDocument');
    if (modal) modal.close();
}

function openDeleteModal(id, title) {
    const modal = document.getElementById('modalDeleteDocument');
    const form = document.getElementById('formDeleteDocument');
    const titleEl = document.getElementById('deleteDocTitle');
    if (!modal || !form) return;

    form.action = '<?= site_url('shared-documents') ?>/' + id + '/delete';
    if (titleEl) titleEl.textContent = '"' + title + '"';

    modal.showModal();
}
function closeDeleteModal() {
    const modal = document.getElementById('modalDeleteDocument');
    if (modal) modal.close();
}
<?php endif; ?>
</script>
