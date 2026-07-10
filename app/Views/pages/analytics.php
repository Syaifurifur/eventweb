<?php
$campuses = $campuses ?? [];
$selectedAnalyticsCampusId = $selectedAnalyticsCampusId ?? null;
$selectedAnalyticsCampusName = $selectedAnalyticsCampusName ?? 'Semua kampus';
$selectedAnalyticsYear = $selectedAnalyticsYear ?? '';
$selectedAnalyticsMonth = $selectedAnalyticsMonth ?? '';
$selectedAnalyticsPeriodLabel = $selectedAnalyticsPeriodLabel ?? 'Semua bulan';
$analyticsYears = $analyticsYears ?? [];
if ($analyticsYears === []) {
    $analyticsYears = [date('Y')];
}
$analyticsMonths = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
];
$crmSummary = $crmSummary ?? [
    'total' => 0,
    'fresh' => 0,
    'stage2_responded' => 0,
    'stage2_no_response' => 0,
    'rejected' => 0,
    'closing' => 0,
    'score' => 0,
    'tone' => 'red',
];
$crmRows = $crmRows ?? [];
$eventDistribution = $eventDistribution ?? [];
$oneCampusSections = $oneCampusSections ?? [];
$formatAnalyticsDate = static function (string $date): string {
    if ($date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d M Y', $timestamp) : '-';
};
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">Analytics Kampus</span>
        <h2><?= esc($selectedAnalyticsCampusName) ?></h2>
        <small><?= esc($selectedAnalyticsPeriodLabel) ?></small>
    </div>
    <form class="analytics-campus-filter" action="<?= site_url('analytics') ?>" method="get">
        <select class="control" name="campus_id" aria-label="Pilih kampus">
            <option value="">Semua kampus</option>
            <?php foreach ($campuses as $campus): ?>
                <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($selectedAnalyticsCampusId ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                    <?= esc($campus['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="control" name="year" aria-label="Pilih tahun pelaksanaan">
            <option value="">Semua tahun</option>
            <?php foreach ($analyticsYears as $year): ?>
                <option value="<?= esc((string) $year) ?>" <?= (string) $selectedAnalyticsYear === (string) $year ? 'selected' : '' ?>>
                    <?= esc((string) $year) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="control" name="month" aria-label="Pilih bulan pelaksanaan">
            <option value="">Semua bulan</option>
            <?php foreach ($analyticsMonths as $monthValue => $monthLabel): ?>
                <option value="<?= esc($monthValue) ?>" <?= (string) $selectedAnalyticsMonth === (string) $monthValue ? 'selected' : '' ?>>
                    <?= esc($monthLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="primary-button compact-button" type="submit">Terapkan</button>
        <a class="ghost-button compact-button" href="<?= site_url('analytics') ?>">Reset</a>
    </form>
</div>

<section class="metric-grid">
    <?php foreach ($stats as $stat): ?>
        <article class="metric-card tone-<?= esc($stat['tone']) ?>">
            <span><?= esc($stat['label']) ?></span>
            <strong><?= esc(number_format((float) $stat['value'], 0, ',', '.')) ?><?= esc($stat['suffix'] ?? '') ?></strong>
            <small><?= esc($stat['trend']) ?></small>
        </article>
    <?php endforeach; ?>
</section>

<div class="analytics-grid">
    <section class="panel wide analytics-distribution-panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Distribusi Event</span>
                <h2>Distribusi event</h2>
            </div>
        </div>
        <div class="analytics-distribution-grid">
            <?php foreach ($eventDistribution as $distribution): ?>
                <article class="analytics-distribution-card tone-<?= esc($distribution['tone'] ?? 'blue') ?>">
                    <div class="analytics-distribution-head">
                        <div>
                            <strong><?= esc($distribution['label']) ?></strong>
                            <small><?= esc($distribution['description']) ?></small>
                        </div>
                        <div class="analytics-distribution-total">
                            <span><?= esc((string) ($distribution['count'] ?? 0)) ?></span>
                            <small class="<?= ! empty($distribution['target_met']) ? 'is-target-met' : 'is-target-missed' ?>">
                                <?= ! empty($distribution['target_met']) ? 'Tercapai' : 'Belum tercapai' ?>
                            </small>
                            <em><?= esc($distribution['target_label'] ?? '') ?></em>
                        </div>
                    </div>
                    <div class="bar-list compact-bar-list">
                        <?php foreach (($distribution['categories'] ?? []) as $category): ?>
                            <div class="bar-item">
                                <div>
                                    <strong><?= esc($category['name']) ?></strong>
                                    <small><?= esc((string) $category['count']) ?> event</small>
                                </div>
                                <span class="bar-track"><span style="width: <?= esc((string) $category['percent']) ?>%"></span></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (($distribution['categories'] ?? []) === []): ?>
                            <p class="muted-note">Belum ada event pada kelompok ini.</p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">CRM</span>
                <h2>Hasil CRM</h2>
            </div>
            <span class="status status-<?= esc($crmSummary['tone'] === 'green' ? 'aktif' : ($crmSummary['tone'] === 'amber' ? 'diajukan' : 'ditolak')) ?>">
                <?= esc((string) $crmSummary['score']) ?>%
            </span>
        </div>
        <div class="analytics-crm-summary">
            <div>
                <small>Closing</small>
                <strong><?= esc((string) $crmSummary['closing']) ?></strong>
            </div>
            <div>
                <small>Tahap 2 respon</small>
                <strong><?= esc((string) $crmSummary['stage2_responded']) ?></strong>
            </div>
            <div>
                <small>Tidak respon</small>
                <strong><?= esc((string) $crmSummary['stage2_no_response']) ?></strong>
            </div>
            <div>
                <small>Menolak</small>
                <strong><?= esc((string) $crmSummary['rejected']) ?></strong>
            </div>
        </div>
    </section>

    <section class="panel wide">
        <div class="panel-header">
            <div>
                <span class="eyebrow">One Campus</span>
                <h2>Rekap aktivitas kampus</h2>
            </div>
        </div>
        <div class="one-campus-analytics-grid">
            <?php foreach ($oneCampusSections as $section): ?>
                <article class="one-campus-analytics-card">
                    <div class="one-campus-analytics-head">
                        <div>
                            <span class="eyebrow"><?= esc($section['label']) ?></span>
                            <strong><?= esc((string) ($section['count'] ?? 0)) ?></strong>
                        </div>
                        <span class="status status-calendar"><?= esc($section['meta'] ?? '') ?></span>
                    </div>
                    <div class="analytics-data-list compact-analytics-list">
                        <?php foreach (($section['rows'] ?? []) as $row): ?>
                            <div class="event-row analytics-mini-row">
                                <span class="date-pill"><?= esc($formatAnalyticsDate((string) ($row['date'] ?? ''))) ?></span>
                                <div>
                                    <strong><?= esc($row['title'] ?? '-') ?></strong>
                                    <small><?= esc($row['detail'] ?? '-') ?> - <?= esc($row['campus'] ?? 'Tanpa kampus') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (($section['rows'] ?? []) === []): ?>
                            <div class="notification-item">
                                <strong><?= esc($section['empty'] ?? 'Belum ada data.') ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">CRM</span>
                <h2>Jumlah data terkumpul</h2>
            </div>
            <span class="status"><?= esc((string) $crmSummary['total']) ?> data</span>
        </div>
        <div class="analytics-data-list">
            <?php foreach (array_slice($crmRows, 0, 6) as $row): ?>
                <a class="event-row" href="<?= site_url('crm?event_id=' . urlencode((string) $row['event_id'])) ?>">
                    <span class="date-pill"><?= esc((string) $row['total']) ?></span>
                    <div>
                        <strong><?= esc($row['event_code']) ?> - <?= esc($row['title']) ?></strong>
                        <small><?= esc((string) $row['score']) ?>% hasil CRM - <?= esc($row['campus']) ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if ($crmRows === []): ?>
                <div class="notification-item">
                    <strong>Belum ada data CRM</strong>
                    <p>Upload data CRM pada event kampus ini untuk melihat jumlah data terkumpul.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel wide">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Event</span>
                <h2>Event pada kampus terpilih</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Peserta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <a class="table-title" href="<?= site_url('events/' . $event['id']) ?>"><?= esc($event['title']) ?></a>
                                <small><?= esc($event['campus']) ?></small>
                            </td>
                            <td><?= esc($event['category']) ?></td>
                            <td><?= esc($event['date_label'] ?? date('d M Y', strtotime($event['date']))) ?></td>
                            <td><span class="status status-<?= esc($event['status']) ?>"><?= esc($event['status']) ?></span></td>
                            <td><?= esc((string) $event['participants_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($events === []): ?>
                        <tr>
                            <td colspan="5"><span class="muted-note">Belum ada event untuk kampus ini.</span></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
