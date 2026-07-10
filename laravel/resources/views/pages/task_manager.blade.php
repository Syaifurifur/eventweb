<?php
$currentTasks = $activeTab === 'selesai' ? ($completedTasks ?? []) : ($runningTasks ?? []);
$taskCounts = $taskCounts ?? ['running' => count($runningTasks ?? []), 'completed' => count($completedTasks ?? [])];
$pagination = $taskPagination ?? [
    'current' => 1,
    'perPage' => 5,
    'total' => count($currentTasks),
    'totalPages' => 1,
    'from' => count($currentTasks) > 0 ? 1 : 0,
    'to' => count($currentTasks),
];
$tabQuery = static fn (string $tab): string => site_url('task-manager?' . http_build_query(array_filter([
    'tab' => $tab,
    'q' => $search ?? '',
], static fn ($value): bool => $value !== '')));
$taskPageUrl = static function (int $page) use ($activeTab, $search): string {
    return site_url('task-manager?' . http_build_query(array_filter([
        'tab' => $activeTab,
        'q' => $search ?? '',
        'page' => $page > 1 ? $page : null,
    ], static fn ($value): bool => $value !== '' && $value !== null)));
};
?>

<div class="toolbar task-manager-toolbar">
    <div>
        <span class="eyebrow">Tugas PIC</span>
        <h2>Kelola dan kirim hasil tugas</h2>
    </div>
    <div class="task-toolbar-actions">
        <a class="primary-button" href="<?= site_url('task-manager/new') ?>">Buat Tugas</a>
        <form action="<?= site_url('task-manager') ?>" method="get" class="task-search-form">
            <input type="hidden" name="tab" value="<?= esc($activeTab) ?>">
            <input class="control" type="search" name="q" value="<?= esc($search ?? '') ?>" placeholder="Cari event atau nama tugas" aria-label="Cari tugas">
            <button class="ghost-button" type="submit">Cari</button>
            <?php if (($search ?? '') !== ''): ?>
                <a class="text-link" href="<?= site_url('task-manager?tab=' . $activeTab) ?>">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<nav class="task-tabs" aria-label="Status tugas">
    <a class="task-tab <?= $activeTab === 'berjalan' ? 'is-active' : '' ?>" href="<?= $tabQuery('berjalan') ?>">
        Tugas Berjalan <span><?= esc((string) ($taskCounts['running'] ?? 0)) ?></span>
    </a>
    <a class="task-tab <?= $activeTab === 'selesai' ? 'is-active' : '' ?>" href="<?= $tabQuery('selesai') ?>">
        Tugas Selesai <span><?= esc((string) ($taskCounts['completed'] ?? 0)) ?></span>
    </a>
</nav>

