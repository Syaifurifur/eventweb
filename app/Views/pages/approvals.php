<?php
$statusLabels = [
    'draft' => 'Draft',
    'diajukan' => 'Diajukan',
    'disetujui' => 'Disetujui',
    'ditolak' => 'Reject',
    'selesai' => 'Selesai',
];

$statusCounts = $approvalStatusCounts ?? array_fill_keys(array_keys($statusLabels), 0);

if (! isset($approvalStatusCounts)) {
    foreach ($events as $event) {
        if (isset($statusCounts[$event['status']])) {
            $statusCounts[$event['status']]++;
        }
    }
}

$pendingCount = $statusCounts['diajukan'] ?? 0;
$pagination = $approvalPagination ?? [
    'current' => 1,
    'perPage' => 10,
    'total' => 0,
    'totalPages' => 1,
    'from' => 0,
    'to' => 0,
];
$approvalPageUrl = static fn (int $page): string => site_url('approvals?page=' . $page);
?>

<div class="toolbar approval-toolbar">
    <div>
        <span class="eyebrow">Approval center</span>
        <h2>Review pengajuan event</h2>
    </div>
    <span class="status status-diajukan"><?= esc($pendingCount) ?> perlu review</span>
</div>

<section class="panel">
    <div class="event-table-summary">
        <span><?= esc($pagination['from']) ?>-<?= esc($pagination['to']) ?> dari <?= esc($pagination['total']) ?> event</span>
        <span>Pengajuan menunggu review ditampilkan lebih dulu</span>
    </div>
    <div class="table-wrap">
        <table class="data-table approval-table">
            <thead>
                <tr>
                    <th>Nama Event</th>
                    <th>Tanggal Pelaksanaan</th>
                    <th>PIC Pelaksana</th>
                    <th>Kampus Pelaksana</th>
                    <th>Status Event</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (($events ?? []) === []): ?>
                    <tr>
                        <td colspan="6">
                            <div class="approval-empty">Belum ada event untuk ditampilkan.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($events as $event): ?>
                    <?php
                    $status = (string) ($event['status'] ?? '');
                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                    ?>
                    <tr>
                        <td>
                            <a class="table-title" href="<?= site_url('events/' . $event['id']) ?>"><?= esc($event['title']) ?></a>
                            <small><?= esc($event['category']) ?></small>
                        </td>
                        <td><?= esc($event['date_label'] ?? date('d M Y', strtotime($event['date']))) ?></td>
                        <td><?= esc($event['created_by']) ?></td>
                        <td><?= esc($event['campus']) ?></td>
                        <td><span class="status status-<?= esc($status) ?>"><?= esc($statusLabel) ?></span></td>
                        <td>
                            <div class="approval-row-actions">
                                <?php if ($status === 'diajukan'): ?>
                                    <form action="<?= site_url('approvals/' . $event['id'] . '/approve') ?>" method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <button class="primary-button compact-button" type="submit">Approve</button>
                                    </form>
                                    <form action="<?= site_url('approvals/' . $event['id'] . '/reject') ?>" method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="rejected_reason" value="Data event perlu dilengkapi sebelum disetujui.">
                                        <button class="danger-button compact-button" type="submit">Reject</button>
                                    </form>
                                <?php endif; ?>
                                <a class="ghost-button compact-button" href="<?= site_url('events/' . $event['id']) ?>">Lihat Event</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <nav class="pagination-bar" aria-label="Navigasi halaman approval">
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) <= 1 ? '#' : esc($approvalPageUrl((int) $pagination['current'] - 1)) ?>"
            >Sebelumnya</a>
            <div class="pagination-pages">
                <?php for ($page = 1; $page <= (int) $pagination['totalPages']; $page++): ?>
                    <?php if ($page === 1 || $page === (int) $pagination['totalPages'] || abs($page - (int) $pagination['current']) <= 2): ?>
                        <a
                            class="ghost-button compact-button <?= $page === (int) $pagination['current'] ? 'is-active' : '' ?>"
                            href="<?= esc($approvalPageUrl($page)) ?>"
                        ><?= esc($page) ?></a>
                    <?php elseif (abs($page - (int) $pagination['current']) === 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? '#' : esc($approvalPageUrl((int) $pagination['current'] + 1)) ?>"
            >Berikutnya</a>
        </nav>
    <?php endif; ?>
</section>
