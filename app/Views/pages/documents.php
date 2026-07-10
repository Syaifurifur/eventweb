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
$documentTypeLabels = [
    'all' => 'Semua Dokumen',
    'proposal' => 'Proposal',
    'report' => 'Laporan',
];
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">Dokumen Event</span>
        <h2>Proposal dan laporan setiap event</h2>
    </div>
    <?php if ($isAdminSystem ?? false): ?>
        <span class="status status-aktif">Mode CRUD admin</span>
    <?php else: ?>
        <span class="status">Mode lihat</span>
    <?php endif; ?>
</div>

<?php if ($documentNotifications !== []): ?>
    <section class="panel document-upload-alerts" data-document-notification-panel>
        <div class="panel-header">
            <div>
                <span class="eyebrow">Upload terbaru</span>
                <h2>Proposal/Laporan baru diupload</h2>
            </div>
            <span class="status status-diajukan" data-document-notification-count><?= esc((string) count($documentNotifications)) ?> notifikasi</span>
        </div>
        <div class="notification-list expanded">
            <?php foreach ($documentNotifications as $notification): ?>
                <?php
                    $notificationLink = (string) ($notification['link'] ?? '');
                    $notificationType = (string) ($notification['type'] ?? '');
                    $notificationHref = $notificationLink !== ''
                        ? site_url('documents/notification/open?' . http_build_query([
                            'key' => (string) ($notification['key'] ?? ''),
                            'event' => (string) ($notification['event_id'] ?? ''),
                            'type' => $notificationType,
                        ]))
                        : '';
                ?>
                <div class="notification-item is-unread" data-document-notification>
                    <strong><?= esc($notification['label'] ?? 'Dokumen') ?> - <?= esc($notification['event_title'] ?? '-') ?></strong>
                    <p>
                        <?= esc($notification['campus'] ?? '-') ?>
                        <?php if (! empty($notification['uploaded_at'])): ?>
                            - diupload <?= esc(date('d M Y H:i', strtotime($notification['uploaded_at']))) ?>
                        <?php endif; ?>
                        <?php if (! empty($notification['pic_name'])): ?>
                            oleh <?= esc($notification['pic_name']) ?>
                        <?php endif; ?>
                    </p>
                    <?php if ($notificationHref !== ''): ?>
                        <a class="text-link" href="<?= esc($notificationHref) ?>" target="_blank" rel="noopener" data-dismiss-document-notification>Buka dokumen</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="panel">
    <form class="report-filter-bar" action="<?= site_url('documents/' . $selectedDocumentType) ?>" method="get">
        <label>
            Kampus pelaksana
            <select class="control" name="campus">
                <option value="">Semua kampus</option>
                <?php foreach ($documentCampusOptions as $campus): ?>
                    <option value="<?= esc($campus) ?>" <?= $selectedDocumentCampus === $campus ? 'selected' : '' ?>><?= esc($campus) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Jenis dokumen
            <select class="control" name="type" onchange="if (this.value) window.location.href='<?= site_url('documents') ?>/' + this.value + (this.form.campus.value ? '?campus=' + encodeURIComponent(this.form.campus.value) : '')">
                <?php foreach ($documentTypeLabels as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= $selectedDocumentType === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="report-filter-actions">
            <button class="primary-button compact-button" type="submit">Terapkan</button>
            <a class="ghost-button compact-button" href="<?= site_url('documents') ?>">Reset</a>
        </div>
    </form>
</section>

<section class="document-list">
    <?php
        $documentPageUrl = static function (int $page) use ($selectedDocumentType, $documentPagination): string {
            $query = $documentPagination['query'] ?? [];
            $query['page'] = $page;

            return site_url('documents/' . $selectedDocumentType . '?' . http_build_query($query));
        };
    ?>
    <div class="event-table-summary">
        <span><?= esc($documentPagination['from']) ?>-<?= esc($documentPagination['to']) ?> dari <?= esc($documentPagination['total']) ?> event</span>
        <span>Urutan berdasarkan tanggal event terbaru</span>
    </div>

    <?php foreach ($documentRows as $row): ?>
        <?php $event = $row['event'] ?? []; ?>
        <article class="document-list-item">
            <div class="document-event-header">
                <div>
                    <span class="eyebrow"><?= esc($event['id'] ?? '-') ?></span>
                    <h3><?= esc($event['title'] ?? '-') ?></h3>
                </div>
                <small><?= esc($event['campus'] ?? '-') ?> - <?= esc($event['date_label'] ?? (! empty($event['date']) ? date('d M Y', strtotime($event['date'])) : '-')) ?></small>
            </div>

            <div class="document-type-list">
                <?php foreach (($row['documents'] ?? []) as $document): ?>
                    <?php
                    $type = (string) ($document['type'] ?? '');
                    $link = (string) ($document['link'] ?? '');
                    $usesFileUpload = in_array($type, ['proposal', 'report'], true);
                    $linkIsExternal = preg_match('/^https?:\/\//i', $link) === 1;
                    $documentHref = $link !== ''
                        ? ($usesFileUpload && ! $linkIsExternal ? site_url('events/' . ($event['id'] ?? '') . '/documents/' . $type . '/file') : $link)
                        : '';
                    $documentDisplay = $link !== '' && $usesFileUpload && ! $linkIsExternal
                        ? basename(str_replace('\\', '/', $link))
                        : $link;
                    $formAction = site_url('documents/' . ($event['id'] ?? '') . '/' . $type . '/link');
                    $deleteAction = site_url('documents/' . ($event['id'] ?? '') . '/' . $type . '/delete');
                    ?>
                    <div class="document-type-row document-admin-row">
                        <div class="document-type-main">
                            <strong><?= esc($document['label'] ?? '-') ?></strong>
                            <small>Kode: <?= esc($document['code'] ?? '-') ?> - Upload checklist: <?= esc($document['upload_status'] ?? '-') ?></small>
                            <?php if (! empty($document['submitted_at'])): ?>
                                <small>Diupload <?= esc(date('d M Y H:i', strtotime($document['submitted_at']))) ?><?= ! empty($document['pic_name']) ? ' oleh ' . esc($document['pic_name']) : '' ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="document-type-main">
                            <span class="status status-<?= esc(strtolower(str_replace(' ', '-', (string) ($document['status'] ?? 'draft')))) ?>"><?= esc($document['status'] ?? '-') ?></span>
                            <?php if ($link !== ''): ?>
                                <small><?= esc($documentDisplay) ?></small>
                            <?php else: ?>
                                <small>Belum ada dokumen dari upload event.</small>
                            <?php endif; ?>
                        </div>

                        <div class="document-actions">
                            <?php if ($documentHref !== ''): ?>
                                <a class="ghost-button compact-button" href="<?= esc($documentHref) ?>" target="_blank" rel="noopener">Buka</a>
                            <?php else: ?>
                                <span class="muted-note">Belum ada</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($isAdminSystem ?? false): ?>
                            <details class="document-admin-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <form class="document-admin-form" action="<?= $formAction ?>" method="post" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="campus" value="<?= esc($selectedDocumentCampus, 'attr') ?>">
                                    <input type="hidden" name="selected_document_type" value="<?= esc($selectedDocumentType, 'attr') ?>">
                                    <label>
                                        File dokumen
                                        <input class="control table-control" type="file" name="document_file" accept=".doc,.docx,.pdf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                    </label>
                                    <?php if ($type === 'report'): ?>
                                        <label>
                                            Status laporan
                                            <input class="control table-control" type="text" name="realization_status" value="<?= esc($document['status'] ?? '', 'attr') ?>">
                                        </label>
                                        <label>
                                            Skor
                                            <input class="control table-control" type="number" min="0" max="100" name="score" value="<?= esc((string) ($document['score'] ?? 0), 'attr') ?>">
                                        </label>
                                        <label class="document-admin-notes">
                                            Catatan
                                            <textarea class="control table-control" name="notes" rows="2"><?= esc($document['notes'] ?? '') ?></textarea>
                                        </label>
                                    <?php else: ?>
                                        <label>
                                            Status proposal
                                            <input class="control table-control" type="text" name="status" value="<?= esc($document['status'] ?? '', 'attr') ?>">
                                        </label>
                                    <?php endif; ?>
                                    <div class="button-row">
                                        <button class="primary-button compact-button" type="submit">Simpan</button>
                                    </div>
                                </form>
                                <form action="<?= $deleteAction ?>" method="post" class="inline-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="campus" value="<?= esc($selectedDocumentCampus, 'attr') ?>">
                                    <input type="hidden" name="selected_document_type" value="<?= esc($selectedDocumentType, 'attr') ?>">
                                    <button class="danger-button compact-button" type="submit">Hapus Dokumen</button>
                                </form>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($documentRows === []): ?>
        <div class="notification-item">
            <strong>Belum ada dokumen</strong>
            <p>Tidak ada proposal atau laporan event sesuai filter kampus yang dipilih.</p>
        </div>
    <?php endif; ?>

    <?php if (($documentPagination['totalPages'] ?? 1) > 1): ?>
        <nav class="pagination-bar" aria-label="Navigasi halaman dokumen">
            <a
                class="ghost-button compact-button <?= ($documentPagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                href="<?= ($documentPagination['current'] ?? 1) <= 1 ? '#' : esc($documentPageUrl((int) $documentPagination['current'] - 1)) ?>"
            >Sebelumnya</a>
            <div class="pagination-pages">
                <?php for ($page = 1; $page <= (int) $documentPagination['totalPages']; $page++): ?>
                    <?php if ($page === 1 || $page === (int) $documentPagination['totalPages'] || abs($page - (int) $documentPagination['current']) <= 2): ?>
                        <a
                            class="ghost-button compact-button <?= $page === (int) $documentPagination['current'] ? 'is-active' : '' ?>"
                            href="<?= esc($documentPageUrl($page)) ?>"
                        ><?= esc($page) ?></a>
                    <?php elseif (abs($page - (int) $documentPagination['current']) === 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <a
                class="ghost-button compact-button <?= ($documentPagination['current'] ?? 1) >= ($documentPagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                href="<?= ($documentPagination['current'] ?? 1) >= ($documentPagination['totalPages'] ?? 1) ? '#' : esc($documentPageUrl((int) $documentPagination['current'] + 1)) ?>"
            >Berikutnya</a>
        </nav>
    <?php endif; ?>
</section>
