<?php
$periodType = $periodType ?? 'year';
$selectedYear = $selectedYear ?? date('Y');
$selectedMonth = $selectedMonth ?? '';
$analyticsYears = $analyticsYears ?? [];
if (! in_array($selectedYear, $analyticsYears, true)) {
    array_unshift($analyticsYears, $selectedYear);
}
$analyticsYears = array_values(array_unique($analyticsYears));
rsort($analyticsYears, SORT_STRING);
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
$summary = $summary ?? [];
$campusRows = $campusRows ?? [];
$notifications = $notifications ?? [];
$monthLabels = $monthLabels ?? [];
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
$strategyDownloadMonth = $selectedMonth !== '' ? $selectedMonth : date('m');
$strategyDownloadUrl = site_url('analisis-khusus/laporan-strategi?' . http_build_query([
    'year' => $selectedYear ?: date('Y'),
    'month' => $strategyDownloadMonth,
]));
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">DMER & Admin Sistem</span>
        <h2>Analisis khusus kampus</h2>
        <small><?= esc($periodLabel ?? 'Periode terpilih') ?></small>
    </div>
    <form class="special-analytics-filter" action="<?= site_url('analisis-khusus') ?>" method="get">
        <select class="control" name="period_type" aria-label="Pilih jenis periode" data-special-period-type>
            <option value="year" <?= $periodType === 'year' ? 'selected' : '' ?>>Per Tahun</option>
            <option value="month" <?= $periodType === 'month' ? 'selected' : '' ?>>Per Bulan</option>
        </select>
        <select class="control" name="year" aria-label="Pilih tahun">
            <?php foreach ($analyticsYears as $year): ?>
                <option value="<?= esc((string) $year) ?>" <?= (string) $selectedYear === (string) $year ? 'selected' : '' ?>>
                    <?= esc((string) $year) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="control" name="month" aria-label="Pilih bulan" data-special-period-month>
            <?php foreach ($analyticsMonths as $monthValue => $monthLabel): ?>
                <option value="<?= esc($monthValue) ?>" <?= (string) $selectedMonth === (string) $monthValue ? 'selected' : '' ?>>
                    <?= esc($monthLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="primary-button compact-button" type="submit">Terapkan</button>
        <a class="ghost-button compact-button" href="<?= esc($strategyDownloadUrl) ?>">Download 5 Strategi</a>
        <a class="ghost-button compact-button" href="<?= site_url('analisis-khusus') ?>">Reset</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.querySelector('[data-special-period-type]');
    const month = document.querySelector('[data-special-period-month]');
    if (!type || !month) {
        return;
    }
    const sync = function () {
        month.disabled = type.value === 'year';
    };
    type.addEventListener('change', sync);
    sync();
});
</script>

