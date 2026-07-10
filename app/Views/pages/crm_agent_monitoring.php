<?php
$agentMonitoring = $agentMonitoring ?? [];
$currentUser = $crmUser ?? [];
$scopeText = ($isAdmin ?? false)
    ? (($campusScope ?? null) !== null ? 'Kampus pelaksana sesuai akun' : 'Semua kampus')
    : 'Data agent yang sedang login';
$summary = [
    'agents' => count($agentMonitoring),
    'assigned' => array_sum(array_map(static fn (array $row): int => (int) ($row['assigned'] ?? 0), $agentMonitoring)),
    'followed_up' => array_sum(array_map(static fn (array $row): int => (int) ($row['followed_up'] ?? 0), $agentMonitoring)),
    'interested' => array_sum(array_map(static fn (array $row): int => (int) ($row['interested'] ?? 0), $agentMonitoring)),
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Monitoring Agent') ?> | EventHub</title>
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="crm-page">
    <header class="crm-topbar">
        <a class="crm-brand" href="<?= site_url('crm') ?>">
            <img class="crm-brand-logo" src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub">
            Monitoring Agent
        </a>
        <nav class="crm-nav" aria-label="Navigasi CRM">
            <span><?= esc($currentUser['name'] ?? 'Agent') ?></span>
            <a href="<?= site_url('crm') ?>">Data CRM</a>
            <a href="<?= site_url('dashboard') ?>">Dashboard</a>
            <form action="<?= site_url('crm/logout') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit">Logout</button>
            </form>
        </nav>
    </header>

    <main class="crm-content">
        <section class="crm-monitor-hero">
            <div>
                <span>Monitoring Agent</span>
                <h1>Grading Follow Up</h1>
                <p><?= esc($scopeText) ?></p>
            </div>
            <div class="crm-monitor-summary">
                <span><b><?= esc((string) $summary['agents']) ?></b> Agent</span>
                <span><b><?= esc((string) $summary['assigned']) ?></b> Data</span>
                <span><b><?= esc((string) $summary['followed_up']) ?></b> Follow up</span>
                <span><b><?= esc((string) $summary['interested']) ?></b> Minat</span>
            </div>
        </section>

        <section class="crm-monitor-report">
            <div class="crm-agent-monitor-header">
                <div>
                    <span>Laporan Agent</span>
                    <h2>Performa per baris</h2>
                </div>
                <small>Grade dihitung dari closing, follow up, progres Tahap 2/3, minat, penolakan, dan tidak merespon.</small>
            </div>

            <div class="crm-monitor-table-wrap">
                <table class="crm-monitor-table">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Kampus</th>
                            <th>Data</th>
                            <th>Follow Up</th>
                            <th>Tahap 2</th>
                            <th>Tahap 3</th>
                            <th>Closing</th>
                            <th>Minat</th>
                            <th>Ragu</th>
                            <th>Menolak</th>
                            <th>Tidak Respon</th>
                            <th>Konversi</th>
                            <th>Skor</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agentMonitoring as $row): ?>
                            <tr>
                                <td><strong><?= esc($row['name']) ?></strong></td>
                                <td><?= esc($row['campus'] !== '' ? $row['campus'] : '-') ?></td>
                                <td><?= esc((string) $row['assigned']) ?></td>
                                <td>
                                    <?= esc((string) $row['followed_up']) ?>
                                    <small><?= esc((string) $row['follow_up_rate']) ?>%</small>
                                </td>
                                <td><?= esc((string) $row['stage2']) ?></td>
                                <td><?= esc((string) $row['stage3']) ?></td>
                                <td><?= esc((string) $row['closing']) ?></td>
                                <td><?= esc((string) $row['interested']) ?></td>
                                <td><?= esc((string) $row['hesitant']) ?></td>
                                <td><?= esc((string) $row['rejected']) ?></td>
                                <td><?= esc((string) $row['no_response']) ?></td>
                                <td><?= esc((string) $row['conversion_rate']) ?>%</td>
                                <td><strong><?= esc((string) $row['score']) ?></strong></td>
                                <td><span class="crm-grade-pill compact <?= esc($row['grade_class']) ?>"><?= esc($row['grade']) ?></span></td>
                                <td><?= esc($row['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($agentMonitoring === []): ?>
                            <tr>
                                <td colspan="14" class="crm-empty">Belum ada agent aktif untuk dimonitor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
