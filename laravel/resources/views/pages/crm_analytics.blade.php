<?php
$analytics = $analytics ?? [];
$summary = $analytics['summary'] ?? [];
$funnel = $analytics['funnel'] ?? [];
$trend = $analytics['trend'] ?? [];
$aging = $analytics['aging'] ?? [];
$alerts = $analytics['alerts'] ?? [];
$quality = $analytics['quality'] ?? [];
$scorecards = $analytics['scorecards'] ?? [];
$filters = $filters ?? [];
$currentUser = $crmUser ?? [];
$drilldown = $drilldown ?? null;
$number = static fn ($value): string => number_format((float) $value, 0, ',', '.');
$percent = static fn ($value): string => number_format((float) $value, 1, ',', '.') . '%';
$filterQuery = array_filter($filters, static fn ($value): bool => $value !== '' && $value !== null);
$analyticsUrl = static function (array $changes = []) use ($filterQuery): string {
    $query = array_merge($filterQuery, $changes);
    $query = array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);

    return site_url('crm/analytics' . ($query !== [] ? '?' . http_build_query($query) : ''));
};
$drilldownUrl = static fn (string $focus): string => $analyticsUrl(['focus' => $focus]) . '#crm-analytics-drilldown';
$scopeText = ($isAdmin ?? false)
    ? (($campusScope ?? null) !== null ? 'Kampus sesuai scope akun' : 'Seluruh kampus')
    : 'Leads milik agent yang sedang login';
