<div class="detail-layout">
    <section class="panel hero-panel">
        <span class="status status-<?= esc($event['status']) ?>"><?= esc($event['status']) ?></span>
        <h2><?= esc($event['title']) ?></h2>
        <p><?= esc($event['description']) ?></p>
        <div class="detail-meta">
            <span><?= esc($event['date_label'] ?? date('d M Y', strtotime($event['date']))) ?>, <?= esc($event['time']) ?></span>
            <span><?= esc($event['campus']) ?></span>
            <span><?= esc($event['location']) ?></span>
            <span><?= esc($event['participants_count']) ?> peserta</span>
            <span><?= esc($event['budget']) ?></span>
        </div>
        <?php if ($canSubmitDraftEvent ?? false): ?>
            <form action="<?= site_url('events/' . $event['id'] . '/submit') ?>" method="post" class="button-row event-submit-row">
                <?= csrf_field() ?>
                <button class="primary-button" type="submit">Ajukan Event</button>
            </form>
        <?php endif; ?>
        <?php if ($canManageChecklist ?? false): ?>
            <div class="button-row event-submit-row">
                <a class="ghost-button" href="<?= site_url('events/' . $event['id'] . '/edit') ?>">Update Event</a>
                <?php if (! empty($event['can_delete'])): ?>
                    <form action="<?= site_url('events/' . $event['id'] . '/delete') ?>" method="post" class="inline-form">
                        <?= csrf_field() ?>
                        <button class="danger-button" type="submit">Hapus Event</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php elseif (! empty($event['can_delete'])): ?>
            <form action="<?= site_url('events/' . $event['id'] . '/delete') ?>" method="post" class="button-row event-submit-row">
                <?= csrf_field() ?>
                <button class="danger-button" type="submit">Hapus Event</button>
            </form>
        <?php endif; ?>
    </section>

    <details class="panel progress-checklist-panel checklist-accordion">
        <summary class="progress-checklist-summary">
            <div class="progress-checklist-hero">
                <div>
                    <span class="eyebrow">Progress event</span>
                    <h2>Checklist kelengkapan</h2>
                    <p><?= esc($checklistSummary['done'] ?? 0) ?> dari <?= esc($checklistSummary['total'] ?? 0) ?> item lengkap untuk memastikan event siap dijalankan.</p>
                </div>
                <span class="accordion-indicator" aria-hidden="true">Buka</span>
            </div>
            <div class="progress-stats">
                <span><strong><?= esc($checklistSummary['done'] ?? 0) ?></strong> selesai</span>
                <span><strong><?= esc($checklistSummary['pending'] ?? 0) ?></strong> pending</span>
                <span><strong><?= esc($calendarIntegration['sync_status'] ?? 'pending_connect') ?></strong> Google Calendar</span>
            </div>
        </summary>
        <div class="checklist-accordion-body">

        <?php if ($canManageChecklist): ?>
            <form action="<?= site_url('events/' . $event['id'] . '/checklist/items') ?>" method="post" class="checklist-add-form">
                <?= csrf_field() ?>
                <label>
                    Kelengkapan baru
                    <input class="control" type="text" name="name" placeholder="Contoh: Vendor sound system" required>
                </label>
                <label>
                    Fase
                    <select class="control" name="phase" required>
                        <option>Pra Event</option>
                        <option>Event</option>
                        <option>Pasca Event</option>
                    </select>
                </label>
                <label>
                    PIC
                    <select class="control" name="pic_user_id">
                        <option value="">Belum dipilih</option>
                        <?php foreach ($checklistUsers as $user): ?>
                            <option value="<?= esc($user['id']) ?>"><?= esc($user['name']) ?> - <?= esc($user['role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Deadline
                    <input class="control" type="date" name="deadline">
                </label>
                <label class="checklist-add-notes">
                    Keterangan pekerjaan
                    <textarea class="control" name="notes" rows="3" placeholder="Instruksi singkat untuk PIC"></textarea>
                </label>
                <label class="checklist-add-material">
                    Link bahan event
                    <input class="control" type="url" name="material_drive_link" placeholder="https://contoh.com/bahan-event">
                </label>
                <label class="check-row compact-check assign-new-check">
                    <input type="checkbox" name="is_assigned" value="1">
                    <span>Kirim tugas</span>
                </label>
                <button class="primary-button" type="submit">Tambah Item</button>
            </form>
        <?php endif; ?>

        <form action="<?= site_url('events/' . $event['id'] . '/checklist') ?>" method="post" class="checklist-form">
            <?= csrf_field() ?>
            <?php foreach ($checklistGroups as $phase => $items): ?>
                <div class="checklist-phase">
                    <div class="phase-title">
                        <strong><?= esc($phase) ?></strong>
                        <span><?= esc(count($items)) ?> item</span>
                    </div>
                    <div class="checklist-row-list">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $isDone = ($item['status'] ?? '') === 'done';
                            $itemDriveLink = trim((string) ($item['drive_link'] ?? ''));
                            $itemNotes = trim((string) ($item['notes'] ?? ''));
                            $itemMaterialDriveLink = trim((string) ($item['material_drive_link'] ?? ''));
                            $itemDocumentType = match (strtolower(trim((string) ($item['name'] ?? '')))) {
                                'proposal' => 'proposal',
                                'laporan' => 'report',
                                default => null,
                            };
                            $itemLinkIsExternal = preg_match('/^https?:\/\//i', $itemDriveLink) === 1;
                            $itemDocumentHref = $itemDriveLink !== ''
                                ? ($itemLinkIsExternal || $itemDocumentType === null ? $itemDriveLink : site_url('events/' . $event['id'] . '/documents/' . $itemDocumentType . '/file'))
                                : '';
                            ?>
                            <article class="checklist-item-row <?= $isDone ? 'is-done' : '' ?>">
                                <label class="checklist-row-status">
                                    <input
                                        type="checkbox"
                                        name="done[]"
                                        value="<?= esc($item['id']) ?>"
                                        <?= $isDone ? 'checked' : '' ?>
                                        <?= $canManageChecklist ? '' : 'disabled' ?>
                                    >
                                    <span><?= $isDone ? 'Lengkap' : 'Pending' ?></span>
                                </label>
                                <div class="checklist-row-main">
                                    <h3><?= esc($item['name']) ?></h3>
                                    <?php if (! empty($item['completed_at'])): ?>
                                        <small>Selesai <?= esc(date('d M Y H:i', strtotime($item['completed_at']))) ?></small>
                                    <?php else: ?>
                                        <small><?= ! empty($item['deadline']) ? 'Target ' . esc(date('d M Y', strtotime($item['deadline']))) : 'Deadline belum diatur' ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="checklist-row-fields">
                                    <label class="assignment-check">
                                        Assign PIC
                                        <span>
                                            <input
                                                type="checkbox"
                                                name="assigned[]"
                                                value="<?= esc($item['id']) ?>"
                                                <?= (int) ($item['is_assigned'] ?? 0) === 1 ? 'checked' : '' ?>
                                                <?= $canManageChecklist ? '' : 'disabled' ?>
                                            >
                                            Kirim tugas
                                        </span>
                                    </label>
                                    <label>
                                        PIC
                                        <select class="control table-control" name="items[<?= esc($item['id']) ?>][pic_user_id]" <?= $canManageChecklist ? '' : 'disabled' ?>>
                                            <option value="">Belum dipilih</option>
                                            <?php foreach ($checklistUsers as $user): ?>
                                                <option value="<?= esc($user['id']) ?>" <?= (int) ($item['pic_user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>>
                                                    <?= esc($user['name']) ?> - <?= esc($user['role']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>
                                        Deadline
                                        <input
                                            class="control table-control"
                                            type="date"
                                            name="items[<?= esc($item['id']) ?>][deadline]"
                                            value="<?= esc($item['deadline'] ?? '') ?>"
                                            <?= $canManageChecklist ? '' : 'disabled' ?>
                                        >
                                    </label>
                                    <label class="checklist-work-note">
                                        Keterangan pekerjaan
                                        <textarea
                                            class="control table-control"
                                            name="items[<?= esc($item['id']) ?>][notes]"
                                            rows="3"
                                            placeholder="Keterangan pekerjaan untuk PIC"
                                            <?= $canManageChecklist ? '' : 'disabled' ?>
                                        ><?= esc($itemNotes) ?></textarea>
                                    </label>
                                    <label class="checklist-material-link">
                                        Link bahan event
                                        <input
                                            class="control table-control"
                                            type="url"
                                            name="items[<?= esc($item['id']) ?>][material_drive_link]"
                                            value="<?= esc($itemMaterialDriveLink) ?>"
                                            placeholder="https://contoh.com/bahan-event"
                                            <?= $canManageChecklist ? '' : 'disabled' ?>
                                        >
                                    </label>
                                </div>
                                <div class="checklist-row-report">
                                    <?php if ($itemMaterialDriveLink !== ''): ?>
                                        <a class="ghost-button compact-button" href="<?= esc($itemMaterialDriveLink) ?>" target="_blank" rel="noopener">Bahan Event</a>
                                    <?php endif; ?>
                                    <?php if ($itemDocumentHref !== ''): ?>
                                        <a class="ghost-button compact-button" href="<?= esc($itemDocumentHref) ?>" target="_blank" rel="noopener">Preview Hasil</a>
                                    <?php else: ?>
                                        <span class="muted-note">Belum ada laporan PIC</span>
                                    <?php endif; ?>
                                    <?php if (! empty($item['submitted_at'])): ?>
                                        <small>Dilaporkan <?= esc(date('d M Y H:i', strtotime($item['submitted_at']))) ?></small>
                                    <?php endif; ?>
                                    <?php if ($canManageChecklist): ?>
                                        <button
                                            class="danger-button compact-button"
                                            type="submit"
                                            formaction="<?= site_url('events/' . $event['id'] . '/checklist/items/' . $item['id'] . '/delete') ?>"
                                            formmethod="post"
                                        >Hapus</button>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($canManageChecklist): ?>
                <div class="button-row checklist-actions">
                    <button class="primary-button" type="submit">Simpan Checklist</button>
                </div>
            <?php else: ?>
                <p class="muted-note">Checklist hanya dapat diubah oleh Admin Sistem atau pembuat event.</p>
            <?php endif; ?>
        </form>
        </div>
    </details>

    <section class="panel">
        <?php
            $proposalUpload = $checklistDocumentUploads['proposal'] ?? null;
            $reportUpload = $checklistDocumentUploads['report'] ?? null;
            $photoUpload = $checklistDocumentUploads['foto'] ?? null;
            $videoUpload = $checklistDocumentUploads['video'] ?? null;
            $documentCards = [
                'proposal' => [
                    'label' => 'Proposal',
                    'id' => $proposal['id'] ?? 'Belum dibuat',
                    'status' => $proposal['status'] ?? 'Tidak tersedia',
                    'record_link' => $proposal['document_link'] ?? '',
                    'upload' => $proposalUpload,
                ],
                'report' => [
                    'label' => 'Laporan',
                    'id' => $report['id'] ?? 'Belum dibuka',
                    'status' => $report['realization_status'] ?? 'Menunggu event selesai',
                    'record_link' => $report['document_link'] ?? '',
                    'upload' => $reportUpload,
                ],
                'foto' => [
                    'label' => 'Link Dokumentasi Foto',
                    'id' => 'Dokumentasi foto',
                    'status' => $photoUpload['status'] ?? 'Belum upload',
                    'record_link' => '',
                    'upload' => $photoUpload,
                ],
                'video' => [
                    'label' => 'Link Dokumentasi Video',
                    'id' => 'Dokumentasi video',
                    'status' => $videoUpload['status'] ?? 'Belum upload',
                    'record_link' => '',
                    'upload' => $videoUpload,
                ],
            ];
        ?>
        <div class="panel-header">
            <div>
                <span class="eyebrow">Dokumen</span>
                <h2>Dokumen Event</h2>
            </div>
        </div>
        <div class="document-grid">
            <?php foreach ($documentCards as $type => $card): ?>
                <?php
                    $upload = $card['upload'] ?? null;
                    $documentLink = $card['record_link'] ?: ($upload['drive_link'] ?? '');
                    $documentLinkIsExternal = preg_match('/^https?:\/\//i', (string) $documentLink) === 1;
                    $usesFileUpload = in_array($type, ['proposal', 'report'], true);
                    $documentHref = $documentLink !== ''
                        ? ($usesFileUpload && ! $documentLinkIsExternal ? site_url('events/' . $event['id'] . '/documents/' . $type . '/file') : $documentLink)
                        : '';
                    $isPostEventDocument = in_array($type, ['report', 'foto', 'video'], true);
                    $canUploadThisDocument = ! $isPostEventDocument || ($canUploadReportDocument ?? false) || ($canManageChecklist ?? false);
                    $showProposalUpdateForm = $type === 'proposal' && $canUploadThisDocument && ($canManageChecklist ?? false);
                    $showDirectUploadForm = $canUploadThisDocument && ($documentLink === '' || $isPostEventDocument || $showProposalUpdateForm);
                ?>
                <article class="document-card">
                    <span><?= esc($card['label']) ?></span>
                    <strong><?= esc($card['id']) ?></strong>
                    <small><?= esc($card['status']) ?></small>
                    <div class="document-checklist-upload">
                        <small>Checklist: <?= esc($upload['status'] ?? 'belum upload') ?></small>
                        <?php if (! empty($upload['submitted_at'])): ?>
                            <small>Diupload <?= esc(date('d M Y H:i', strtotime($upload['submitted_at']))) ?></small>
                        <?php endif; ?>
                        <?php if ($documentHref !== ''): ?>
                            <a class="ghost-button compact-button" href="<?= esc($documentHref) ?>" target="_blank" rel="noopener"><?= $usesFileUpload ? 'Download Dokumen' : 'Lihat Dokumen' ?></a>
                        <?php else: ?>
                            <small>Belum ada dokumen yang diupload.</small>
                        <?php endif; ?>
                    </div>
                    <?php if ($showDirectUploadForm): ?>
                        <form class="document-card-upload-form" action="<?= site_url('events/' . $event['id'] . '/documents/' . $type . '/upload') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <?php if ($usesFileUpload): ?>
                                <label>
                                    Upload File
                                    <input class="control compact" type="file" name="document_file" accept=".doc,.docx,.pdf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                                </label>
                            <?php else: ?>
                                <label>
                                    Upload Link
                                    <input class="control compact" type="url" name="document_link" value="<?= esc($documentLink) ?>" placeholder="https://drive.google.com/..." required>
                                </label>
                            <?php endif; ?>
                            <button class="primary-button compact-button" type="submit">
                                <?= $usesFileUpload ? ($documentLink !== '' ? 'Update File' : 'Upload File') : ($documentLink !== '' ? 'Update Link' : 'Upload') ?>
                            </button>
                        </form>
                    <?php elseif ($documentLink === '' && $isPostEventDocument): ?>
                        <small>Dokumen pasca event dibuka setelah event terlaksana.</small>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <aside class="panel">
        <span class="eyebrow">PIC</span>
        <h2><?= esc($event['created_by']) ?></h2>
        <p>Kreator event dapat melihat status approval, memperbarui dokumen, dan mengisi laporan pasca event.</p>
        <a class="ghost-button full" href="<?= site_url('crm?event_id=' . urlencode((string) ($event['db_id'] ?? ''))) ?>">Lihat Data CRM Event</a>
        <?php if (($googleCalendar['connected'] ?? false) === true): ?>
            <p>Google Calendar terhubung. Event baru akan otomatis disinkronkan saat dibuat.</p>
        <?php else: ?>
            <a class="ghost-button full" href="<?= site_url('google-calendar/connect') ?>">Connect Google Calendar</a>
        <?php endif; ?>
        <?php if (! empty($calendarIntegration['sync_status'])): ?>
            <p>Status sync: <strong><?= esc($calendarIntegration['sync_status']) ?></strong></p>
            <?php if (! empty($calendarIntegration['external_link'])): ?>
                <a class="text-link" href="<?= esc($calendarIntegration['external_link']) ?>" target="_blank" rel="noopener">Buka di Google Calendar</a>
            <?php endif; ?>
        <?php endif; ?>
    </aside>
</div>
