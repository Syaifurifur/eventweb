<?php
$templates = $templates ?? [];
$activeDocType = $activeDocType ?? 'proposal';
$eventCategories = $eventCategories ?? ['Sosialisasi', 'Seminar', 'Workshop', 'Lomba', 'Pelatihan', 'Lainnya'];
$filters = $filters ?? [
    'q' => '',
    'category' => '',
    'status' => '',
];
$isAdminSystem = $isAdminSystem ?? (session('user.role') === 'Admin Sistem');
$totalTemplates = count($templates);

$dtIcon = static function (string $name, int $size = 18): string {
    $paths = [
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'file-word' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13l2 5 2-5M8 13h2M12 13h2M10 18h2"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash' => '<path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'sparkles' => '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3z"/>',
    ];
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
};
?>

<style>
    .dt-page {
        --dt-primary: #2563eb;
        --dt-primary-dark: #1d4ed8;
        --dt-border: #e2e8f0;
        --dt-ink: #0f172a;
        --dt-muted: #64748b;
        display: flex;
        flex-direction: column;
        gap: 20px;
        color: var(--dt-ink);
        font-family: inherit;
    }
    .dt-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    .dt-head-left h1 {
        margin: 0 0 4px;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--dt-ink);
    }
    .dt-head-left p {
        margin: 0;
        color: var(--dt-muted);
        font-size: 14px;
    }
    .dt-head-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .dt-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        border: 1px solid var(--dt-border);
        background: #fff;
        color: var(--dt-ink);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .dt-btn-primary {
        background: var(--dt-primary);
        border-color: var(--dt-primary);
        color: #fff;
        box-shadow: 0 6px 16px -4px rgba(37, 99, 235, 0.45);
    }
    .dt-btn-primary:hover {
        background: var(--dt-primary-dark);
        border-color: var(--dt-primary-dark);
        color: #fff;
    }
    .dt-btn-outline {
        color: var(--dt-primary);
        border-color: #bfdbfe;
        background: #eff6ff;
    }
    .dt-btn-outline:hover {
        background: #dbeafe;
    }
    .dt-btn-sm {
        padding: 6px 12px;
        font-size: 12.5px;
        border-radius: 8px;
    }

    /* Subnav Tabs */
    .dt-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 2px;
    }
    .dt-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px 10px 0 0;
        font-size: 14px;
        font-weight: 700;
        color: var(--dt-muted);
        text-decoration: none;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        border-bottom: none;
        position: relative;
        bottom: -2px;
    }
    .dt-tab:hover {
        color: var(--dt-primary);
        background: #f8fafc;
    }
    .dt-tab.is-active {
        color: var(--dt-primary);
        background: #fff;
        border-color: #e2e8f0;
        border-bottom: 2px solid #fff;
        box-shadow: 0 -2px 6px rgba(15, 23, 42, 0.04);
    }
    .dt-tab-badge {
        font-size: 11.5px;
        padding: 2px 7px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #334155;
    }
    .dt-tab.is-active .dt-tab-badge {
        background: #eff6ff;
        color: var(--dt-primary);
    }

    /* Filter Bar */
    .dt-card {
        background: #fff;
        border: 1px solid var(--dt-border);
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .dt-filter-bar {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .dt-search-wrap {
        position: relative;
        flex: 1;
        min-width: 240px;
    }
    .dt-search-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .dt-input, .dt-select {
        height: 38px;
        border: 1px solid var(--dt-border);
        border-radius: 9px;
        padding: 0 12px;
        font-size: 13.5px;
        font-family: inherit;
        color: var(--dt-ink);
        background: #fff;
        outline: none;
        transition: border-color 0.15s ease;
    }
    .dt-input:focus, .dt-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .dt-search-input {
        width: 100%;
        padding-left: 36px;
    }
    .dt-select {
        cursor: pointer;
    }

    /* Table */
    .dt-table-wrap {
        overflow-x: auto;
    }
    .dt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        min-width: 860px;
    }
    .dt-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 12px 18px;
        border-bottom: 1px solid var(--dt-border);
        text-align: left;
    }
    .dt-table td {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .dt-table tr:hover td {
        background: #fafcff;
    }
    .dt-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .dt-file-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .dt-name-cell strong {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: var(--dt-ink);
    }
    .dt-badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .dt-badge-active {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .dt-badge-inactive {
        background: #ffe4e6;
        color: #be123c;
        border: 1px solid #fecdd3;
    }
    .dt-badge-cat {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .dt-badge-format {
        background: #eff6ff;
        color: #1d4ed8;
        font-family: monospace;
        font-weight: 800;
        border: 1px solid #bfdbfe;
    }
    .dt-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .dt-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .dt-action-btn:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        color: var(--dt-primary);
    }
    .dt-action-btn.is-danger:hover {
        border-color: #fecaca;
        background: #fef2f2;
        color: #dc2626;
    }

    /* Explanatory cards at bottom */
    .dt-explainer {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 20px;
        align-items: center;
        margin-top: 10px;
    }
    .dt-explain-card {
        background: #fff;
        border: 1.5px dashed #cbd5e1;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    }
    .dt-explain-badge {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: var(--dt-primary);
        color: #fff;
        display: inline-grid;
        place-items: center;
        font-weight: 800;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .dt-explain-card h3 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 800;
        color: var(--dt-ink);
    }
    .dt-explain-card p {
        margin: 0 0 14px;
        font-size: 13px;
        color: var(--dt-muted);
        line-height: 1.5;
    }
    .dt-explain-preview {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        gap: 10px;
        overflow-x: auto;
    }
    .dt-thumb-mini {
        width: 90px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        padding: 6px;
        flex-shrink: 0;
        text-align: center;
        font-size: 10.5px;
        font-weight: 700;
    }
    .dt-thumb-mini img {
        width: 100%;
        height: 65px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 4px;
    }
    .dt-explain-arrow {
        color: var(--dt-primary);
        font-size: 28px;
        display: grid;
        place-items: center;
    }

    /* Modal Form */
    .dt-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .dt-modal-overlay[hidden] {
        display: none;
    }
    .dt-modal {
        background: #fff;
        border-radius: 18px;
        width: 100%;
        max-width: 580px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
        overflow: hidden;
        animation: dtModalIn 0.2s ease-out;
    }
    @keyframes dtModalIn {
        from { transform: scale(0.96); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .dt-modal-head {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dt-modal-head h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--dt-ink);
    }
    .dt-modal-close {
        background: transparent;
        border: none;
        font-size: 22px;
        color: #64748b;
        cursor: pointer;
    }
    .dt-modal-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-height: 75vh;
        overflow-y: auto;
    }
    .dt-form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .dt-form-group label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
    }
    .dt-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    .dt-modal-foot {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @media (max-width: 900px) {
        .dt-explainer { grid-template-columns: 1fr; }
        .dt-explain-arrow { transform: rotate(90deg); margin: 6px 0; }
    }
</style>

<div class="dt-page">
    <!-- Header -->
    <div class="dt-head">
        <div class="dt-head-left">
            <div style="font-size:12.5px; color:#64748b; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                <span>Dashboard</span> &rsaquo; <span>Dokumen Event</span> &rsaquo; <strong style="color:#0f172a;">Template Dokumen</strong>
            </div>
            <h1>Template Dokumen</h1>
            <p>Kelola template resmi untuk proposal dan laporan event yang digunakan dalam generate AI.</p>
        </div>
        <div class="dt-head-actions">
            <a class="dt-btn dt-btn-outline" href="<?= site_url('documents') ?>">
                &larr; Ke Dokumen Event
            </a>
            <?php if ($isAdminSystem): ?>
                <button type="button" class="dt-btn dt-btn-primary" onclick="openTemplateModal()">
                    <?= $dtIcon('plus', 16) ?>
                    <span>Upload Template Baru</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Subnav Tabs -->
    <div class="dt-tabs">
        <a class="dt-tab <?= $activeDocType === 'proposal' ? 'is-active' : '' ?>" href="<?= site_url('document-templates?doc_type=proposal') ?>">
            <span>Template Proposal</span>
            <span class="dt-tab-badge"><?= $activeDocType === 'proposal' ? $totalTemplates : '' ?></span>
        </a>
        <a class="dt-tab <?= $activeDocType === 'report' ? 'is-active' : '' ?>" href="<?= site_url('document-templates?doc_type=report') ?>">
            <span>Template Laporan</span>
            <span class="dt-tab-badge"><?= $activeDocType === 'report' ? $totalTemplates : '' ?></span>
        </a>
    </div>

    <!-- Main Table Card -->
    <div class="dt-card">
        <form class="dt-filter-bar" method="get" action="<?= site_url('document-templates') ?>">
            <input type="hidden" name="doc_type" value="<?= esc($activeDocType) ?>">
            
            <div class="dt-search-wrap">
                <?= $dtIcon('search', 16) ?>
                <input
                    type="text"
                    name="q"
                    class="dt-input dt-search-input"
                    placeholder="Cari nama template atau deskripsi..."
                    value="<?= esc($filters['q']) ?>"
                >
            </div>

            <select name="category" class="dt-select" onchange="this.form.submit()">
                <option value="">Semua jenis event</option>
                <?php foreach ($eventCategories as $cat): ?>
                    <option value="<?= esc($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>>
                        <?= esc($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="dt-select" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select>

            <button type="submit" class="dt-btn dt-btn-primary dt-btn-sm">Terapkan</button>
            <?php if ($filters['q'] !== '' || $filters['category'] !== '' || $filters['status'] !== ''): ?>
                <a href="<?= site_url('document-templates?doc_type=' . esc($activeDocType)) ?>" class="dt-btn dt-btn-sm" style="color:#64748b;">Reset</a>
            <?php endif; ?>
        </form>

        <div class="dt-table-wrap">
            <table class="dt-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama Template</th>
                        <th>Jenis Event</th>
                        <th>Deskripsi</th>
                        <th>Format</th>
                        <th>Tanggal Upload</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($templates === []): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:38px 20px; color:#94a3b8;">
                                <div style="font-size:28px; margin-bottom:8px;">📄</div>
                                <strong>Belum ada template dokumen</strong>
                                <p style="margin:4px 0 0; font-size:13px;">Silakan klik "+ Upload Template Baru" untuk menambahkan template <?= esc($activeDocType) ?> resmi.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $idx => $tpl): ?>
                            <?php
                            $isActive = ($tpl['status'] ?? 'active') === 'active';
                            $uploadedDate = ! empty($tpl['created_at']) ? date('d M Y', strtotime((string) $tpl['created_at'])) : '-';
                            ?>
                            <tr>
                                <td style="font-weight:700; color:#64748b;"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="dt-name-cell">
                                        <div class="dt-file-icon">
                                            <?= $dtIcon('file-word', 20) ?>
                                        </div>
                                        <div>
                                            <strong><?= esc($tpl['name']) ?></strong>
                                            <span style="font-size:11.5px; color:#64748b;"><?= strtoupper(esc($tpl['doc_type'] ?? 'proposal')) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="dt-badge dt-badge-cat"><?= esc($tpl['event_category'] ?? 'Semua') ?></span>
                                </td>
                                <td style="max-width:320px; color:#475569; font-size:12.5px; line-height:1.4;">
                                    <?= esc($tpl['description'] ?? '-') ?>
                                </td>
                                <td>
                                    <span class="dt-badge dt-badge-format"><?= esc($tpl['file_format'] ?? 'DOCX') ?></span>
                                </td>
                                <td style="color:#64748b; font-size:12.5px; white-space:nowrap;">
                                    <?= esc($uploadedDate) ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="dt-badge dt-badge-active">● Aktif</span>
                                    <?php else: ?>
                                        <span class="dt-badge dt-badge-inactive">● Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dt-actions" style="justify-content:flex-end;">
                                        <a
                                            class="dt-action-btn"
                                            href="<?= site_url('documents/generate?type=' . esc($tpl['doc_type'] ?? 'proposal') . '&template_id=' . esc($tpl['id'])) ?>"
                                            title="Uji coba generate dengan template ini"
                                        >
                                            <?= $dtIcon('sparkles', 15) ?>
                                        </a>
                                        <?php if ($isAdminSystem): ?>
                                            <button
                                                type="button"
                                                class="dt-action-btn"
                                                title="Edit template"
                                                onclick='editTemplateModal(<?= json_encode($tpl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            >
                                                <?= $dtIcon('edit', 15) ?>
                                            </button>
                                            <form action="<?= site_url('document-templates/' . $tpl['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus template <?= esc($tpl['name'], 'js') ?>?');" style="margin:0;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dt-action-btn is-danger" title="Hapus template">
                                                    <?= $dtIcon('trash', 15) ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Explanatory Footer Cards Matching Image 3 -->
    <div class="dt-explainer">
        <div class="dt-explain-card">
            <div class="dt-explain-badge">1</div>
            <h3>Template yang sudah diupload muncul saat Generate</h3>
            <p>Template yang diupload oleh admin akan otomatis tersedia untuk dipilih di halaman generate dokumen proposal &amp; laporan event.</p>
            <div class="dt-explain-preview">
                <div class="dt-thumb-mini">
                    <img src="<?= base_url('assets/img/templates/proposal_sosialisasi.svg') ?>" alt="Sosialisasi">
                    <span>Sosialisasi</span>
                </div>
                <div class="dt-thumb-mini">
                    <img src="<?= base_url('assets/img/templates/proposal_seminar.svg') ?>" alt="Seminar">
                    <span>Seminar</span>
                </div>
                <div class="dt-thumb-mini">
                    <img src="<?= base_url('assets/img/templates/proposal_lomba.svg') ?>" alt="Lomba">
                    <span>Lomba</span>
                </div>
                <div class="dt-thumb-mini">
                    <img src="<?= base_url('assets/img/templates/proposal_lainnya.svg') ?>" alt="Lainnya">
                    <span>Lainnya</span>
                </div>
            </div>
        </div>

        <div class="dt-explain-arrow">
            <?= $dtIcon('arrow-right', 32) ?>
        </div>

        <div class="dt-explain-card">
            <div class="dt-explain-badge">2</div>
            <h3>Hasil dokumen mengikuti desain template yang diupload</h3>
            <p>Isi dokumen diisi cerdas oleh AI berdasarkan data event &amp; brief, sedangkan desain, logo, dan format tetap mengikuti standar template resmi.</p>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px; display:flex; align-items:center; gap:12px;">
                <div style="width:72px; height:88px; background:#fff; border:1px solid #cbd5e1; border-radius:6px; overflow:hidden; flex-shrink:0;">
                    <img src="<?= base_url('assets/img/templates/proposal_sosialisasi.svg') ?>" style="width:100%; height:100%; object-fit:cover;" alt="Preview Cover">
                </div>
                <div>
                    <strong style="font-size:13px; color:#0f172a; display:block;">PROPOSAL KEGIATAN RESMI</strong>
                    <span style="font-size:11.5px; color:#64748b; display:block;">Cover BSI Group, Latar Belakang, Rundown, Susunan Panitia &amp; RAB siap export Word / PDF.</span>
                    <span style="display:inline-block; margin-top:6px; font-size:11px; font-weight:700; color:#15803d; background:#dcfce7; padding:2px 7px; border-radius:6px;">✓ Terintegrasi Dokumen Event</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload / Edit Template -->
<div class="dt-modal-overlay" id="templateModal" hidden onclick="if(event.target===this)closeTemplateModal()">
    <div class="dt-modal">
        <div class="dt-modal-head">
            <h2 id="modalTitle">Upload Template Baru</h2>
            <button type="button" class="dt-modal-close" onclick="closeTemplateModal()">&times;</button>
        </div>
        <form id="templateForm" method="post" action="<?= site_url('document-templates/create') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="dt-modal-body">
                <div class="dt-form-group">
                    <label for="tpl-name">Nama Template <span style="color:#ef4444;">*</span></label>
                    <input id="tpl-name" type="text" name="name" class="dt-input" placeholder="Contoh: Template Proposal Sosialisasi Kampus" required>
                </div>

                <div class="dt-form-row">
                    <div class="dt-form-group">
                        <label for="tpl-doc-type">Jenis Dokumen <span style="color:#ef4444;">*</span></label>
                        <select id="tpl-doc-type" name="doc_type" class="dt-select" required>
                            <option value="proposal">Proposal Event</option>
                            <option value="report">Laporan Event</option>
                        </select>
                    </div>
                    <div class="dt-form-group">
                        <label for="tpl-category">Jenis Event <span style="color:#ef4444;">*</span></label>
                        <select id="tpl-category" name="event_category" class="dt-select" required>
                            <option value="Semua">Semua Jenis Event</option>
                            <?php foreach ($eventCategories as $c): ?>
                                <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="dt-form-group">
                    <label for="tpl-desc">Deskripsi Template</label>
                    <textarea id="tpl-desc" name="description" class="dt-input" rows="3" style="height:auto; padding:10px;" placeholder="Jelaskan peruntukan template ini (misal: untuk seminar, talkshow, kunjungan sekolah)..."></textarea>
                </div>

                <div class="dt-form-row">
                    <div class="dt-form-group">
                        <label for="tpl-format">Format Dokumen</label>
                        <select id="tpl-format" name="file_format" class="dt-select">
                            <option value="DOCX">DOCX (Microsoft Word)</option>
                            <option value="PDF">PDF Document</option>
                        </select>
                    </div>
                    <div class="dt-form-group">
                        <label for="tpl-status">Status</label>
                        <select id="tpl-status" name="status" class="dt-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="dt-form-group">
                    <label for="tpl-file">File Template (.docx / .dotx / .doc)</label>
                    <input id="tpl-file" type="file" name="template_file" class="dt-input" accept=".docx,.doc,.dotx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    <small style="color:#64748b; font-size:11.5px;">Opsional. Jika kosong, sistem otomatis memakai layout master resmi BSI Group.</small>
                </div>
            </div>

            <div class="dt-modal-foot">
                <button type="button" class="dt-btn" onclick="closeTemplateModal()">Batal</button>
                <button type="submit" class="dt-btn dt-btn-primary">Simpan Template</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTemplateModal() {
    const modal = document.getElementById('templateModal');
    const form = document.getElementById('templateForm');
    document.getElementById('modalTitle').textContent = 'Upload Template Baru';
    form.action = '<?= site_url('document-templates/create') ?>';
    form.reset();
    document.getElementById('tpl-doc-type').value = '<?= esc($activeDocType) ?>';
    modal.removeAttribute('hidden');
}

function editTemplateModal(tpl) {
    const modal = document.getElementById('templateModal');
    const form = document.getElementById('templateForm');
    document.getElementById('modalTitle').textContent = 'Edit Template: ' + (tpl.name || '');
    form.action = '<?= site_url('document-templates/') ?>' + tpl.id + '/update';
    
    document.getElementById('tpl-name').value = tpl.name || '';
    document.getElementById('tpl-doc-type').value = tpl.doc_type || 'proposal';
    document.getElementById('tpl-category').value = tpl.event_category || 'Semua';
    document.getElementById('tpl-desc').value = tpl.description || '';
    document.getElementById('tpl-format').value = tpl.file_format || 'DOCX';
    document.getElementById('tpl-status').value = tpl.status || 'active';
    
    modal.removeAttribute('hidden');
}

function closeTemplateModal() {
    document.getElementById('templateModal').setAttribute('hidden', '');
}
</script>