$executiveKpis = [
    ['label' => 'Total Leads', 'value' => $summary['total'] ?? 0, 'meta' => 'Partisipasi pada filter aktif', 'focus' => 'total', 'tone' => 'blue'],
    ['label' => 'Belum Assign', 'value' => $summary['unassigned'] ?? 0, 'meta' => $percent(100 - (float) ($summary['assignment_rate'] ?? 0)) . ' dari leads valid', 'focus' => 'unassigned', 'tone' => 'red'],
    ['label' => 'Sudah Diproses', 'value' => $summary['processed'] ?? 0, 'meta' => $percent($summary['processing_rate'] ?? 0) . ' dari assigned', 'focus' => 'processed', 'tone' => 'cyan'],
    ['label' => 'Closing', 'value' => $summary['closing'] ?? 0, 'meta' => $percent($summary['closing_rate_processed'] ?? 0) . ' dari processed', 'focus' => 'closing', 'tone' => 'green'],
    ['label' => 'SLA <=24 Jam', 'value' => $percent($summary['sla_rate'] ?? 0), 'meta' => $number($summary['sla_within'] ?? 0) . '/' . $number($summary['sla_eligible'] ?? 0) . ' eligible', 'focus' => 'sla_missed', 'tone' => 'violet'],
];
$secondaryKpis = [
    ['label' => 'WA Unik', 'value' => $summary['unique'] ?? 0, 'meta' => $number($summary['valid'] ?? 0) . ' nomor valid', 'focus' => 'valid', 'tone' => 'ink'],
    ['label' => 'Fresh', 'value' => $summary['fresh'] ?? 0, 'meta' => $number($summary['fresh7'] ?? 0) . ' berusia >7 hari', 'focus' => 'fresh', 'tone' => 'amber'],
    ['label' => 'Data Quality', 'value' => $percent($summary['quality_score'] ?? 0), 'meta' => $number($summary['quality_records'] ?? 0) . ' record berisiko', 'focus' => 'data_quality', 'tone' => 'slate'],
];
$qualityRows = [
    ['label' => 'WA invalid/kosong', 'value' => $quality['invalid_wa'] ?? 0, 'focus' => 'invalid_wa'],
    ['label' => 'Tambahan WA berulang', 'value' => $quality['duplicate'] ?? 0, 'focus' => 'duplicate'],
    ['label' => 'Sekolah belum lengkap', 'value' => $quality['missing_school'] ?? 0, 'focus' => 'missing_school'],
    ['label' => 'Processed tanpa owner', 'value' => $quality['processed_unassigned'] ?? 0, 'focus' => 'processed_unassigned'],
    ['label' => 'Closing belum verified', 'value' => $quality['closing_unverified'] ?? 0, 'focus' => 'closing_unverified'],
];
$crmLabels = ['Closing', 'Minat/Tertarik', 'Ragu-ragu', 'Menolak', 'Tidak Merespon'];
$topCampuses = array_slice($scorecards['campuses'] ?? [], 0, 5);
$topCampusMax = max(1, ...array_map(static fn (array $row): int => (int) ($row['total'] ?? 0), $topCampuses));
$trendInputPoints = [];
$trendClosingPoints = [];
$trendLabels = [];
$trendCount = max(1, count($trend));
foreach (array_values($trend) as $index => $bucket) {
    $x = $trendCount === 1 ? 200 : 18 + ($index * (364 / ($trendCount - 1)));
    $trendInputPoints[] = round($x, 1) . ',' . round(126 - ((float) ($bucket['input_height'] ?? 0) * 0.94), 1);
    $trendClosingPoints[] = round($x, 1) . ',' . round(126 - ((float) ($bucket['closing_height'] ?? 0) * 0.94), 1);
    $trendLabels[] = (string) ($bucket['label'] ?? '');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Analisis CRM') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="crm-page crm-analytics-page">
    <header class="crm-analytics-brandbar">
        <a href="<?= site_url('crm/analytics') ?>">
            <strong>BSI FLASH</strong>
            <i aria-hidden="true"></i>
            <span>CRM ANALYTICS</span>
        </a>
        <div class="crm-analytics-account">
            <span><?= esc($currentUser['name'] ?? 'Agent') ?></span>
            <form action="<?= site_url('crm/logout') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit">Logout</button>
            </form>
        </div>
    </header>

    <div class="crm-analytics-shell">
        <aside class="crm-analytics-sidebar">
            <nav aria-label="Navigasi analitik CRM">
                <a class="is-active" href="#dashboard-eksekutif">Dashboard</a>
                <a href="<?= site_url('crm') ?>">Leads &amp; Work Queue</a>
                <a href="#scorecard-campuses">Kampus</a>
                <a href="#scorecard-events">Event</a>
                <a href="#scorecard-agents">Agent CRM</a>
                <a href="#scorecard-schools">Sekolah</a>
                <a href="#data-quality">Data Quality</a>
                <a href="#performance-analytics">Laporan</a>
            </nav>
            <div class="crm-analytics-sidebar-foot">
                <a href="<?= site_url('crm/monitoring') ?>">Monitoring Agent</a>
                <a href="<?= site_url('dashboard') ?>">EventHub</a>
            </div>
        </aside>

        <main class="crm-content crm-analytics-content" id="dashboard-eksekutif">
        <header class="crm-analytics-heading">
            <div>
                <span class="crm-analytics-eyebrow">Pusat Kendali CRM Event</span>
                <h1>Dashboard Eksekutif</h1>
                <p><?= esc($scopeText) ?> | <?= $number($summary['total'] ?? 0) ?> leads pada filter aktif</p>
            </div>
            <div class="crm-freshness">
                <span>Data terakhir diperbarui</span>
                <strong><?= esc(! empty($analytics['freshness']) ? date('d M Y, H.i', strtotime($analytics['freshness'])) . ' WIB' : '-') ?></strong>
            </div>
        </header>

        <section class="crm-executive-kpis" aria-label="KPI utama CRM">
            <?php foreach ($executiveKpis as $kpi): ?>
                <a class="crm-executive-kpi tone-<?= esc($kpi['tone']) ?>" href="<?= esc($drilldownUrl($kpi['focus'])) ?>">
                    <span><?= esc($kpi['label']) ?></span>
                    <strong><?= is_numeric($kpi['value']) ? $number($kpi['value']) : esc($kpi['value']) ?></strong>
                    <small><?= esc($kpi['meta']) ?></small>
                </a>
            <?php endforeach; ?>
        </section>

        <div class="crm-executive-grid crm-executive-grid-primary">
            <section class="crm-executive-panel">
                <header><h2>Funnel CRM</h2><small>Denominator: seluruh leads</small></header>
                <div class="crm-executive-funnel">
                    <?php foreach ($funnel as $index => $step): ?>
                        <a class="step-<?= esc((string) $index) ?>" href="<?= esc($drilldownUrl($step['focus'])) ?>">
                            <span><?= esc($step['label']) ?>: <?= $number($step['value']) ?></span>
                            <strong><?= $percent($step['rate']) ?></strong>
                            <i style="width: <?= esc((string) max(7, (float) $step['rate'])) ?>%"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="crm-executive-panel">
                <header><h2>Alert Prioritas</h2><small><?= $number(array_sum(array_column($alerts, 'value'))) ?> temuan</small></header>
                <div class="crm-executive-alerts">
                    <?php foreach ($alerts as $alert): ?>
                        <a class="severity-<?= esc($alert['severity']) ?>" href="<?= esc($drilldownUrl($alert['focus'])) ?>">
                            <i aria-hidden="true"></i>
                            <span><strong><?= $number($alert['value']) ?></strong> <?= esc($alert['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <div class="crm-executive-grid crm-executive-grid-secondary">
            <section class="crm-executive-panel">
                <header><h2>Tren Leads vs Closing</h2><small>Enam bulan terakhir</small></header>
                <div class="crm-line-chart">
                    <svg viewBox="0 0 400 138" role="img" aria-label="Grafik tren leads dan closing enam bulan terakhir">
                        <line x1="18" y1="126" x2="382" y2="126" class="axis"></line>
                        <line x1="18" y1="79" x2="382" y2="79" class="guide"></line>
                        <line x1="18" y1="32" x2="382" y2="32" class="guide"></line>
                        <polyline points="<?= esc(implode(' ', $trendInputPoints)) ?>" class="line-input"></polyline>
                        <polyline points="<?= esc(implode(' ', $trendClosingPoints)) ?>" class="line-closing"></polyline>
                        <?php foreach ($trendInputPoints as $point): ?>
                            <?php [$cx, $cy] = explode(',', $point); ?>
                            <circle cx="<?= esc($cx) ?>" cy="<?= esc($cy) ?>" r="3.5" class="point-input"></circle>
                        <?php endforeach; ?>
                        <?php foreach ($trendClosingPoints as $point): ?>
                            <?php [$cx, $cy] = explode(',', $point); ?>
                            <circle cx="<?= esc($cx) ?>" cy="<?= esc($cy) ?>" r="2.7" class="point-closing"></circle>
                        <?php endforeach; ?>
                    </svg>
                    <div class="crm-line-chart-labels">
                        <?php foreach ($trendLabels as $label): ?><span><?= esc($label) ?></span><?php endforeach; ?>
                    </div>
                    <div class="crm-line-chart-legend"><span class="leads">Leads</span><span class="closing">Closing</span></div>
                </div>
            </section>

            <section class="crm-executive-panel">
                <header><h2>Top Kampus (Volume)</h2><small>Top <?= $number(count($topCampuses)) ?></small></header>
                <div class="crm-campus-ranking">
                    <?php foreach ($topCampuses as $campus): ?>
                        <div>
                            <span><?= esc($campus['label']) ?></span>
                            <i><b style="width: <?= esc((string) max(4, ((int) $campus['total'] / $topCampusMax) * 100)) ?>%"></b></i>
                            <strong><?= $number($campus['total']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($topCampuses === []): ?><p>Belum ada data kampus.</p><?php endif; ?>
                </div>
            </section>
        </div>

        <section class="crm-analytics-filter" aria-label="Filter analisis CRM">
            <form action="<?= site_url('crm/analytics') ?>" method="get">
                <label>
                    Dari tanggal input
                    <input class="control" type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>">
                </label>
                <label>
                    Sampai tanggal input
                    <input class="control" type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>">
                </label>
                <label>
                    Kampus
                    <select class="control" name="campus_id" <?= ($campusScope ?? null) !== null ? 'disabled' : '' ?>>
                        <option value="">Semua kampus</option>
                        <?php foreach (($campuses ?? []) as $campus): ?>
                            <option value="<?= esc($campus['id']) ?>" <?= (string) ($filters['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>><?= esc($campus['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Kategori event
                    <select class="control" name="category">
                        <option value="">Semua kategori</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc($category) ?>" <?= ($filters['category'] ?? '') === $category ? 'selected' : '' ?>><?= esc($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="crm-analytics-filter-wide">
                    Event
                    <select class="control" name="event_id">
                        <option value="">Semua event</option>
                        <?php foreach (($events ?? []) as $event): ?>
                            <option value="<?= esc($event['id']) ?>" <?= (string) ($filters['event_id'] ?? '') === (string) $event['id'] ? 'selected' : '' ?>><?= esc(($event['code'] ?? '') . ' - ' . ($event['title'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Agent
                    <select class="control" name="agent_id">
                        <option value="">Semua agent</option>
                        <?php foreach (($agents ?? []) as $agent): ?>
                            <option value="<?= esc($agent['id']) ?>" <?= (string) ($filters['agent_id'] ?? '') === (string) $agent['id'] ? 'selected' : '' ?>><?= esc($agent['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Sekolah
                    <select class="control" name="school">
                        <option value="">Semua sekolah</option>
                        <?php foreach (($schools ?? []) as $school): ?>
                            <option value="<?= esc($school) ?>" <?= ($filters['school'] ?? '') === $school ? 'selected' : '' ?>><?= esc($school) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tahap
                    <select class="control" name="stage">
                        <option value="">Semua tahap</option>
                        <?php foreach (['Tahap 1', 'Tahap 2', 'Tahap 3'] as $stage): ?>
                            <option value="<?= esc($stage) ?>" <?= ($filters['stage'] ?? '') === $stage ? 'selected' : '' ?>><?= esc($stage) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Hasil CRM
                    <select class="control" name="label">
                        <option value="">Semua hasil</option>
                        <?php foreach ($crmLabels as $label): ?>
                            <option value="<?= esc($label) ?>" <?= ($filters['label'] ?? '') === $label ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Assignment
                    <select class="control" name="assignment">
                        <option value="">Semua status</option>
                        <option value="assigned" <?= ($filters['assignment'] ?? '') === 'assigned' ? 'selected' : '' ?>>Sudah assign</option>
                        <option value="unassigned" <?= ($filters['assignment'] ?? '') === 'unassigned' ? 'selected' : '' ?>>Belum assign</option>
                    </select>
                </label>
                <label>
                    Status SLA
                    <select class="control" name="sla_status">
                        <option value="">Semua status</option>
                        <option value="within" <?= ($filters['sla_status'] ?? '') === 'within' ? 'selected' : '' ?>>Dalam 24 jam</option>
                        <option value="missed" <?= ($filters['sla_status'] ?? '') === 'missed' ? 'selected' : '' ?>>Melewati 24 jam</option>
                        <option value="pending" <?= ($filters['sla_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Belum jatuh tempo</option>
                    </select>
                </label>
                <label>
                    Kualitas data
                    <select class="control" name="quality">
                        <option value="">Semua data</option>
                        <option value="any" <?= ($filters['quality'] ?? '') === 'any' ? 'selected' : '' ?>>Memiliki isu</option>
                        <option value="invalid_wa" <?= ($filters['quality'] ?? '') === 'invalid_wa' ? 'selected' : '' ?>>WA invalid</option>
                        <option value="duplicate" <?= ($filters['quality'] ?? '') === 'duplicate' ? 'selected' : '' ?>>WA berulang</option>
                        <option value="missing_school" <?= ($filters['quality'] ?? '') === 'missing_school' ? 'selected' : '' ?>>Sekolah kosong</option>
                    </select>
                </label>
                <div class="crm-analytics-filter-actions">
                    <a class="ghost-button" href="<?= site_url('crm/analytics') ?>">Reset</a>
                    <button class="primary-button" type="submit">Terapkan</button>
                </div>
            </form>
        </section>

        <div class="crm-analytics-context">
            <span><?= $number(count($filterQuery)) ?> filter aktif</span>
            <p>SLA menggunakan estimasi <code>responded_at - created_at</code>. Closing belum terhubung ke referensi resmi PMB.</p>
        </div>

        <section class="crm-analytics-kpis crm-analytics-kpis-secondary" aria-label="KPI pendukung CRM">
            <?php foreach ($secondaryKpis as $kpi): ?>
                <a class="crm-analytics-kpi tone-<?= esc($kpi['tone']) ?>" href="<?= esc($drilldownUrl($kpi['focus'])) ?>">
                    <span><?= esc($kpi['label']) ?></span>
                    <strong><?= is_numeric($kpi['value']) ? $number($kpi['value']) : esc($kpi['value']) ?></strong>
                    <small><?= esc($kpi['meta']) ?></small>
                </a>
            <?php endforeach; ?>
        </section>

        <?php if ($drilldown !== null): ?>
            <section class="crm-analytics-panel crm-drilldown-panel" id="crm-analytics-drilldown">
                <header class="crm-analytics-panel-head">
                    <div>
                        <span>Drill-down</span>
                        <h2><?= esc($drilldown['label']) ?></h2>
                    </div>
                    <div class="crm-drilldown-actions">
                        <strong><?= $number($drilldown['total']) ?> leads</strong>
                        <a class="ghost-button compact-button" href="<?= esc($analyticsUrl(['focus' => null])) ?>">Tutup</a>
                    </div>
                </header>
                <div class="crm-analytics-table-wrap">
                    <table class="crm-analytics-table crm-drilldown-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>WA</th>
                                <th>Sekolah</th>
                                <th>Event</th>
                                <th>Kampus</th>
                                <th>Agent</th>
                                <th>Tahap</th>
                                <th>Hasil</th>
                                <th>Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($drilldown['rows'] ?? []) as $lead): ?>
                                <?php
                                    $rawWa = preg_replace('/\D+/', '', (string) ($lead['whatsapp'] ?? '')) ?? '';
                                    $maskedWa = strlen($rawWa) >= 7 ? substr($rawWa, 0, 4) . '****' . substr($rawWa, -3) : ($rawWa !== '' ? '****' : '-');
                                ?>
                                <tr>
                                    <td><strong><?= esc($lead['student_name'] ?? '-') ?></strong></td>
                                    <td><?= esc($maskedWa) ?></td>
                                    <td><?= esc($lead['school_origin'] ?: '-') ?></td>
                                    <td><?= esc($lead['event_code'] ?: '-') ?></td>
                                    <td><?= esc($lead['campus_name'] ?: '-') ?></td>
                                    <td><?= esc($lead['agent_name'] ?: 'Belum assign') ?></td>
                                    <td><?= esc($lead['stage'] ?: '-') ?></td>
                                    <td><?= esc($lead['label'] ?: 'Fresh') ?></td>
                                    <td><?= esc(! empty($lead['created_at']) ? date('d M Y', strtotime($lead['created_at'])) : '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (($drilldown['rows'] ?? []) === []): ?>
                                <tr><td colspan="9" class="crm-empty">Tidak ada leads pada kelompok ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (($drilldown['total'] ?? 0) > count($drilldown['rows'] ?? [])): ?>
                    <p class="crm-analytics-table-note">Menampilkan 25 leads tertua dari kelompok ini.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <div class="crm-analytics-grid crm-analytics-grid-secondary" id="data-quality">
            <section class="crm-analytics-panel">
                <header class="crm-analytics-panel-head">
                    <div>
                        <span>Backlog Aging</span>
                        <h2>Usia leads yang masih aktif</h2>
                    </div>
                    <strong><?= $number($summary['backlog'] ?? 0) ?> backlog</strong>
                </header>
                <div class="crm-aging-list">
                    <?php foreach ($aging as $bucket): ?>
                        <div>
                            <span><?= esc($bucket['label']) ?></span>
                            <div class="crm-aging-track"><i class="<?= esc($bucket['class']) ?>" style="width: <?= esc((string) max(2, $bucket['width'])) ?>%"></i></div>
                            <strong><?= $number($bucket['count']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="crm-analytics-panel">
                <header class="crm-analytics-panel-head">
                    <div>
                        <span>Data Quality</span>
                        <h2>Antrean koreksi data</h2>
                    </div>
                    <strong><?= $percent($summary['quality_score'] ?? 0) ?> score</strong>
                </header>
                <div class="crm-quality-list">
                    <?php foreach ($qualityRows as $row): ?>
                        <a href="<?= esc($drilldownUrl($row['focus'])) ?>">
                            <span><?= esc($row['label']) ?></span>
                            <strong><?= $number($row['value']) ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <section class="crm-scorecard-section" id="performance-analytics">
            <header class="crm-scorecard-heading">
                <div>
                    <span>Performance Analytics</span>
                    <h2>Scorecard lintas dimensi</h2>
                </div>
                <p>Conversion ditampilkan jika processed leads minimal <?= $number($analytics['minimumSample'] ?? 20) ?>.</p>
            </header>

            <?php
                $scorecardSections = [
                    ['key' => 'campuses', 'title' => 'Kampus', 'entity' => 'Kampus'],
                    ['key' => 'events', 'title' => 'Event & Kategori', 'entity' => 'Event'],
                    ['key' => 'agents', 'title' => 'Agent CRM', 'entity' => 'Agent'],
                    ['key' => 'schools', 'title' => 'Sekolah', 'entity' => 'Sekolah'],
                ];
            ?>
            <div class="crm-scorecard-grid">
                <?php foreach ($scorecardSections as $section): ?>
                    <section class="crm-analytics-panel crm-scorecard-panel" id="scorecard-<?= esc($section['key'], 'attr') ?>">
                        <header class="crm-analytics-panel-head">
                            <div>
                                <span>Top 10</span>
                                <h3><?= esc($section['title']) ?></h3>
                            </div>
                        </header>
                        <div class="crm-analytics-table-wrap">
                            <table class="crm-analytics-table">
                                <thead>
                                    <tr>
                                        <th><?= esc($section['entity']) ?></th>
                                        <?php if ($section['key'] === 'schools'): ?><th>Event</th><?php endif; ?>
                                        <th>Leads</th>
                                        <th>Process</th>
                                        <th>SLA</th>
                                        <th>Backlog</th>
                                        <th>Closing</th>
                                        <th>Conversion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($scorecards[$section['key']] ?? []) as $row): ?>
                                        <tr>
                                            <td><strong><?= esc($row['label']) ?></strong><small><?= esc($row['meta'] ?: '-') ?></small></td>
                                            <?php if ($section['key'] === 'schools'): ?><td><?= $number($row['events'] ?? 0) ?></td><?php endif; ?>
                                            <td><?= $number($row['total']) ?></td>
                                            <td><?= $percent($row['process_rate']) ?><small><?= $number($row['processed']) ?> data</small></td>
                                            <td><?= $percent($row['sla_rate']) ?></td>
                                            <td><?= $number($row['backlog']) ?></td>
                                            <td><?= $number($row['closing']) ?></td>
                                            <td>
                                                <?php if ($row['sample_ok']): ?>
                                                    <strong><?= $percent($row['closing_rate']) ?></strong>
                                                <?php else: ?>
                                                    <span class="crm-sample-badge">Sample &lt;20</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (($scorecards[$section['key']] ?? []) === []): ?>
                                        <tr><td colspan="<?= $section['key'] === 'schools' ? '8' : '7' ?>" class="crm-empty">Belum ada data pada dimensi ini.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
        </main>
    </div>
</body>
</html>
