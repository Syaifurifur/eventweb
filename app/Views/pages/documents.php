<?php
$selectedDocumentType = $selectedDocumentType ?? 'all';
$documentRows = $documentRows ?? [];
$documentNotifications = $documentNotifications ?? [];
$documentPagination = $documentPagination ?? [
    'current' => 1,
    'perPage' => 10,
    'total' => 0,
    'totalPages' => 1,
    'from' => 0,
    'to' => 0,
    'query' => [],
];
$documentCampusOptions = $documentCampusOptions ?? [];
$selectedDocumentCampus = $selectedDocumentCampus ?? '';
$documentStats = array_merge([
    'total_events' => 0,
    'waiting_review' => 0,
    'complete' => 0,
    'missing' => 0,
    'trend_pct' => 0,
], $documentStats ?? []);
$documentFilters = array_merge([
    'q' => '',
    'doc_status' => '',
    'period' => '',
    'sort' => 'newest',
    'per_page' => 10,
], $documentFilters ?? []);
$documentTypeLabels = [
    'all' => 'Semua dokumen',
    'proposal' => 'Proposal',
    'report' => 'Laporan',
];
$documentStatusLabels = [
    '' => 'Semua status',
    'complete' => 'Lengkap',
    'partial' => 'Sebagian (1 dokumen)',
    'none' => 'Belum ada dokumen',
    'waiting_review' => 'Menunggu review',
];
$isAdminSystem = $isAdminSystem ?? false;

$totalEvents = (int) $documentStats['total_events'];
$completePct = $totalEvents > 0 ? (int) round(((int) $documentStats['complete'] / $totalEvents) * 100) : 0;
$missingPct = $totalEvents > 0 ? (int) round(((int) $documentStats['missing'] / $totalEvents) * 100) : 0;
$trendPct = (int) $documentStats['trend_pct'];
$advancedOpen = $selectedDocumentCampus !== '' || $selectedDocumentType !== 'all' || $documentFilters['doc_status'] !== '' || $documentFilters['period'] !== '';

$dxIcon = static function (string $name, int $size = 18): string {
    $paths = [
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>',
        'check-circle' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        'file-alert' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h7"/><path d="M14 2v6h6v5"/><circle cx="18" cy="18" r="4"/><path d="M18 16.5v2M18 20h.01"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'filter' => '<path d="M22 3H2l8 9.46V19l4 2v-8.54z"/>',
        'building' => '<path d="M3 21h18M5 21V10l7-5 7 5v11M9 21v-6h6v6M9 10h.01M15 10h.01"/>',
        'layers' => '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/>',
        'sort' => '<path d="m3 16 4 4 4-4M7 20V4M21 8l-4-4-4 4M17 4v16"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5M12 3v12"/>',
        'more' => '<circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'arrow-up' => '<path d="M12 19V5M5 12l7-7 7 7"/>',
        'arrow-down' => '<path d="M12 5v14M19 12l-7 7-7-7"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'trash' => '<path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
    ];

    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
};

$dxCategoryClass = static function (string $category): string {
    $c = mb_strtolower($category);
    return match (true) {
        str_contains($c, 'seminar'), str_contains($c, 'webinar') => 'dx-cat-purple',
        str_contains($c, 'workshop'), str_contains($c, 'training'), str_contains($c, 'pelatihan') => 'dx-cat-blue',
        str_contains($c, 'sosialisasi'), str_contains($c, 'visit'), str_contains($c, 'kunjungan') => 'dx-cat-pink',
        str_contains($c, 'expo'), str_contains($c, 'edufair'), str_contains($c, 'pameran') => 'dx-cat-teal',
        str_contains($c, 'lomba'), str_contains($c, 'kompetisi'), str_contains($c, 'talkshow') => 'dx-cat-amber',
        default => 'dx-cat-slate',
    };
};

$dxDocStatusClass = static function (string $status, bool $hasLink): string {
    $s = mb_strtolower($status);
    if (! $hasLink) {
        return 'dx-chip-slate';
    }
    return match (true) {
        str_contains($s, 'tolak'), str_contains($s, 'revisi'), str_contains($s, 'reject') => 'dx-chip-red',
        str_contains($s, 'setuju'), str_contains($s, 'approved'), str_contains($s, 'selesai'), str_contains($s, 'lengkap'), str_contains($s, 'valid'), str_contains($s, 'terima') => 'dx-chip-green',
        default => 'dx-chip-amber',
    };
};

$dxStateMeta = [
    'complete' => ['label' => 'Lengkap', 'class' => 'dx-chip-green', 'bar' => 'dx-bar-green'],
    'waiting_report' => ['label' => 'Menunggu Laporan', 'class' => 'dx-chip-amber', 'bar' => 'dx-bar-green'],
    'waiting_proposal' => ['label' => 'Menunggu Proposal', 'class' => 'dx-chip-amber', 'bar' => 'dx-bar-green'],
    'none' => ['label' => 'Belum ada', 'class' => 'dx-chip-red', 'bar' => 'dx-bar-red'],
];

$dxDayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$dxMonthNames = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$documentPageUrl = static function (int $page) use ($documentPagination): string {
    $query = $documentPagination['query'] ?? [];
    $query['page'] = $page;

    return site_url('documents?' . http_build_query($query));
};
$baseQuery = $documentPagination['query'] ?? [];
?>