<section class="panel task-manager-panel">
    <?php if ($currentTasks === []): ?>
        <div class="task-empty-state">
            <strong><?= ($search ?? '') !== '' ? 'Tugas tidak ditemukan' : ($activeTab === 'selesai' ? 'Belum ada tugas selesai' : 'Tidak ada tugas berjalan') ?></strong>
            <p><?= ($search ?? '') !== '' ? 'Coba gunakan kata kunci lain.' : 'Tugas PIC akan otomatis masuk ke tab yang sesuai.' ?></p>
        </div>
    <?php endif; ?>

    <div class="task-manager-list">
        <?php foreach ($currentTasks as $task): ?>
            <?php
            $isDone = ($task['status'] ?? '') === 'done';
            $description = trim((string) ($task['notes'] ?? ''));
            $materialLink = trim((string) ($task['material_drive_link'] ?? ''));
            $resultLink = trim((string) ($task['drive_link'] ?? ''));
            ?>
            <article class="task-manager-card <?= $isDone ? 'is-done' : '' ?>">
                <header class="task-manager-card-header">
                    <div>
                        <span class="status status-<?= $isDone ? 'disetujui' : 'diajukan' ?>"><?= esc($isDone ? 'Selesai' : (($task['status'] ?? '') === 'submitted' ? 'Terkirim' : 'Berjalan')) ?></span>
                        <p class="task-event-name"><?= esc($task['event_title'] ?? '-') ?> <small><?= esc($task['event_code'] ?? '') ?></small></p>
                        <h3><?= esc($task['name'] ?? '-') ?></h3>
                    </div>
                    <div class="task-manager-meta">
                        <?php if ($isTaskAdmin ?? false): ?>
                            <span>PIC <?= esc($task['pic_name'] ?? 'Belum dipilih') ?></span>
                        <?php endif; ?>
                        <span><?= esc($task['phase'] ?? '-') ?></span>
                        <span><?= ! empty($task['deadline']) ? 'Deadline ' . esc(date('d M Y', strtotime($task['deadline']))) : 'Tanpa deadline' ?></span>
                    </div>
                </header>

                <div class="task-manager-actions">
                    <details class="task-description">
                        <summary class="ghost-button compact-button">Lihat Deskripsi</summary>
                        <div class="task-description-content">
                            <strong>Deskripsi tugas</strong>
                            <p><?= esc($description !== '' ? $description : 'Belum ada deskripsi tambahan untuk tugas ini.') ?></p>
                        </div>
                    </details>

                    <?php if ($materialLink !== ''): ?>
                        <a class="ghost-button compact-button" href="<?= esc($materialLink) ?>" target="_blank" rel="noopener">File Pendukung</a>
                    <?php else: ?>
                        <span class="muted-note">Tidak ada file pendukung</span>
                    <?php endif; ?>

                    <?php if ($isDone): ?>
                        <?php if ($resultLink !== ''): ?>
                            <a class="ghost-button compact-button" href="<?= esc($resultLink) ?>" target="_blank" rel="noopener">Lihat Hasil</a>
                        <?php endif; ?>
                        <details class="task-edit-panel">
                            <summary class="primary-button compact-button">Edit Tugas</summary>
                            <form action="<?= site_url('checklist-tasks/' . $task['id'] . '/report') ?>" method="post" class="task-submit-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="is_done" value="1">
                                <label>
                                    Link file tugas
                                    <input class="control" type="url" name="drive_link" value="<?= esc($resultLink) ?>" placeholder="https://drive.google.com/hasil-tugas" required>
                                </label>
                                <button class="primary-button" type="submit">Simpan Perubahan</button>
                            </form>
                        </details>
                    <?php else: ?>
                        <form action="<?= site_url('checklist-tasks/' . $task['id'] . '/report') ?>" method="post" class="task-submit-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="is_done" value="1">
                            <label>
                                Link file tugas yang telah selesai
                                <input class="control" type="url" name="drive_link" value="<?= esc($resultLink) ?>" placeholder="https://drive.google.com/hasil-tugas" required>
                            </label>
                            <button class="primary-button" type="submit">Kirim File Tugas</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (($pagination['total'] ?? 0) > 0): ?>
        <div class="task-pagination-summary">
            Menampilkan <?= esc((string) $pagination['from']) ?>-<?= esc((string) $pagination['to']) ?> dari <?= esc((string) $pagination['total']) ?> tugas
        </div>
    <?php endif; ?>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <nav class="pagination-bar" aria-label="Navigasi halaman <?= $activeTab === 'selesai' ? 'tugas selesai' : 'tugas berjalan' ?>">
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) <= 1 ? '#' : esc($taskPageUrl((int) $pagination['current'] - 1)) ?>"
            >Sebelumnya</a>
            <div class="pagination-pages">
                <?php for ($page = 1; $page <= (int) $pagination['totalPages']; $page++): ?>
                    <?php if ($page === 1 || $page === (int) $pagination['totalPages'] || abs($page - (int) $pagination['current']) <= 2): ?>
                        <a
                            class="ghost-button compact-button <?= $page === (int) $pagination['current'] ? 'is-active' : '' ?>"
                            href="<?= esc($taskPageUrl($page)) ?>"
                        ><?= esc((string) $page) ?></a>
                    <?php elseif (abs($page - (int) $pagination['current']) === 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? '#' : esc($taskPageUrl((int) $pagination['current'] + 1)) ?>"
            >Berikutnya</a>
        </nav>
    <?php endif; ?>
</section>
