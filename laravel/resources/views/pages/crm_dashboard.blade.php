<?php
$stats = $stats ?? [];
$leads = $leads ?? [];
$agents = $agents ?? [];
$users = $users ?? [];
$batches = $batches ?? [];
$events = $events ?? [];
$campuses = $campuses ?? [];
$filters = $filters ?? [];
$schools = $schools ?? [];
$leadLogsByLeadId = $leadLogsByLeadId ?? [];
$currentUser = $crmUser ?? [];
$selectedCount = 0;
$leadPagination = $leadPagination ?? [
    'current' => 1,
    'perPage' => 25,
    'total' => count($leads),
    'totalPages' => 1,
    'from' => count($leads) > 0 ? 1 : 0,
    'to' => count($leads),
    'query' => [],
];
$leadPageUrl = static function (int $page) use ($leadPagination): string {
    $query = $leadPagination['query'] ?? [];
    $query['page'] = $page;

    return site_url('crm?' . http_build_query($query));
};
$crmLabels = [
    ['name' => 'Closing', 'class' => 'crm-label-closing', 'stat' => 'closing'],
    ['name' => 'Minat/Tertarik', 'class' => 'crm-label-green', 'stat' => 'interested'],
    ['name' => 'Ragu-ragu', 'class' => 'crm-label-yellow', 'stat' => 'hesitant'],
    ['name' => 'Menolak', 'class' => 'crm-label-red', 'stat' => 'rejected'],
    ['name' => 'Tidak Merespon', 'class' => 'crm-label-gray', 'stat' => 'no_response'],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'CRM Admin') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="crm-page">
    <header class="crm-topbar">
        <a class="crm-brand" href="<?= site_url('crm') ?>">
            <img class="crm-brand-logo" src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub">
            CRM Admin
        </a>
        <nav class="crm-nav" aria-label="Navigasi CRM">
            <span><?= esc($currentUser['name'] ?? 'Agent') ?></span>
            <a href="<?= site_url('crm/analytics') ?>">Analisis CRM</a>
            <a href="<?= site_url('crm/monitoring') ?>">Monitoring Agent</a>
            <a href="<?= site_url('dashboard') ?>">Dashboard</a>
            <form action="<?= site_url('crm/logout') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit">Logout</button>
            </form>
        </nav>
    </header>

    <main class="crm-content">
        <?php if (session('success')): ?>
            <div class="alert success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert error"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <section class="crm-stats-grid">
            <article class="crm-stat-card crm-stat-card-accent">
                <span>Total Data</span>
                <strong><?= esc((string) ($stats['total'] ?? 0)) ?></strong>
                <i>DB</i>
            </article>
            <article class="crm-stat-card crm-stat-card-accent">
                <span>Belum Assign</span>
                <strong><?= esc((string) ($stats['unassigned'] ?? 0)) ?></strong>
                <i>AG</i>
            </article>
            <article class="crm-stage-card">
                <div>
                    <span>Tahap 1</span>
                    <strong class="crm-blue"><?= esc((string) ($stats['stage1'] ?? 0)) ?></strong>
                    <small>Fresh Data</small>
                </div>
                <div>
                    <span>Tahap 2</span>
                    <strong class="crm-amber"><?= esc((string) ($stats['stage2'] ?? 0)) ?></strong>
                    <small>Sudah Respon</small>
                </div>
                <div>
                    <span>Tahap 3</span>
                    <strong class="crm-green"><?= esc((string) ($stats['stage3'] ?? 0)) ?></strong>
                    <small>Tahap Lanjut</small>
                </div>
            </article>
        </section>

        <section class="crm-label-strip">
            <?php foreach ($crmLabels as $label): ?>
                <div>
                    <span class="crm-label-badge <?= esc($label['class']) ?>"><?= esc($label['name']) ?></span>
                    <strong><?= esc((string) ($stats[$label['stat']] ?? 0)) ?></strong>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="crm-main-grid">
            <section class="crm-board">
                <div class="crm-board-header">
                    <h2>Data Siswa CRM</h2>
                    <?php if ($isAdmin): ?>
                        <div class="crm-board-actions">
                            <span data-crm-selected-count>0 dipilih</span>
                            <form id="crm-assign-form" action="<?= site_url('crm/leads/assign') ?>" method="post">
                                <?= csrf_field() ?>
                                <select class="control compact" name="agent_id" required>
                                    <option value="">Pilih Agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?= esc($agent['id']) ?>"><?= esc($agent['full_name'] . (! empty($agent['campus_name']) ? ' - ' . $agent['campus_name'] : '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <button class="ghost-button compact-button" type="submit" form="crm-assign-form">Assign</button>
                            <form action="<?= site_url('crm/leads/auto-assign') ?>" method="post" class="inline-form">
                                <?= csrf_field() ?>
                                <button class="crm-warning-button compact-button" type="submit">Auto Assign</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <form class="crm-filter-bar" action="<?= site_url('crm') ?>" method="get">
                    <input class="control" type="search" name="q" placeholder="Cari nama/sekolah/WA..." value="<?= esc($filters['q'] ?? '') ?>">
                    <select class="control" name="campus_id">
                        <option value="">Semua Kampus</option>
                        <?php foreach ($campuses as $campus): ?>
                            <option value="<?= esc($campus['id']) ?>" <?= (string) ($filters['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>><?= esc($campus['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="control" name="event_id">
                        <option value="">Semua Event</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= esc($event['id']) ?>" <?= (string) ($filters['event_id'] ?? '') === (string) $event['id'] ? 'selected' : '' ?>>
                                <?= esc(($event['campus_name'] ?: 'Tanpa kampus') . ' - ' . $event['code'] . ' - ' . $event['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="control" name="stage">
                        <option value="">Semua Tahap</option>
                        <?php foreach (['Tahap 1', 'Tahap 2', 'Tahap 3'] as $stage): ?>
                            <option value="<?= esc($stage) ?>" <?= ($filters['stage'] ?? '') === $stage ? 'selected' : '' ?>><?= esc($stage) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="control" name="label">
                        <option value="">Semua Label</option>
                        <?php foreach ($crmLabels as $label): ?>
                            <option value="<?= esc($label['name']) ?>" <?= ($filters['label'] ?? '') === $label['name'] ? 'selected' : '' ?>><?= esc($label['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isAdmin): ?>
                        <select class="control" name="agent_id">
                            <option value="">Semua Agent</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= esc($agent['id']) ?>" <?= (string) ($filters['agent_id'] ?? '') === (string) $agent['id'] ? 'selected' : '' ?>><?= esc($agent['full_name'] . (! empty($agent['campus_name']) ? ' - ' . $agent['campus_name'] : '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <select class="control" name="school">
                        <option value="">Semua Sekolah</option>
                        <?php foreach ($schools as $school): ?>
                            <option value="<?= esc($school) ?>" <?= ($filters['school'] ?? '') === $school ? 'selected' : '' ?>><?= esc($school) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="control" type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>">
                    <input class="control" type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>">
                    <select class="control" name="batch_id">
                        <option value="">Semua Batch</option>
                        <?php foreach ($batches as $batch): ?>
                            <option value="<?= esc($batch['id']) ?>" <?= (string) ($filters['batch_id'] ?? '') === (string) $batch['id'] ? 'selected' : '' ?>><?= esc($batch['batch_code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isAdmin): ?>
                        <label class="crm-check-filter">
                            <input type="checkbox" name="unassigned" value="1" <?= ($filters['unassigned'] ?? '') === '1' ? 'checked' : '' ?>>
                            Unassigned
                        </label>
                    <?php endif; ?>
                    <div class="crm-filter-actions">
                        <button class="primary-button compact-button" type="submit">Filter</button>
                        <a class="ghost-button compact-button" href="<?= site_url('crm') ?>">Reset</a>
                    </div>
                </form>

                <div class="report-table-summary crm-table-summary">
                    <span><?= esc((string) $leadPagination['from']) ?>-<?= esc((string) $leadPagination['to']) ?> dari <?= esc((string) $leadPagination['total']) ?> data siswa CRM</span>
                    <span>25 record per halaman</span>
                </div>

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <?php if ($isAdmin): ?>
                                    <th><input type="checkbox" data-crm-check-all aria-label="Pilih semua"></th>
                                <?php endif; ?>
                                <th>Nama Siswa</th>
                                <th>Asal Sekolah</th>
                                <th>No. WA</th>
                                <th>Event</th>
                                <th>Kampus</th>
                                <th>Tahap</th>
                                <th>Label</th>
                                <th>Alasan/Keterangan</th>
                                <th>Agent</th>
                                <th>Bukti Follow Up</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <?php
                                    $leadFormId = 'crm-lead-' . $lead['id'];
                                    $rowLogs = $leadLogsByLeadId[(int) $lead['id']] ?? [];
                                ?>
                                <tr id="crm-lead-row-<?= esc((string) $lead['id'], 'attr') ?>">
                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <input form="crm-assign-form" type="checkbox" name="lead_ids[]" value="<?= esc($lead['id']) ?>" data-crm-row-check aria-label="Pilih <?= esc($lead['student_name'], 'attr') ?>">
                                        </td>
                                    <?php endif; ?>
                                    <td><strong><?= esc($lead['student_name']) ?></strong></td>
                                    <td><?= esc($lead['school_origin'] ?: '-') ?></td>
                                    <?php
                                        $rawWhatsapp = preg_replace('/\D+/', '', (string) ($lead['whatsapp'] ?? '')) ?? '';
                                        $whatsappLinkNumber = str_starts_with($rawWhatsapp, '0')
                                            ? '62' . substr($rawWhatsapp, 1)
                                            : (str_starts_with($rawWhatsapp, '8') ? '62' . $rawWhatsapp : $rawWhatsapp);
                                    ?>
                                    <td>
                                        <?php if ($whatsappLinkNumber !== ''): ?>
                                            <a
                                                class="crm-whatsapp-button"
                                                href="https://wa.me/<?= esc($whatsappLinkNumber) ?>"
                                                target="_blank"
                                                rel="noopener"
                                                aria-label="Chat WhatsApp <?= esc($lead['student_name'], 'attr') ?> di nomor <?= esc($lead['whatsapp'], 'attr') ?>"
                                                title="Buka chat WhatsApp"
                                            >
                                                <span class="crm-whatsapp-button-icon" aria-hidden="true"></span>
                                                <span><?= esc($lead['whatsapp']) ?></span>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (! empty($lead['event_code'])): ?>
                                            <a class="text-link" href="<?= site_url('events/' . $lead['event_code']) ?>"><?= esc($lead['event_code']) ?></a>
                                            <small><?= esc($lead['event_title'] ?? '') ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($lead['campus_name'] ?: '-') ?></td>
                                    <td>
                                        <select class="crm-inline-select crm-stage-select" form="<?= esc($leadFormId) ?>" name="stage" data-crm-stage-select data-crm-lead-form="<?= esc($leadFormId, 'attr') ?>">
                                            <?php foreach (['Tahap 1', 'Tahap 2', 'Tahap 3'] as $stage): ?>
                                                <option <?= ($lead['stage'] ?? '') === $stage ? 'selected' : '' ?>><?= esc($stage) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="crm-inline-select" form="<?= esc($leadFormId) ?>" name="label" data-crm-label-select data-crm-lead-form="<?= esc($leadFormId, 'attr') ?>">
                                            <option value="">-</option>
                                            <?php foreach ($crmLabels as $label): ?>
                                                <option value="<?= esc($label['name']) ?>" <?= ($lead['label'] ?? '') === $label['name'] ? 'selected' : '' ?>><?= esc($label['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php
                                            $labelReasonActive = in_array(($lead['stage'] ?? ''), ['Tahap 2', 'Tahap 3'], true)
                                                && in_array(($lead['label'] ?? ''), ['Minat/Tertarik', 'Ragu-ragu', 'Menolak'], true);
                                        ?>
                                        <textarea
                                            class="crm-label-reason"
                                            form="<?= esc($leadFormId) ?>"
                                            name="rejection_reason"
                                            rows="2"
                                            placeholder="Alasan atau keterangan"
                                            aria-label="Alasan atau keterangan label"
                                            data-crm-label-reason
                                            data-crm-lead-form="<?= esc($leadFormId, 'attr') ?>"
                                            <?= $labelReasonActive ? '' : 'disabled' ?>
                                        ><?= esc($lead['rejection_reason'] ?? '') ?></textarea>
                                    </td>
                                    <td>
                                        <?php if ($isAdmin): ?>
                                            <select class="crm-inline-select" form="<?= esc($leadFormId) ?>" name="agent_id">
                                                <option value="">Belum assign</option>
                                                <?php foreach ($agents as $agent): ?>
                                                    <option value="<?= esc($agent['id']) ?>" <?= (string) ($lead['agent_id'] ?? '') === (string) $agent['id'] ? 'selected' : '' ?>><?= esc($agent['full_name'] . (! empty($agent['campus_name']) ? ' - ' . $agent['campus_name'] : '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <?= esc($lead['agent_name'] ?: '-') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <label class="crm-follow-up-upload">
                                            <span>Pilih JPG/PNG</span>
                                            <input
                                                form="<?= esc($leadFormId, 'attr') ?>"
                                                type="file"
                                                name="follow_up_image"
                                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                aria-label="Upload bukti follow-up <?= esc($lead['student_name'], 'attr') ?>"
                                            >
                                        </label>
                                    </td>
                                    <td>
                                        <form id="<?= esc($leadFormId) ?>" action="<?= site_url('crm/leads/' . $lead['id'] . '/update') ?>" method="post" enctype="multipart/form-data" class="crm-row-action">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="notes" value="<?= esc($lead['notes'] ?? '', 'attr') ?>">
                                            <button class="crm-icon-action crm-save" type="submit" aria-label="Simpan">OK</button>
                                        </form>
                                        <?php if ($isAdmin): ?>
                                            <form action="<?= site_url('crm/leads/' . $lead['id'] . '/delete') ?>" method="post" class="crm-row-action">
                                                <?= csrf_field() ?>
                                                <button class="crm-icon-action crm-delete" type="submit" aria-label="Hapus">DEL</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr class="crm-lead-row-log">
                                    <td colspan="<?= $isAdmin ? '12' : '11' ?>">
                                        <details class="crm-row-log-details">
                                            <summary>Riwayat follow-up baris ini (<?= esc((string) count($rowLogs)) ?> terbaru)</summary>
                                            <div class="crm-row-log-list">
                                                <?php foreach ($rowLogs as $log): ?>
                                                    <article class="crm-row-log-item">
                                                        <div>
                                                            <strong><?= esc($log['summary'] ?: 'Data CRM diperbarui.') ?></strong>
                                                            <?php if (! empty($log['new_rejection_reason'])): ?>
                                                                <small>Alasan: <?= esc($log['new_rejection_reason']) ?></small>
                                                            <?php endif; ?>
                                                            <small>
                                                                <?= esc($log['actor_name'] ?: 'User CRM') ?> -
                                                                <?= esc(! empty($log['created_at']) ? date('d M Y, H.i', strtotime($log['created_at'])) : '-') ?>
                                                            </small>
                                                        </div>
                                                        <?php if (! empty($log['follow_up_image'])): ?>
                                                            <a class="crm-follow-up-preview" href="<?= site_url('crm/logs/' . $log['id'] . '/image') ?>" target="_blank" rel="noopener">
                                                                <img src="<?= site_url('crm/logs/' . $log['id'] . '/image') ?>" alt="Bukti hasil follow-up">
                                                                <span>Lihat bukti</span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </article>
                                                <?php endforeach; ?>
                                                <?php if ($rowLogs === []): ?>
                                                    <p class="crm-log-empty">Belum ada follow-up pada baris ini.</p>
                                                <?php endif; ?>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($leads === []): ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? '12' : '11' ?>" class="crm-empty">Belum ada data calon pendaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (($leadPagination['totalPages'] ?? 1) > 1): ?>
                    <nav class="pagination-bar crm-pagination-bar" aria-label="Navigasi data siswa CRM">
                        <a
                            class="ghost-button compact-button <?= ($leadPagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                            href="<?= ($leadPagination['current'] ?? 1) <= 1 ? '#' : esc($leadPageUrl((int) $leadPagination['current'] - 1)) ?>"
                        >Sebelumnya</a>
                        <div class="pagination-pages">
                            <?php for ($page = 1; $page <= (int) $leadPagination['totalPages']; $page++): ?>
                                <?php if ($page === 1 || $page === (int) $leadPagination['totalPages'] || abs($page - (int) $leadPagination['current']) <= 2): ?>
                                    <a
                                        class="ghost-button compact-button <?= $page === (int) $leadPagination['current'] ? 'is-active' : '' ?>"
                                        href="<?= esc($leadPageUrl($page)) ?>"
                                    ><?= esc((string) $page) ?></a>
                                <?php elseif (abs($page - (int) $leadPagination['current']) === 3): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <a
                            class="ghost-button compact-button <?= ($leadPagination['current'] ?? 1) >= ($leadPagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                            href="<?= ($leadPagination['current'] ?? 1) >= ($leadPagination['totalPages'] ?? 1) ? '#' : esc($leadPageUrl((int) $leadPagination['current'] + 1)) ?>"
                        >Berikutnya</a>
                    </nav>
                <?php endif; ?>
            </section>

            <aside class="crm-side">
                <?php if ($isAdmin): ?>
                    <section class="crm-side-card">
                        <h2>Upload Data Excel</h2>
                        <?php
                            $selectedUploadEvent = null;
                            foreach ($events as $event) {
                                if ((string) ($filters['event_id'] ?? '') === (string) $event['id']) {
                                    $selectedUploadEvent = $event;
                                    break;
                                }
                            }
                            $selectedUploadEventLabel = $selectedUploadEvent
                                ? trim(($selectedUploadEvent['code'] ?? '') . ' - ' . ($selectedUploadEvent['title'] ?? ''))
                                : '';
                        ?>
                        <form action="<?= site_url('crm/leads/upload') ?>" method="post" enctype="multipart/form-data" class="crm-upload-form">
                            <?= csrf_field() ?>
                            <div class="crm-event-combobox" data-crm-event-combobox>
                                <label for="crm-upload-event-search">Cari Nama Event</label>
                                <input
                                    id="crm-upload-event-search"
                                    class="control"
                                    type="search"
                                    placeholder="Ketik nama event..."
                                    value="<?= esc($selectedUploadEventLabel, 'attr') ?>"
                                    autocomplete="off"
                                    data-crm-event-search
                                    required
                                >
                                <input type="hidden" name="event_id" value="<?= esc((string) ($filters['event_id'] ?? ''), 'attr') ?>" data-crm-event-id>
                                <div class="crm-event-suggestions" data-crm-event-suggestions hidden>
                                    <?php foreach ($events as $event): ?>
                                        <?php $eventLabel = trim(($event['code'] ?? '') . ' - ' . ($event['title'] ?? '')); ?>
                                        <button
                                            type="button"
                                            data-crm-event-option
                                            data-id="<?= esc((string) $event['id'], 'attr') ?>"
                                            data-label="<?= esc($eventLabel, 'attr') ?>"
                                            data-search="<?= esc(strtolower($eventLabel . ' ' . ($event['campus_name'] ?: 'Tanpa kampus')), 'attr') ?>"
                                        >
                                            <strong><?= esc($eventLabel) ?></strong>
                                            <small><?= esc($event['campus_name'] ?: 'Tanpa kampus') ?></small>
                                        </button>
                                    <?php endforeach; ?>
                                    <p data-crm-event-empty hidden>Tidak ada event yang cocok.</p>
                                </div>
                            </div>
                            <label class="crm-dropzone">
                                <span>UPLOAD</span>
                                <strong>Drag & drop file Excel .xlsx atau CSV</strong>
                                <input type="file" name="lead_file" accept=".csv,.xlsx" required>
                            </label>
                            <p>Format: No, Nama Siswa, Asal Sekolah, No WA</p>
                            <div class="crm-upload-actions">
                                <a class="ghost-button compact-button" href="<?= site_url('crm/template') ?>">Template</a>
                                <button class="crm-upload-button" type="submit">Upload</button>
                            </div>
                        </form>
                    </section>

                    <section class="crm-side-card crm-history-card">
                        <div class="crm-side-title">
                            <h2>Riwayat Upload</h2>
                        </div>
                        <?php foreach ($batches as $batch): ?>
                            <article class="crm-history-item">
                                <div>
                                    <strong><?= esc($batch['batch_code']) ?></strong>
                                    <small><?= esc(date('d M Y, H.i', strtotime($batch['uploaded_at']))) ?> - <?= esc((string) $batch['total_rows']) ?> data</small>
                                    <small><?= esc(($batch['campus_name'] ?: 'Tanpa kampus') . ' - ' . ($batch['event_code'] ?: 'Tanpa event')) ?></small>
                                </div>
                                <form action="<?= site_url('crm/batches/' . $batch['id'] . '/delete') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button class="crm-icon-action crm-delete" type="submit">DEL</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <section class="crm-side-card crm-users-card">
                        <div class="crm-user-card-title">
                            <h2>Manajemen User</h2>
                            <button class="crm-modal-open" type="button" data-crm-modal-open>+</button>
                        </div>
                        <div class="crm-user-list">
                            <?php foreach ($users as $user): ?>
                                <article>
                                    <div>
                                        <strong><?= esc($user['full_name']) ?></strong>
                                        <small><?= esc($user['username']) ?> - <?= esc($user['role']) ?> - <?= esc($user['status']) ?></small>
                                    </div>
                                    <?php if (empty($user['deleted_at']) && (int) $user['id'] !== (int) ($currentUser['id'] ?? 0)): ?>
                                        <form action="<?= site_url('crm/users/' . $user['id'] . '/delete') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button class="crm-icon-action crm-delete" type="submit">DEL</button>
                                        </form>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
    </main>

    <?php if ($isAdmin): ?>
        <div class="crm-modal" data-crm-modal hidden>
            <div class="crm-modal-backdrop" data-crm-modal-close></div>
            <section class="crm-modal-panel" role="dialog" aria-modal="true" aria-labelledby="crm-user-modal-title">
                <header>
                    <h2 id="crm-user-modal-title">Tambah User Baru</h2>
                    <button type="button" data-crm-modal-close aria-label="Tutup">X</button>
                </header>
                <form action="<?= site_url('crm/users') ?>" method="post" class="crm-modal-form">
                    <?= csrf_field() ?>
                    <label>
                        Username
                        <input class="control" type="text" name="username" value="<?= esc(old('username') ?? '') ?>" required>
                    </label>
                    <label>
                        Password
                        <input class="control" type="password" name="password" required minlength="6">
                    </label>
                    <label>
                        Nama Lengkap
                        <input class="control" type="text" name="full_name" value="<?= esc(old('full_name') ?? '') ?>" required>
                    </label>
                    <div class="crm-modal-grid">
                        <label>
                            Role
                            <select class="control" name="role">
                                <option value="Agent">Agent</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </label>
                        <label>
                            Status
                            <select class="control" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </label>
                    </div>
                    <div class="crm-modal-actions">
                        <button class="ghost-button" type="button" data-crm-modal-close>Batal</button>
                        <button class="primary-button" type="submit">Simpan</button>
                    </div>
                </form>
            </section>
        </div>
    <?php endif; ?>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
