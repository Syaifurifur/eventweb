<div class="toolbar">
    <div>
        <span class="eyebrow">Event management</span>
        <h2>Daftar event</h2>
    </div>
    <div class="button-row">
        <?php if ($isAdminSystem ?? false): ?>
            <a class="ghost-button" href="<?= site_url('events/template') ?>">Template Import</a>
            <a class="ghost-button" href="<?= site_url('events/export') ?>">Download Event</a>
        <?php endif; ?>
        <a class="primary-button" href="<?= site_url('events/new') ?>">Buat Event</a>
    </div>
</div>

<?php if ($isAdminSystem ?? false): ?>
    <section class="panel event-import-panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Upload rekap event</span>
                <h2>Import data event</h2>
            </div>
            <a class="ghost-button compact-button" href="<?= site_url('events/template') ?>">Download Template</a>
        </div>
        <form action="<?= site_url('events/import') ?>" method="post" enctype="multipart/form-data" class="event-import-form">
            <?= csrf_field() ?>
            <label>
                File Excel / CSV
                <input class="control" type="file" name="event_file" accept=".xlsx,.csv" required>
            </label>
            <button class="primary-button compact-button" type="submit">Upload Event</button>
        </form>
    </section>
<?php endif; ?>

<?php
    $eventFilters = $eventFilters ?? ['q' => '', 'category' => '', 'status' => ''];
    $canDeleteAnyEvent = count(array_filter($events ?? [], static fn (array $event): bool => ! empty($event['can_delete']))) > 0;
?>
<form class="filter-bar" action="<?= site_url('events') ?>" method="get">
    <input class="control" type="search" name="q" value="<?= esc((string) ($eventFilters['q'] ?? '')) ?>" placeholder="Cari judul, kampus, lokasi, atau PIC">
    <select class="control" name="category">
        <option value="">Semua kategori</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= esc($category['name']) ?>" <?= (string) ($eventFilters['category'] ?? '') === (string) $category['name'] ? 'selected' : '' ?>>
                <?= esc($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select class="control" name="status">
        <option value="">Semua status</option>
        <?php foreach (['draft', 'diajukan', 'disetujui', 'ditolak', 'selesai'] as $status): ?>
            <option value="<?= esc($status) ?>" <?= (string) ($eventFilters['status'] ?? '') === $status ? 'selected' : '' ?>>
                <?= esc($status) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button class="primary-button compact-button" type="submit">Terapkan</button>
    <a class="ghost-button compact-button" href="<?= site_url('events') ?>">Reset</a>
</form>

<section class="panel">
    <?php
        $pagination = $eventPagination ?? [
            'current' => 1,
            'perPage' => 10,
            'total' => 0,
            'totalPages' => 1,
            'from' => 0,
            'to' => 0,
        ];
        $eventPageUrl = static function (int $page) use ($eventFilters): string {
            $query = array_filter([
                'q' => trim((string) ($eventFilters['q'] ?? '')),
                'category' => trim((string) ($eventFilters['category'] ?? '')),
                'status' => trim((string) ($eventFilters['status'] ?? '')),
                'page' => $page,
            ], static fn ($value): bool => $value !== '' && $value !== null);

            return site_url('events' . ($query !== [] ? '?' . http_build_query($query) : ''));
        };
    ?>
    <div class="event-table-summary">
        <span><?= esc($pagination['from']) ?>-<?= esc($pagination['to']) ?> dari <?= esc($pagination['total']) ?> event</span>
        <span>Urutan berdasarkan tanggal event terbaru</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Kategori</th>
                    <th>Kampus</th>
                    <th>Tanggal</th>
                    <th>PIC</th>
                    <th>Peserta</th>
                    <th>Status</th>
                    <?php if ($canDeleteAnyEvent): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td>
                            <a class="table-title" href="<?= site_url('events/' . $event['id']) ?>"><?= esc($event['title']) ?></a>
                            <small><?= esc($event['location']) ?></small>
                        </td>
                        <td><?= esc($event['category']) ?></td>
                        <td><?= esc($event['campus']) ?></td>
                        <td><?= esc($event['date_label'] ?? date('d M Y', strtotime($event['date']))) ?></td>
                        <td><?= esc($event['created_by']) ?></td>
                        <td><?= esc($event['participants_count']) ?></td>
                        <td><span class="status status-<?= esc($event['status']) ?>"><?= esc($event['status']) ?></span></td>
                        <?php if ($canDeleteAnyEvent): ?>
                            <td>
                                <?php if (! empty($event['can_delete'])): ?>
                                    <form action="<?= site_url('events/' . $event['id'] . '/delete') ?>" method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <button class="danger-button compact-button" type="submit">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted-note">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (($events ?? []) === []): ?>
                    <tr>
                        <td colspan="<?= $canDeleteAnyEvent ? 8 : 7 ?>">
                            <span class="muted-note">Belum ada event yang tersedia.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <nav class="pagination-bar" aria-label="Navigasi halaman event">
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) <= 1 ? '#' : esc($eventPageUrl((int) $pagination['current'] - 1)) ?>"
            >Sebelumnya</a>
            <div class="pagination-pages">
                <?php for ($page = 1; $page <= (int) $pagination['totalPages']; $page++): ?>
                    <?php if ($page === 1 || $page === (int) $pagination['totalPages'] || abs($page - (int) $pagination['current']) <= 2): ?>
                        <a
                            class="ghost-button compact-button <?= $page === (int) $pagination['current'] ? 'is-active' : '' ?>"
                            href="<?= esc($eventPageUrl($page)) ?>"
                        ><?= esc($page) ?></a>
                    <?php elseif (abs($page - (int) $pagination['current']) === 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? '#' : esc($eventPageUrl((int) $pagination['current'] + 1)) ?>"
            >Berikutnya</a>
        </nav>
    <?php endif; ?>
</section>