<style>
    .dx-page { --dx-primary:#2563eb; --dx-primary-dark:#1d4ed8; --dx-border:#e5eaf2; --dx-muted:#64748b; --dx-ink:#0f172a; display:flex; flex-direction:column; gap:18px; color:var(--dx-ink); }
    .dx-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .dx-eyebrow { font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--dx-primary); }
    .dx-head h1 { font-size:26px; font-weight:800; margin:4px 0 2px; letter-spacing:-.02em; }
    .dx-head p { margin:0; color:var(--dx-muted); font-size:14px; }
    .dx-head-right { display:flex; flex-direction:column; align-items:flex-end; gap:12px; }
    .dx-breadcrumb { display:flex; align-items:center; gap:6px; font-size:13px; color:var(--dx-muted); }
    .dx-breadcrumb a { color:var(--dx-muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    .dx-breadcrumb strong { color:var(--dx-ink); font-weight:700; }
    .dx-head-actions { display:flex; align-items:center; gap:10px; }
    .dx-mode { background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:700; padding:5px 10px; border-radius:8px; }
    .dx-mode.is-view { background:#f1f5f9; color:#475569; }
    .dx-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; height:40px; padding:0 16px; border-radius:10px; font-size:13.5px; font-weight:700; border:1px solid var(--dx-border); background:#fff; color:var(--dx-ink); cursor:pointer; text-decoration:none; transition:.15s ease; white-space:nowrap; }
    .dx-btn:hover { border-color:#cbd5e1; background:#f8fafc; }
    .dx-btn-primary { background:var(--dx-primary); border-color:var(--dx-primary); color:#fff; box-shadow:0 6px 16px -6px rgba(37,99,235,.55); }
    .dx-btn-primary:hover { background:var(--dx-primary-dark); border-color:var(--dx-primary-dark); }
    .dx-btn-outline { color:var(--dx-primary); border-color:#bfdbfe; }
    .dx-btn-outline:hover, .dx-btn-outline.is-active { background:#eff6ff; border-color:#93c5fd; }
    .dx-btn-sm { height:34px; padding:0 14px; font-size:12.5px; border-radius:8px; }

    .dx-stats { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; }
    .dx-stat { background:#fff; border:1px solid var(--dx-border); border-radius:14px; padding:18px; display:flex; gap:14px; align-items:flex-start; box-shadow:0 1px 2px rgba(15,23,42,.04); }
    .dx-stat-icon { width:44px; height:44px; border-radius:12px; display:grid; place-items:center; flex-shrink:0; }
    .dx-stat-body { flex:1; min-width:0; }
    .dx-stat-label { font-size:13px; color:#334155; font-weight:600; }
    .dx-stat-row { display:flex; align-items:center; justify-content:space-between; gap:8px; margin:4px 0 2px; }
    .dx-stat-value { font-size:26px; font-weight:800; letter-spacing:-.02em; }
    .dx-stat-sub { font-size:12px; color:var(--dx-muted); }
    .dx-pill { font-size:11.5px; font-weight:700; padding:3px 8px; border-radius:999px; display:inline-flex; align-items:center; gap:3px; }
    .dx-icon-blue { background:#eff6ff; color:#2563eb; } .dx-icon-amber { background:#fff7ed; color:#ea580c; }
    .dx-icon-green { background:#ecfdf5; color:#16a34a; } .dx-icon-red { background:#fff1f2; color:#e11d48; }
    .dx-pill-green { background:#dcfce7; color:#15803d; } .dx-pill-red { background:#ffe4e6; color:#be123c; } .dx-pill-amber { background:#ffedd5; color:#c2410c; }

    .dx-card { background:#fff; border:1px solid var(--dx-border); border-radius:14px; box-shadow:0 1px 2px rgba(15,23,42,.04); }
    .dx-filter { padding:18px; display:flex; flex-direction:column; gap:14px; }
    .dx-filter-top { display:flex; gap:10px; flex-wrap:wrap; }
    .dx-input-wrap { position:relative; flex:1; min-width:260px; display:flex; align-items:center; }
    .dx-input-wrap > svg { position:absolute; left:13px; color:#94a3b8; pointer-events:none; }
    .dx-input, .dx-select { width:100%; height:40px; border:1px solid var(--dx-border); border-radius:10px; background:#fff; font-size:13.5px; color:var(--dx-ink); padding:0 12px 0 40px; outline:none; transition:.15s; font-family:inherit; }
    .dx-select { appearance:none; padding-right:34px; cursor:pointer; }
    .dx-input:focus, .dx-select:focus { border-color:#93c5fd; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .dx-select-caret { position:absolute; right:12px; color:#64748b; pointer-events:none; }
    .dx-filter-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:14px; }
    .dx-filter-grid[hidden] { display:none; }
    .dx-field label { display:block; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:6px; }

    .dx-list-head { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; padding:18px 18px 12px; flex-wrap:wrap; }
    .dx-list-head h2 { margin:0; font-size:18px; font-weight:800; }
    .dx-list-head p { margin:2px 0 0; font-size:12.5px; color:var(--dx-muted); }
    .dx-list-tools { display:flex; gap:8px; }
    .dx-list-tools .dx-input-wrap { min-width:0; flex:none; }
    .dx-list-tools .dx-select { height:34px; font-size:12.5px; font-weight:600; border-radius:8px; }
    .dx-table-wrap { margin:0 18px; border:1px solid var(--dx-border); border-radius:12px; overflow:auto; }
    .dx-table { width:100%; border-collapse:collapse; font-size:13px; min-width:980px; }
    .dx-table thead th { text-align:left; font-size:12px; font-weight:700; color:#334155; padding:11px 12px; background:#f8fafc; border-bottom:1px solid var(--dx-border); white-space:nowrap; }
    .dx-table tbody tr.dx-row > td { padding:11px 12px; border-bottom:1px solid #eef2f7; vertical-align:middle; }
    .dx-table tbody tr.dx-row:hover > td { background:#fafcff; }
    .dx-table tbody tr.dx-row.is-open > td { background:#f5f9ff; border-bottom-color:transparent; }
    .dx-code { color:#334155; font-weight:600; white-space:nowrap; }
    .dx-title-cell { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .dx-title-cell a { color:var(--dx-ink); font-weight:700; text-decoration:none; }
    .dx-title-cell a:hover { color:var(--dx-primary); }
    .dx-cat { font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; white-space:nowrap; }
    .dx-cat-purple { background:#f3e8ff; color:#7e22ce; } .dx-cat-blue { background:#dbeafe; color:#1d4ed8; }
    .dx-cat-pink { background:#ffe4e6; color:#be123c; } .dx-cat-teal { background:#ccfbf1; color:#0f766e; }
    .dx-cat-amber { background:#fef3c7; color:#b45309; } .dx-cat-slate { background:#f1f5f9; color:#475569; }
    .dx-meta { display:flex; align-items:center; gap:9px; }
    .dx-meta > svg { color:#475569; flex-shrink:0; }
    .dx-meta strong { display:block; font-size:12.5px; font-weight:700; white-space:nowrap; }
    .dx-meta small { display:block; font-size:11.5px; color:var(--dx-muted); white-space:nowrap; }
    .dx-progress { width:130px; }
    .dx-progress-track { height:8px; border-radius:999px; background:#e2e8f0; overflow:hidden; position:relative; }
    .dx-progress-fill { height:100%; border-radius:999px; }
    .dx-bar-green { background:linear-gradient(90deg,#22c55e,#16a34a); }
    .dx-bar-red { background:#ef4444; width:8px !important; }
    .dx-progress small { display:block; font-size:11.5px; color:#334155; margin-top:4px; font-weight:600; }
    .dx-chip { font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:999px; white-space:nowrap; display:inline-block; }
    .dx-chip-green { background:#dcfce7; color:#15803d; } .dx-chip-amber { background:#ffedd5; color:#c2410c; }
    .dx-chip-red { background:#ffe4e6; color:#be123c; } .dx-chip-slate { background:#e2e8f0; color:#334155; }
    .dx-toggle { width:32px; height:32px; border-radius:8px; border:none; background:transparent; color:#334155; cursor:pointer; display:grid; place-items:center; transition:.2s; }
    .dx-toggle:hover { background:#eef2ff; }
    .dx-row.is-open .dx-toggle svg { transform:rotate(180deg); }
    .dx-toggle svg { transition:transform .2s; }

    .dx-detail > td { padding:0 !important; background:#f5f9ff; border-bottom:1px solid #e5eaf2; }
    .dx-detail[hidden] { display:none; }
    .dx-detail-inner { margin:0 14px 14px 30px; background:#fff; border:1px solid var(--dx-border); border-radius:10px; }
    .dx-doc-row { display:grid; grid-template-columns:200px 150px 150px 1fr auto auto; align-items:center; gap:14px; padding:12px 16px; }
    .dx-doc-row + .dx-doc-row, .dx-doc-form + .dx-doc-row { border-top:1px solid #eef2f7; }
    .dx-doc-name { display:flex; align-items:center; gap:12px; font-weight:700; }
    .dx-doc-icon { width:34px; height:34px; border-radius:9px; display:grid; place-items:center; }
    .dx-doc-icon.is-proposal { background:#eff6ff; color:#2563eb; }
    .dx-doc-icon.is-report { background:#ecfdf5; color:#16a34a; }
    .dx-doc-code { font-size:12px; color:var(--dx-muted); white-space:nowrap; }
    .dx-doc-code a, .dx-doc-code span { color:var(--dx-primary); font-weight:600; text-decoration:none; }
    .dx-doc-note { font-size:12px; color:var(--dx-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .dx-doc-note a { color:var(--dx-primary); text-decoration:none; font-weight:600; }
    .dx-menu { position:relative; }
    .dx-menu > summary { list-style:none; width:32px; height:32px; border-radius:8px; display:grid; place-items:center; cursor:pointer; color:#334155; }
    .dx-menu > summary::-webkit-details-marker { display:none; }
    .dx-menu > summary:hover { background:#f1f5f9; }
    .dx-menu-pop { position:absolute; right:0; top:36px; z-index:20; background:#fff; border:1px solid var(--dx-border); border-radius:10px; box-shadow:0 12px 28px -8px rgba(15,23,42,.18); min-width:180px; padding:6px; }
    .dx-menu-pop a, .dx-menu-pop button { display:flex; align-items:center; gap:8px; width:100%; padding:8px 10px; border-radius:7px; font-size:12.5px; font-weight:600; color:#334155; background:none; border:none; cursor:pointer; text-decoration:none; text-align:left; font-family:inherit; }
    .dx-menu-pop a:hover, .dx-menu-pop button:hover { background:#f1f5f9; }
    .dx-menu-pop .is-danger { color:#be123c; }
    .dx-menu-pop .is-danger:hover { background:#fff1f2; }
    .dx-menu-empty { padding:8px 10px; font-size:12px; color:var(--dx-muted); }
    .dx-doc-form { padding:14px 16px 16px 62px; background:#f8fafc; display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:12px; align-items:end; }
    .dx-doc-form[hidden] { display:none; }
    .dx-doc-form label { font-size:12px; font-weight:700; color:#334155; display:flex; flex-direction:column; gap:5px; }
    .dx-doc-form input, .dx-doc-form textarea { border:1px solid var(--dx-border); border-radius:8px; padding:8px 10px; font-size:12.5px; background:#fff; font-family:inherit; }
    .dx-doc-form .dx-wide { grid-column:1 / -1; }

    .dx-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 18px 18px; flex-wrap:wrap; font-size:12.5px; color:var(--dx-muted); }
    .dx-pages { display:flex; align-items:center; gap:6px; }
    .dx-page-btn { min-width:32px; height:32px; padding:0 8px; border-radius:8px; border:1px solid var(--dx-border); background:#fff; color:#334155; display:grid; place-items:center; font-size:12.5px; font-weight:700; text-decoration:none; }
    .dx-page-btn:hover { border-color:#93c5fd; color:var(--dx-primary); }
    .dx-page-btn.is-active { background:var(--dx-primary); border-color:var(--dx-primary); color:#fff; }
    .dx-page-btn.is-disabled { opacity:.45; pointer-events:none; }
    .dx-empty { padding:40px 20px; text-align:center; color:var(--dx-muted); }
    .dx-empty strong { display:block; color:var(--dx-ink); font-size:15px; margin-bottom:4px; }

    .dx-alerts { padding:14px 18px; display:flex; flex-direction:column; gap:10px; border-color:#bfdbfe; background:linear-gradient(180deg,#f8fbff,#fff); }
    .dx-alerts-head { display:flex; align-items:center; justify-content:space-between; gap:10px; font-weight:800; font-size:14px; }
    .dx-alerts-head span:first-child { display:inline-flex; align-items:center; gap:8px; color:var(--dx-primary); }
    .dx-alert-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:10px; }
    .dx-alert-item { border:1px solid var(--dx-border); background:#fff; border-radius:10px; padding:10px 12px; font-size:12.5px; }
    .dx-alert-item strong { display:block; font-size:13px; margin-bottom:2px; }
    .dx-alert-item p { margin:0 0 6px; color:var(--dx-muted); }
    .dx-alert-item a { color:var(--dx-primary); font-weight:700; text-decoration:none; }

    @media (max-width:1200px) { .dx-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } .dx-filter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .dx-doc-row { grid-template-columns:1fr 1fr; } }
    @media (max-width:640px) { .dx-stats, .dx-filter-grid { grid-template-columns:1fr; } .dx-head-right { align-items:flex-start; } }
</style>

<div class="dx-page">
    <!-- Header -->
    <div class="dx-head">
        <div>
            <span class="dx-eyebrow">Aplikasi Penjadwalan &amp; Manajemen Event</span>
            <h1>Dokumen Event</h1>
            <p>Manajemen proposal dan laporan setiap event</p>
        </div>
        <div class="dx-head-right">
            <nav class="dx-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= site_url('dashboard') ?>"><?= $dxIcon('home', 14) ?> Dashboard</a>
                <span>/</span>
                <strong>Dokumen Event</strong>
            </nav>
            <div class="dx-head-actions">
                <?php if ($isAdminSystem): ?>
                    <span class="dx-mode">Mode CRUD Admin</span>
                <?php else: ?>
                    <span class="dx-mode is-view">Mode Lihat</span>
                <?php endif; ?>
                <a class="dx-btn dx-btn-primary" href="<?= site_url('events/new') ?>"><?= $dxIcon('plus', 16) ?> Tambah Event</a>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <section class="dx-stats">
        <article class="dx-stat">
            <div class="dx-stat-icon dx-icon-blue"><?= $dxIcon('calendar', 22) ?></div>
            <div class="dx-stat-body">
                <div class="dx-stat-label">Total Event</div>
                <div class="dx-stat-row">
                    <span class="dx-stat-value"><?= esc(number_format($totalEvents, 0, ',', '.')) ?></span>
                    <span class="dx-pill <?= $trendPct >= 0 ? 'dx-pill-green' : 'dx-pill-red' ?>"><?= $dxIcon($trendPct >= 0 ? 'arrow-up' : 'arrow-down', 12) ?> <?= esc((string) abs($trendPct)) ?>%</span>
                </div>
                <div class="dx-stat-sub">Dibanding 30 hari sebelumnya</div>
            </div>
        </article>
        <article class="dx-stat">
            <div class="dx-stat-icon dx-icon-amber"><?= $dxIcon('file', 22) ?></div>
            <div class="dx-stat-body">
                <div class="dx-stat-label">Menunggu Review</div>
                <div class="dx-stat-row">
                    <span class="dx-stat-value"><?= esc(number_format((int) $documentStats['waiting_review'], 0, ',', '.')) ?></span>
                    <span class="dx-pill dx-pill-amber"><?= $dxIcon('clock', 13) ?></span>
                </div>
                <div class="dx-stat-sub">Proposal &amp; Laporan</div>
            </div>
        </article>
        <article class="dx-stat">
            <div class="dx-stat-icon dx-icon-green"><?= $dxIcon('check-circle', 22) ?></div>
            <div class="dx-stat-body">
                <div class="dx-stat-label">Dokumen Lengkap</div>
                <div class="dx-stat-row">
                    <span class="dx-stat-value"><?= esc(number_format((int) $documentStats['complete'], 0, ',', '.')) ?></span>
                    <span class="dx-pill dx-pill-green"><?= esc((string) $completePct) ?>%</span>
                </div>
                <div class="dx-stat-sub">Proposal &amp; Laporan tersedia</div>
            </div>
        </article>
        <article class="dx-stat">
            <div class="dx-stat-icon dx-icon-red"><?= $dxIcon('file-alert', 22) ?></div>
            <div class="dx-stat-body">
                <div class="dx-stat-label">Dokumen Belum Ada</div>
                <div class="dx-stat-row">
                    <span class="dx-stat-value"><?= esc(number_format((int) $documentStats['missing'], 0, ',', '.')) ?></span>
                    <span class="dx-pill dx-pill-red"><?= esc((string) $missingPct) ?>%</span>
                </div>
                <div class="dx-stat-sub">Proposal atau Laporan belum diunggah</div>
            </div>
        </article>
    </section>

    <?php if ($documentNotifications !== []): ?>
        <section class="dx-card dx-alerts" data-document-notification-panel>
            <div class="dx-alerts-head">
                <span><?= $dxIcon('bell', 16) ?> Proposal/Laporan baru diupload</span>
                <span class="dx-chip dx-chip-amber" data-document-notification-count><?= esc((string) count($documentNotifications)) ?> notifikasi</span>
            </div>
            <div class="dx-alert-list">
                <?php foreach ($documentNotifications as $notification): ?>
                    <?php
                        $notificationLink = (string) ($notification['link'] ?? '');
                        $notificationHref = $notificationLink !== ''
                            ? site_url('documents/notification/open?' . http_build_query([
                                'key' => (string) ($notification['key'] ?? ''),
                                'event' => (string) ($notification['event_id'] ?? ''),
                                'type' => (string) ($notification['type'] ?? ''),
                            ]))
                            : '';
                    ?>
                    <div class="dx-alert-item" data-document-notification>
                        <strong><?= esc($notification['label'] ?? 'Dokumen') ?> - <?= esc($notification['event_title'] ?? '-') ?></strong>
                        <p>
                            <?= esc($notification['campus'] ?? '-') ?>
                            <?php if (! empty($notification['uploaded_at'])): ?>
                                &middot; <?= esc(date('d M Y H:i', strtotime($notification['uploaded_at']))) ?>
                            <?php endif; ?>
                            <?php if (! empty($notification['pic_name'])): ?>
                                &middot; <?= esc($notification['pic_name']) ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($notificationHref !== ''): ?>
                            <a href="<?= esc($notificationHref) ?>" target="_blank" rel="noopener" data-dismiss-document-notification>Buka dokumen →</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Filter -->
    <form class="dx-card dx-filter" action="<?= site_url('documents') ?>" method="get" id="dx-filter-form">
        <input type="hidden" name="sort" value="<?= esc($documentFilters['sort'], 'attr') ?>">
        <input type="hidden" name="per_page" value="<?= esc((string) $documentFilters['per_page'], 'attr') ?>">
        <div class="dx-filter-top">
            <div class="dx-input-wrap">
                <?= $dxIcon('search', 17) ?>
                <input class="dx-input" type="search" name="q" value="<?= esc($documentFilters['q'], 'attr') ?>" placeholder="Cari nama event, kode event, kode proposal, atau kode laporan...">
            </div>
            <button type="button" class="dx-btn dx-btn-outline <?= $advancedOpen ? 'is-active' : '' ?>" data-dx-toggle-filter aria-expanded="<?= $advancedOpen ? 'true' : 'false' ?>"><?= $dxIcon('filter', 15) ?> Filter Lanjutan</button>
            <a class="dx-btn" href="<?= site_url('documents') ?>">Reset</a>
            <button type="submit" class="dx-btn dx-btn-primary">Terapkan</button>
        </div>
        <div class="dx-filter-grid" data-dx-filter-grid <?= $advancedOpen ? '' : 'hidden' ?>>
            <div class="dx-field">
                <label for="dx-campus">Kampus</label>
                <div class="dx-input-wrap">
                    <?= $dxIcon('building', 16) ?>
                    <select class="dx-select" id="dx-campus" name="campus">
                        <option value="">Semua kampus</option>
                        <?php foreach ($documentCampusOptions as $campus): ?>
                            <option value="<?= esc($campus, 'attr') ?>" <?= $selectedDocumentCampus === $campus ? 'selected' : '' ?>><?= esc($campus) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="dx-select-caret"><?= $dxIcon('chevron-down', 16) ?></span>
                </div>
            </div>
            <div class="dx-field">
                <label for="dx-type">Jenis Dokumen</label>
                <div class="dx-input-wrap">
                    <?= $dxIcon('file', 16) ?>
                    <select class="dx-select" id="dx-type" name="type">
                        <?php foreach ($documentTypeLabels as $key => $label): ?>
                            <option value="<?= esc($key, 'attr') ?>" <?= $selectedDocumentType === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="dx-select-caret"><?= $dxIcon('chevron-down', 16) ?></span>
                </div>
            </div>
            <div class="dx-field">
                <label for="dx-status">Status Dokumen</label>
                <div class="dx-input-wrap">
                    <?= $dxIcon('layers', 16) ?>
                    <select class="dx-select" id="dx-status" name="doc_status">
                        <?php foreach ($documentStatusLabels as $key => $label): ?>
                            <option value="<?= esc($key, 'attr') ?>" <?= $documentFilters['doc_status'] === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="dx-select-caret"><?= $dxIcon('chevron-down', 16) ?></span>
                </div>
            </div>
            <div class="dx-field">
                <label for="dx-period">Periode Event</label>
                <div class="dx-input-wrap">
                    <?= $dxIcon('calendar', 16) ?>
                    <input class="dx-input" id="dx-period" type="month" name="period" value="<?= esc($documentFilters['period'], 'attr') ?>">
                </div>
            </div>
        </div>
    </form>

    <!-- Event Document List -->
    <section class="dx-card">
        <div class="dx-list-head">
            <div>
                <h2>Daftar Event</h2>
                <p>Menampilkan <?= esc((string) count($documentRows)) ?> dari <?= esc(number_format((int) $documentPagination['total'], 0, ',', '.')) ?> event</p>
            </div>
            <div class="dx-list-tools">
                <div class="dx-input-wrap" style="width:150px;">
                    <?= $dxIcon('sort', 15) ?>
                    <select class="dx-select" data-dx-sync="sort" aria-label="Urutkan">
                        <option value="newest" <?= $documentFilters['sort'] === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="oldest" <?= $documentFilters['sort'] === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                    </select>
                    <span class="dx-select-caret"><?= $dxIcon('chevron-down', 14) ?></span>
                </div>
                <div class="dx-input-wrap" style="width:110px;">
                    <select class="dx-select" data-dx-sync="per_page" aria-label="Jumlah data" style="padding-left:12px;">
                        <?php foreach ([10, 25, 50] as $size): ?>
                            <option value="<?= $size ?>" <?= (int) $documentFilters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?> data</option>
                        <?php endforeach; ?>
                    </select>
                    <span class="dx-select-caret"><?= $dxIcon('chevron-down', 14) ?></span>
                </div>
            </div>
        </div>

        <div class="dx-table-wrap">
            <table class="dx-table">
                <thead>
                    <tr>
                        <th style="width:44px;">No</th>
                        <th>Kode Event</th>
                        <th>Nama Event</th>
                        <th>Kampus</th>
                        <th>Tanggal Event</th>
                        <th>Status Dokumen</th>
                        <th></th>
                        <th style="width:56px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentRows as $rowIndex => $row): ?>
                        <?php
                            $event = $row['event'] ?? [];
                            $eventCode = (string) ($event['id'] ?? '');
                            $rowNumber = (int) ($documentPagination['from'] ?? 1) + $rowIndex;
                            $uploaded = (int) ($row['doc_uploaded'] ?? 0);
                            $required = max(1, (int) ($row['doc_required'] ?? 2));
                            $state = (string) ($row['doc_state'] ?? 'none');
                            $stateMeta = $dxStateMeta[$state] ?? $dxStateMeta['none'];
                            $fillPct = (int) round(($uploaded / $required) * 100);

                            $campusFull = trim((string) ($event['campus'] ?? '-'));
                            $campusParts = preg_split('/\s+/', $campusFull, 2) ?: [$campusFull];
                            $campusMain = $campusParts[0] ?? $campusFull;
                            $campusSub = trim((string) ($campusParts[1] ?? ''));

                            $eventTime = ! empty($event['date']) ? strtotime((string) $event['date']) : false;
                            $dateMain = $eventTime ? date('d', $eventTime) . ' ' . $dxMonthNames[(int) date('n', $eventTime)] . ' ' . date('Y', $eventTime) : '-';
                            $dateSub = $eventTime ? $dxDayNames[(int) date('w', $eventTime)] : '';
                            $isOpen = $rowIndex === 0;
                            $detailId = 'dx-detail-' . $rowIndex;
                        ?>
                        <tr class="dx-row <?= $isOpen ? 'is-open' : '' ?>" data-dx-row="<?= esc($detailId, 'attr') ?>">
                            <td><?= esc((string) $rowNumber) ?></td>
                            <td class="dx-code"><?= esc($eventCode) ?></td>
                            <td>
                                <div class="dx-title-cell">
                                    <a href="<?= site_url('events/' . $eventCode) ?>"><?= esc($event['title'] ?? '-') ?></a>
                                    <?php if (! empty($event['category'])): ?>
                                        <span class="dx-cat <?= esc($dxCategoryClass((string) $event['category']), 'attr') ?>"><?= esc($event['category']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="dx-meta">
                                    <?= $dxIcon('building', 17) ?>
                                    <div>
                                        <strong><?= esc($campusMain) ?></strong>
                                        <?php if ($campusSub !== ''): ?><small><?= esc($campusSub) ?></small><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="dx-meta">
                                    <?= $dxIcon('calendar', 16) ?>
                                    <div>
                                        <strong><?= esc($dateMain) ?></strong>
                                        <?php if ($dateSub !== ''): ?><small><?= esc($dateSub) ?></small><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="dx-progress">
                                    <div class="dx-progress-track"><div class="dx-progress-fill <?= esc($stateMeta['bar'], 'attr') ?>" style="width:<?= $fillPct ?>%;"></div></div>
                                    <small><?= esc($uploaded . '/' . $required) ?> dokumen</small>
                                </div>
                            </td>
                            <td><span class="dx-chip <?= esc($stateMeta['class'], 'attr') ?>"><?= esc($stateMeta['label']) ?></span></td>
                            <td style="text-align:center;">
                                <button type="button" class="dx-toggle" data-dx-toggle="<?= esc($detailId, 'attr') ?>" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>" aria-controls="<?= esc($detailId, 'attr') ?>" title="Lihat dokumen">
                                    <?= $dxIcon('chevron-down', 18) ?>
                                </button>
                            </td>
                        </tr>
                        <tr class="dx-detail" id="<?= esc($detailId, 'attr') ?>" <?= $isOpen ? '' : 'hidden' ?>>
                            <td colspan="8">
                                <div class="dx-detail-inner">
                                    <?php foreach (($row['documents'] ?? []) as $document): ?>
                                        <?php
                                            $type = (string) ($document['type'] ?? '');
                                            $link = (string) ($document['link'] ?? '');
                                            $hasLink = $link !== '';
                                            $usesFileUpload = in_array($type, ['proposal', 'report'], true);
                                            $linkIsExternal = preg_match('/^https?:\/\//i', $link) === 1;
                                            $documentHref = $hasLink
                                                ? ($usesFileUpload && ! $linkIsExternal ? site_url('events/' . $eventCode . '/documents/' . $type . '/file') : $link)
                                                : '';
                                            $documentDisplay = $hasLink && $usesFileUpload && ! $linkIsExternal
                                                ? basename(str_replace('\\', '/', $link))
                                                : $link;
                                            $formAction = site_url('documents/' . $eventCode . '/' . $type . '/link');
                                            $deleteAction = site_url('documents/' . $eventCode . '/' . $type . '/delete');
                                            $formId = 'dx-form-' . $rowIndex . '-' . $type;
                                            $statusText = (string) ($document['status'] ?? '-');
                                            $docCode = (string) ($document['code'] ?? '-');
                                        ?>
                                        <div class="dx-doc-row">
                                            <div class="dx-doc-name">
                                                <span class="dx-doc-icon <?= $type === 'report' ? 'is-report' : 'is-proposal' ?>"><?= $dxIcon('file', 17) ?></span>
                                                <?= esc($document['label'] ?? '-') ?>
                                            </div>
                                            <div class="dx-doc-code">
                                                Kode:
                                                <?php if ($documentHref !== ''): ?>
                                                    <a href="<?= esc($documentHref) ?>" target="_blank" rel="noopener"><?= esc($docCode) ?></a>
                                                <?php else: ?>
                                                    <span><?= esc($docCode) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div><span class="dx-chip <?= esc($dxDocStatusClass($statusText, $hasLink), 'attr') ?>"><?= esc($statusText) ?></span></div>
                                            <div class="dx-doc-note">
                                                <?php if ($hasLink): ?>
                                                    <a href="<?= esc($documentHref) ?>" target="_blank" rel="noopener" title="<?= esc($documentDisplay, 'attr') ?>"><?= esc($documentDisplay) ?></a>
                                                    <?php if (! empty($document['submitted_at'])): ?>
                                                        &middot; <?= esc(date('d M Y H:i', strtotime((string) $document['submitted_at']))) ?><?= ! empty($document['pic_name']) ? ' oleh ' . esc($document['pic_name']) : '' ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    Belum ada dokumen dari upload event.
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($isAdminSystem): ?>
                                                    <button type="button" class="dx-btn dx-btn-outline dx-btn-sm" data-dx-upload="<?= esc($formId, 'attr') ?>"><?= $dxIcon('upload', 14) ?> Upload</button>
                                                <?php else: ?>
                                                    <a class="dx-btn dx-btn-outline dx-btn-sm" href="<?= site_url('events/' . $eventCode) ?>"><?= $dxIcon('upload', 14) ?> Upload</a>
                                                <?php endif; ?>
                                            </div>
                                            <details class="dx-menu">
                                                <summary aria-label="Menu dokumen"><?= $dxIcon('more', 18) ?></summary>
                                                <div class="dx-menu-pop">
                                                    <?php if ($documentHref !== ''): ?>
                                                        <a href="<?= esc($documentHref) ?>" target="_blank" rel="noopener"><?= $dxIcon('external', 14) ?> Buka dokumen</a>
                                                    <?php endif; ?>
                                                    <a href="<?= site_url('events/' . $eventCode) ?>"><?= $dxIcon('calendar', 14) ?> Detail event</a>
                                                    <?php if ($isAdminSystem && $hasLink): ?>
                                                        <form action="<?= $deleteAction ?>" method="post" onsubmit="return confirm('Hapus dokumen <?= esc($document['label'] ?? '', 'js') ?> untuk event ini?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="campus" value="<?= esc($selectedDocumentCampus, 'attr') ?>">
                                                            <input type="hidden" name="selected_document_type" value="<?= esc($selectedDocumentType, 'attr') ?>">
                                                            <button type="submit" class="is-danger"><?= $dxIcon('trash', 14) ?> Hapus dokumen</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </details>
                                        </div>
                                        <?php if ($isAdminSystem): ?>
                                            <form class="dx-doc-form" id="<?= esc($formId, 'attr') ?>" action="<?= $formAction ?>" method="post" enctype="multipart/form-data" hidden>
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="campus" value="<?= esc($selectedDocumentCampus, 'attr') ?>">
                                                <input type="hidden" name="selected_document_type" value="<?= esc($selectedDocumentType, 'attr') ?>">
                                                <label>
                                                    File <?= esc(strtolower((string) ($document['label'] ?? 'dokumen'))) ?> (PDF/DOC/DOCX)
                                                    <input type="file" name="document_file" accept=".doc,.docx,.pdf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                                </label>
                                                <?php if ($type === 'report'): ?>
                                                    <label>
                                                        Status laporan
                                                        <input type="text" name="realization_status" value="<?= esc($document['status'] ?? '', 'attr') ?>">
                                                    </label>
                                                    <label>
                                                        Skor
                                                        <input type="number" min="0" max="100" name="score" value="<?= esc((string) ($document['score'] ?? 0), 'attr') ?>">
                                                    </label>
                                                    <label class="dx-wide">
                                                        Catatan
                                                        <textarea name="notes" rows="2"><?= esc($document['notes'] ?? '') ?></textarea>
                                                    </label>
                                                <?php else: ?>
                                                    <label>
                                                        Status proposal
                                                        <input type="text" name="status" value="<?= esc($document['status'] ?? '', 'attr') ?>">
                                                    </label>
                                                <?php endif; ?>
                                                <div style="display:flex; gap:8px;">
                                                    <button type="submit" class="dx-btn dx-btn-primary dx-btn-sm">Simpan</button>
                                                    <button type="button" class="dx-btn dx-btn-sm" data-dx-upload="<?= esc($formId, 'attr') ?>">Batal</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($documentRows === []): ?>
                        <tr>
                            <td colspan="8">
                                <div class="dx-empty">
                                    <strong>Belum ada dokumen</strong>
                                    Tidak ada event yang sesuai dengan pencarian atau filter yang dipilih.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dx-foot">
            <span>Menampilkan <?= esc((string) $documentPagination['from']) ?> - <?= esc((string) $documentPagination['to']) ?> dari <?= esc(number_format((int) $documentPagination['total'], 0, ',', '.')) ?> event</span>
            <?php if (($documentPagination['totalPages'] ?? 1) > 1): ?>
                <?php $currentPage = (int) $documentPagination['current']; $lastPage = (int) $documentPagination['totalPages']; ?>
                <nav class="dx-pages" aria-label="Navigasi halaman dokumen">
                    <a class="dx-page-btn <?= $currentPage <= 1 ? 'is-disabled' : '' ?>" href="<?= $currentPage <= 1 ? '#' : esc($documentPageUrl($currentPage - 1)) ?>" aria-label="Sebelumnya"><?= $dxIcon('chevron-left', 15) ?></a>
                    <?php for ($page = 1; $page <= $lastPage; $page++): ?>
                        <?php if ($page === 1 || $page === $lastPage || abs($page - $currentPage) <= 2): ?>
                            <a class="dx-page-btn <?= $page === $currentPage ? 'is-active' : '' ?>" href="<?= esc($documentPageUrl($page)) ?>"><?= esc((string) $page) ?></a>
                        <?php elseif (abs($page - $currentPage) === 3): ?>
                            <span style="padding:0 2px;">…</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <a class="dx-page-btn <?= $currentPage >= $lastPage ? 'is-disabled' : '' ?>" href="<?= $currentPage >= $lastPage ? '#' : esc($documentPageUrl($currentPage + 1)) ?>" aria-label="Berikutnya"><?= $dxIcon('chevron-right', 15) ?></a>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
(function () {
    // Expand / collapse event document rows
    document.querySelectorAll('[data-dx-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var detail = document.getElementById(btn.getAttribute('data-dx-toggle'));
            var row = btn.closest('.dx-row');
            if (!detail || !row) return;
            var open = detail.hasAttribute('hidden');
            if (open) { detail.removeAttribute('hidden'); } else { detail.setAttribute('hidden', ''); }
            row.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
    document.querySelectorAll('.dx-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, summary, form')) return;
            var btn = row.querySelector('[data-dx-toggle]');
            if (btn) btn.click();
        });
    });

    // Toggle admin upload form
    document.querySelectorAll('[data-dx-upload]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById(btn.getAttribute('data-dx-upload'));
            if (!form) return;
            if (form.hasAttribute('hidden')) {
                form.removeAttribute('hidden');
                var file = form.querySelector('input[type="file"]');
                if (file) file.focus();
            } else {
                form.setAttribute('hidden', '');
            }
        });
    });

    // Close kebab menus when clicking outside
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.dx-menu[open]').forEach(function (menu) {
            if (!menu.contains(e.target)) menu.removeAttribute('open');
        });
    });

    // Advanced filter toggle
    var toggleFilter = document.querySelector('[data-dx-toggle-filter]');
    var filterGrid = document.querySelector('[data-dx-filter-grid]');
    if (toggleFilter && filterGrid) {
        toggleFilter.addEventListener('click', function () {
            var show = filterGrid.hasAttribute('hidden');
            if (show) { filterGrid.removeAttribute('hidden'); } else { filterGrid.setAttribute('hidden', ''); }
            toggleFilter.classList.toggle('is-active', show);
            toggleFilter.setAttribute('aria-expanded', show ? 'true' : 'false');
        });
    }

    // Sort & per-page selectors submit the main filter form
    var filterForm = document.getElementById('dx-filter-form');
    document.querySelectorAll('[data-dx-sync]').forEach(function (select) {
        select.addEventListener('change', function () {
            if (!filterForm) return;
            var input = filterForm.querySelector('input[name="' + select.getAttribute('data-dx-sync') + '"]');
            if (input) input.value = select.value;
            filterForm.submit();
        });
    });
})();
</script>