<section class="metric-grid">
    <?php foreach ($summary as $stat): ?>
        <article class="metric-card tone-<?= esc($stat['tone']) ?>">
            <span><?= esc($stat['label']) ?></span>
            <strong><?= esc(number_format((float) $stat['value'], 0, ',', '.')) ?><?= esc($stat['suffix'] ?? '') ?></strong>
            <small><?= esc($stat['trend']) ?></small>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel special-target-panel">
    <div class="panel-header">
        <div>
            <span class="eyebrow">Notifikasi target</span>
            <h2>Kampus belum mencapai target</h2>
        </div>
        <span class="status <?= $notifications === [] ? 'status-aktif' : 'status-ditolak' ?>">
            <?= esc((string) count($notifications)) ?> kampus
        </span>
    </div>
    <div class="special-alert-list">
        <?php foreach (array_slice($notifications, 0, 10) as $row): ?>
            <article class="special-alert-item">
                <div>
                    <strong><?= esc($row['campus']) ?></strong>
                    <small>
                        Kurang <?= esc((string) $row['regular_gap']) ?> One Campus Two Event
                        dan <?= esc((string) $row['openhouse_gap']) ?> Hangout/Open House.
                    </small>
                </div>
                <span><?= esc((string) $row['total']) ?>/<?= esc((string) $row['target_total']) ?></span>
            </article>
        <?php endforeach; ?>
        <?php if ($notifications === []): ?>
            <div class="notification-item">
                <strong>Semua kampus mencapai target pada periode ini.</strong>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel wide special-crm-panel">
    <div class="panel-header">
        <div>
            <span class="eyebrow">Laporan CRM</span>
            <h2>CRM periode terpilih</h2>
        </div>
        <span class="status status-<?= esc($crmSummary['tone'] === 'green' ? 'aktif' : ($crmSummary['tone'] === 'amber' ? 'diajukan' : 'ditolak')) ?>">
            <?= esc((string) $crmSummary['score']) ?>%
        </span>
    </div>
    <div class="special-crm-grid">
        <div class="special-crm-score">
            <span>Data CRM</span>
            <strong><?= esc(number_format((float) $crmSummary['total'], 0, ',', '.')) ?></strong>
            <small>Total data terkumpul dari event pada periode ini.</small>
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
    </div>
    <div class="table-wrap special-crm-table-wrap">
        <table class="data-table special-crm-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Kampus</th>
                    <th>Total</th>
                    <th>Closing</th>
                    <th>Respon</th>
                    <th>Skor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($crmRows, 0, 10) as $row): ?>
                    <tr>
                        <td>
                            <a class="table-title" href="<?= site_url('crm?event_id=' . urlencode((string) $row['event_id'])) ?>">
                                <?= esc($row['event_code']) ?> - <?= esc($row['title']) ?>
                            </a>
                        </td>
                        <td><?= esc($row['campus']) ?></td>
                        <td><?= esc((string) $row['total']) ?></td>
                        <td><?= esc((string) $row['closing']) ?></td>
                        <td><?= esc((string) $row['stage2_responded']) ?></td>
                        <td><span class="status status-calendar"><?= esc((string) $row['score']) ?>%</span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($crmRows === []): ?>
                    <tr>
                        <td colspan="6"><span class="muted-note">Belum ada data CRM pada periode ini.</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel wide">
    <div class="panel-header">
        <div>
            <span class="eyebrow">Pergerakan event</span>
            <h2>Event setiap kampus</h2>
        </div>
        <span class="muted-note">Target: 2 OCTE non Aplikasi PMB + 2 Hangout/Open House per bulan</span>
    </div>
    <div class="table-wrap">
        <table class="data-table special-analytics-table">
            <thead>
                <tr>
                    <th>Kampus</th>
                    <th>Capaian</th>
                    <th>OCTE</th>
                    <th>Open House</th>
                    <th>Pergerakan <?= esc((string) $selectedYear) ?></th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campusRows as $row): ?>
                    <tr>
                        <td>
                            <strong class="table-title"><?= esc($row['campus']) ?></strong>
                            <small><?= esc((string) $row['percent']) ?>% dari target periode</small>
                        </td>
                        <td><?= esc((string) $row['total']) ?>/<?= esc((string) $row['target_total']) ?></td>
                        <td><?= esc((string) $row['regular']) ?>/<?= esc((string) $row['target_regular']) ?></td>
                        <td><?= esc((string) $row['openhouse']) ?>/<?= esc((string) $row['target_openhouse']) ?></td>
                        <td>
                            <div class="campus-movement-bars">
                                <?php foreach ($monthLabels as $monthKey => $monthLabel): ?>
                                    <?php $movement = $row['monthly'][$monthKey] ?? ['count' => 0, 'percent' => 0]; ?>
                                    <span title="<?= esc($monthLabel . ': ' . $movement['count'] . ' event', 'attr') ?>">
                                        <i style="height: <?= esc((string) max(6, $movement['percent'])) ?>%"></i>
                                        <em><?= esc($monthLabel) ?></em>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status <?= $row['target_met'] ? 'status-aktif' : 'status-ditolak' ?>">
                                <?= $row['target_met'] ? 'Tercapai' : 'Belum target' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($campusRows === []): ?>
                    <tr>
                        <td colspan="6"><span class="muted-note">Belum ada data kampus untuk dianalisis.</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
