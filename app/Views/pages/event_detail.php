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

    <?php
        $qstData = $questionnaire ?? [];
        $qstQuestions = is_array($qstData['questions'] ?? null) ? $qstData['questions'] : [];
        $qstResponses = is_array($questionnaireResponses ?? null) ? $questionnaireResponses : [];
        $qstPublicUrl = site_url('kuesioner/' . $event['id']);
        $qstShortUrl = site_url('q/' . $event['id']);
        $qstIsActive = (int) ($qstData['is_active'] ?? 1) === 1;
    ?>
    <section class="panel" id="event-questionnaire-section" style="grid-column: 1 / -1;">
        <div class="panel-header" style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:18px;">
            <div>
                <span class="eyebrow" style="color:#2563eb; font-weight:800;">Kuesioner Event • QR Code • Integrasi CRM</span>
                <h2 style="margin:4px 0 6px;">Form Kuesioner Responden & Sinkronisasi CRM Workspace</h2>
                <p style="margin:0; color:#475569; font-size:13.5px;">
                    Buat pertanyaan kuesioner kustom untuk event ini. Data responden otomatis dianalisis dan diklasifikasikan ke dalam <strong>Prospek Prioritas (Chat di Awal)</strong> atau <strong>Follow-up Lanjutan</strong> dan tersinkron langsung ke <a href="<?= site_url('crm?event_id=' . urlencode((string) ($event['db_id'] ?? ''))) ?>" style="color:#2563eb; font-weight:700; text-decoration:underline;">CRM Workspace</a>.
                </p>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                <span style="padding:6px 12px; border-radius:999px; font-size:12px; font-weight:700; <?= $qstIsActive ? 'background:#dcfce7; color:#15803d; border:1px solid #86efac;' : 'background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5;' ?>">
                    <?= $qstIsActive ? '● Form Aktif Menerima Responden' : '○ Form Ditutup Sementara' ?>
                </span>
                <span style="padding:6px 12px; border-radius:999px; font-size:12px; font-weight:700; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;">
                    <?= count($qstResponses) ?> Responden Masuk CRM
                </span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:22px; align-items:start;">
            <!-- Kolom Kiri: QR Code & Link Share -->
            <div style="background:linear-gradient(145deg, #f8fafc, #eff6ff); border:1.5px solid #bfdbfe; border-radius:18px; padding:22px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px;">
                    <strong style="font-size:15px; color:#0f172a;">QR Code & Link Responden</strong>
                    <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; background:#dbeafe; color:#1e40af;">Siap Scan</span>
                </div>

                <div style="display:flex; flex-direction:column; align-items:center; background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:20px; margin-bottom:16px; box-shadow:0 8px 24px rgba(15, 23, 42, 0.05);">
                    <div id="event-qst-qr-box" style="width:210px; height:210px; display:flex; align-items:center; justify-content:center;"></div>
                    <small style="margin-top:10px; font-weight:700; color:#1e293b; text-align:center;"><?= esc($event['title']) ?></small>
                    <small style="color:#64748b; font-size:11.5px; text-align:center;"><?= esc($event['campus']) ?> • Kode: <?= esc($event['id']) ?></small>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Link Publik Kuesioner Responden</label>
                    <div style="display:flex; gap:8px;">
                        <input id="event-qst-public-url" class="control" type="text" value="<?= esc($qstPublicUrl) ?>" readonly style="background:#ffffff; font-size:12.5px; font-weight:600; color:#0f172a;">
                        <button type="button" class="primary-button compact-button" id="btn-copy-qst-url" style="white-space:nowrap;">Salin Link</button>
                    </div>
                    <small style="display:block; margin-top:5px; font-size:11.5px; color:#64748b;">
                        Shortlink alternatif: <a href="<?= esc($qstShortUrl) ?>" target="_blank" rel="noopener" style="color:#2563eb; font-weight:600;"><?= esc($qstShortUrl) ?></a>
                    </small>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:14px;">
                    <button type="button" class="primary-button compact-button" id="btn-download-qst-poster" style="justify-content:center; width:100%;">
                        ⬇ Download QR Poster (PNG)
                    </button>
                    <a class="ghost-button compact-button" href="<?= esc($qstPublicUrl) ?>" target="_blank" rel="noopener" style="justify-content:center; text-align:center;">
                        ↗ Buka Form Responden
                    </a>
                </div>

                <div style="margin-top:14px; padding-top:14px; border-top:1px dashed #cbd5e1; display:flex; flex-wrap:wrap; gap:8px; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:#475569;">Data langsung masuk ke CRM:</span>
                    <a class="ghost-button compact-button" href="<?= site_url('crm?event_id=' . urlencode((string) ($event['db_id'] ?? ''))) ?>" style="background:#ffffff;">
                        Buka CRM Workspace Event →
                    </a>
                </div>
            </div>

            <!-- Kolom Kanan: Form Builder Pertanyaan Kuesioner -->
            <div style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:18px; padding:22px;">
                <form action="<?= site_url('events/' . $event['id'] . '/questionnaire') ?>" method="post" id="event-qst-builder-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="questions_json" id="qst-questions-json-input" value="<?= esc(json_encode($qstQuestions, JSON_UNESCAPED_UNICODE)) ?>">

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px;">
                        <strong style="font-size:15px; color:#0f172a;">Pengaturan & Pembuat Pertanyaan Kuesioner</strong>
                        <label style="display:inline-flex; align-items:center; gap:7px; font-size:12.5px; font-weight:700; color:#0f172a; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= $qstIsActive ? 'checked' : '' ?> <?= ($canManageChecklist ?? false) ? '' : 'disabled' ?>>
                            <span>Aktifkan Form</span>
                        </label>
                    </div>

                    <div style="display:grid; gap:12px; margin-bottom:18px;">
                        <label style="font-size:12.5px; font-weight:700; color:#334155;">
                            Judul Form Kuesioner
                            <input
                                class="control"
                                type="text"
                                name="title"
                                value="<?= esc($qstData['title'] ?? ('Form Kuesioner & Daftar Hadir - ' . $event['title'])) ?>"
                                placeholder="Judul form kuesioner yang tampil ke responden"
                                <?= ($canManageChecklist ?? false) ? '' : 'readonly' ?>
                                required
                                style="margin-top:5px;"
                            >
                        </label>
                        <label style="font-size:12.5px; font-weight:700; color:#334155;">
                            Deskripsi / Instruksi Singkat untuk Responden
                            <textarea
                                class="control"
                                name="description"
                                rows="2"
                                placeholder="Tuliskan sapaan atau instruksi pengisian kuesioner..."
                                <?= ($canManageChecklist ?? false) ? '' : 'readonly' ?>
                                style="margin-top:5px;"
                            ><?= esc($qstData['description'] ?? '') ?></textarea>
                        </label>
                    </div>

                    <!-- Blok Isian Wajib Data Diri Peserta -->
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:14px; padding:14px 16px; margin-bottom:18px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:8px;">
                            <strong style="font-size:12.5px; color:#166534;">Isian Wajib Data Diri Peserta (Otomatis Terhubung)</strong>
                            <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; background:#dcfce7; color:#15803d;">Wajib Diisi</span>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:8px;">
                            <div style="background:#ffffff; border:1px solid #bbf7d0; border-radius:10px; padding:8px 10px; font-size:12px;">
                                <strong style="display:block; color:#0f172a;">1. Nama Siswa *</strong>
                                <span style="color:#64748b; font-size:11px;">Data Diri Peserta</span>
                            </div>
                            <div style="background:#ffffff; border:1px solid #bbf7d0; border-radius:10px; padding:8px 10px; font-size:12px;">
                                <strong style="display:block; color:#0f172a;">2. Asal Sekolah *</strong>
                                <span style="color:#64748b; font-size:11px;">Sekolah / Instansi</span>
                            </div>
                            <div style="background:#ffffff; border:1px solid #bbf7d0; border-radius:10px; padding:8px 10px; font-size:12px;">
                                <strong style="display:block; color:#0f172a;">3. No. WhatsApp *</strong>
                                <span style="color:#64748b; font-size:11px;">Kontak WhatsApp</span>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Pertanyaan Kustom Pembuat Event -->
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px;">
                        <div>
                            <strong style="font-size:13.5px; color:#0f172a;">Pertanyaan Tambahan (Dibuat oleh User)</strong>
                            <small style="display:block; color:#64748b; font-size:11.5px;">Jawaban otomatis dirangkum ke Catatan Lead & Asisten AI di CRM Workspace.</small>
                        </div>
                        <?php if ($canManageChecklist ?? false): ?>
                            <button type="button" class="ghost-button compact-button" id="btn-add-qst-question" style="border-color:#2563eb; color:#2563eb; font-weight:700;">
                                + Tambah Pertanyaan
                            </button>
                        <?php endif; ?>
                    </div>

                    <div id="qst-questions-container" style="display:grid; gap:12px; margin-bottom:16px;"></div>

                    <?php if ($canManageChecklist ?? false): ?>
                        <div class="button-row" style="justify-content:flex-end; margin-top:14px;">
                            <button type="submit" class="primary-button">Simpan Form Kuesioner & Update QR</button>
                        </div>
                    <?php else: ?>
                        <p class="muted-note">Hanya Admin Sistem atau pembuat event yang dapat mengubah daftar pertanyaan kuesioner.</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Tabel Rekap Responden Kuesioner Event -->
        <?php
            $priorityResponses = array_values(array_filter($qstResponses, static fn ($r): bool => ($r['prospect_status'] ?? '') === 'priority'));
            $nurtureResponses = array_values(array_filter($qstResponses, static fn ($r): bool => ($r['prospect_status'] ?? '') === 'nurture'));
        ?>
        <div style="margin-top:24px; padding-top:20px; border-top:1px solid #e2e8f0;">
            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
                <div>
                    <strong style="font-size:15px; color:#0f172a;">Daftar Responden Kuesioner (<?= count($qstResponses) ?> Responden)</strong>
                    <small style="display:block; color:#64748b; font-size:12px;">Data langsung dianalisis otomatis untuk membedakan calon pendaftar prioritas dan follow up lanjutan.</small>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <a class="ghost-button compact-button" href="<?= site_url('events/' . $event['id'] . '/questionnaire/export') ?>">
                        ⬇ Download Excel Responden (.XLSX)
                    </a>
                    <a class="primary-button compact-button" href="<?= site_url('crm?event_id=' . urlencode((string) ($event['db_id'] ?? '')) . '&prospect_priority=priority') ?>" style="background:linear-gradient(135deg, #d97706, #b45309); border-color:#b45309;">
                        ⭐ Chat Prospek Prioritas di CRM →
                    </a>
                    <a class="ghost-button compact-button" href="<?= site_url('crm?event_id=' . urlencode((string) ($event['db_id'] ?? ''))) ?>">
                        Buka CRM Workspace →
                    </a>
                </div>
            </div>

            <!-- Filter Pills Prospek di Halaman Event -->
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px;" id="event-qst-filter-pills">
                <button type="button" class="ghost-button compact-button is-active" data-filter-prospect="all" style="font-weight:700;">
                    📋 Semua Responden (<?= count($qstResponses) ?>)
                </button>
                <button type="button" class="ghost-button compact-button" data-filter-prospect="priority" style="font-weight:700; color:#b45309; border-color:#f59e0b;">
                    ⭐ Prospek Prioritas (Chat di Awal) (<?= count($priorityResponses) ?>)
                </button>
                <button type="button" class="ghost-button compact-button" data-filter-prospect="nurture" style="font-weight:700; color:#475569;">
                    🕒 Follow-up Lanjutan (Setelah Prospek) (<?= count($nurtureResponses) ?>)
                </button>
            </div>

            <?php if ($qstResponses === []): ?>
                <div style="padding:24px; border-radius:14px; background:#f8fafc; border:1px dashed #cbd5e1; text-align:center; color:#64748b; font-size:13.5px;">
                    Belum ada responden yang mengisi kuesioner untuk event ini. Bagikan <strong>Link Publik</strong> atau tampilkan <strong>QR Code</strong> di atas kepada peserta.
                </div>
            <?php else: ?>
                <div style="overflow-x:auto; border:1px solid #e2e8f0; border-radius:14px;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;" id="event-qst-table">
                        <thead>
                            <tr style="background:#f8fafc; color:#334155; text-align:left; border-bottom:1px solid #e2e8f0;">
                                <th style="padding:11px 14px;">No</th>
                                <th style="padding:11px 14px;">Nama & Status</th>
                                <th style="padding:11px 14px;">Kontak (WA & Email)</th>
                                <th style="padding:11px 14px;">Asal Sekolah / Instansi</th>
                                <th style="padding:11px 14px;">Prioritas CRM</th>
                                <th style="padding:11px 14px;">Jawaban Pertanyaan Kuesioner</th>
                                <th style="padding:11px 14px;">Status CRM</th>
                                <th style="padding:11px 14px;">Waktu Mengisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($qstResponses, 0, 50) as $rIdx => $resp): ?>
                                <?php
                                    $ansItems = json_decode((string) ($resp['answers_json'] ?? '[]'), true);
                                    $ansItems = is_array($ansItems) ? $ansItems : [];
                                    $isPriority = ($resp['prospect_status'] ?? '') === 'priority';
                                    $pStatus = $resp['participant_status'] ?? 'Siswa';
                                    $pClass = $resp['student_class'] ?? '';
                                ?>
                                <tr style="border-bottom:1px solid #f1f5f9;" data-prospect="<?= $isPriority ? 'priority' : 'nurture' ?>">
                                    <td style="padding:11px 14px; font-weight:700; color:#64748b;"><?= $rIdx + 1 ?></td>
                                    <td style="padding:11px 14px;">
                                        <div style="font-weight:700; color:#0f172a;"><?= esc($resp['student_name'] ?? '-') ?></div>
                                        <div style="margin-top:3px; display:flex; gap:4px; flex-wrap:wrap;">
                                            <span style="display:inline-block; padding:2px 7px; border-radius:6px; background:#eff6ff; color:#1d4ed8; font-size:10.5px; font-weight:700; border:1px solid #bfdbfe;">
                                                <?= esc($pStatus) ?><?= ($pStatus === 'Siswa' && $pClass) ? ' (Kelas ' . esc($pClass) . ')' : '' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="padding:11px 14px;">
                                        <div style="font-family:monospace; font-weight:600; color:#0f172a;"><?= esc($resp['whatsapp'] ?? '-') ?></div>
                                        <?php if (! empty($resp['email'])): ?>
                                            <div style="font-size:11.5px; color:#64748b; margin-top:2px;">✉ <?= esc($resp['email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:11px 14px; color:#334155;"><?= esc($resp['school_origin'] ?? '-') ?></td>
                                    <td style="padding:11px 14px;">
                                        <?php if ($isPriority): ?>
                                            <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; background:#fef3c7; border:1px solid #f59e0b; color:#92400e; font-size:11px; font-weight:800;">
                                                ⭐ Prospek Prioritas (Chat Diawal)
                                            </span>
                                        <?php else: ?>
                                            <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; background:#f1f5f9; border:1px solid #cbd5e1; color:#475569; font-size:11px; font-weight:700;">
                                                🕒 Follow-up Lanjutan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:11px 14px; max-width:380px;">
                                        <?php if ($ansItems === []): ?>
                                            <span style="color:#94a3b8;">-</span>
                                        <?php else: ?>
                                            <div style="display:grid; gap:4px;">
                                                <?php foreach ($ansItems as $ans): ?>
                                                    <?php if (! empty($ans['answer'])): ?>
                                                        <div style="font-size:12px; line-height:1.4;">
                                                            <strong style="color:#475569;"><?= esc($ans['label'] ?? 'Pertanyaan') ?>:</strong>
                                                            <span style="color:#0f172a;"><?= esc($ans['answer']) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:11px 14px;">
                                        <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:700;">
                                            ✓ Masuk CRM<?= ! empty($resp['crm_lead_id']) ? ' #' . (int) $resp['crm_lead_id'] : '' ?>
                                        </span>
                                    </td>
                                    <td style="padding:11px 14px; color:#64748b; font-size:12px;">
                                        <?= ! empty($resp['submitted_at']) ? esc(date('d M Y H:i', strtotime($resp['submitted_at']))) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="<?= base_url('assets/js/lib/qrcode-svg.js') ?>"></script>
    <script>
    (function () {
        const publicUrl = <?= json_encode($qstPublicUrl) ?>;
        const eventTitle = <?= json_encode((string) ($event['title'] ?? 'EventHub')) ?>;
        const campusName = <?= json_encode((string) ($event['campus'] ?? 'Kampus Pelaksana')) ?>;
        const eventCode = <?= json_encode((string) ($event['id'] ?? 'EVT')) ?>;
        const canEdit = <?= json_encode((bool) ($canManageChecklist ?? false)) ?>;

        const qrBox = document.getElementById('event-qst-qr-box');
        if (qrBox && window.EventHubQRCode) {
            window.EventHubQRCode.renderSvg(qrBox, publicUrl, 210);
        }

        const copyBtn = document.getElementById('btn-copy-qst-url');
        const urlInput = document.getElementById('event-qst-public-url');
        if (copyBtn && urlInput) {
            copyBtn.addEventListener('click', function () {
                urlInput.select();
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(urlInput.value);
                } else {
                    document.execCommand('copy');
                }
                const origText = copyBtn.textContent;
                copyBtn.textContent = '✓ Tersalin!';
                setTimeout(function () { copyBtn.textContent = origText; }, 1800);
            });
        }

        const downloadBtn = document.getElementById('btn-download-qst-poster');
        if (downloadBtn && window.EventHubQRCode) {
            downloadBtn.addEventListener('click', function () {
                window.EventHubQRCode.downloadPoster(
                    publicUrl,
                    eventTitle,
                    campusName,
                    'QR-Kuesioner-' + eventCode + '.png'
                );
            });
        }

        const hiddenInput = document.getElementById('qst-questions-json-input');
        const container = document.getElementById('qst-questions-container');
        const addBtn = document.getElementById('btn-add-qst-question');
        if (!hiddenInput || !container) return;

        let questions = [];
        try {
            questions = JSON.parse(hiddenInput.value || '[]');
            if (!Array.isArray(questions)) questions = [];
        } catch (e) {
            questions = [];
        }

        function syncHiddenInput() {
            hiddenInput.value = JSON.stringify(questions);
        }

        function renderQuestions() {
            container.innerHTML = '';
            if (questions.length === 0) {
                const empty = document.createElement('div');
                empty.style.cssText = 'padding:14px; border-radius:12px; background:#f8fafc; border:1px dashed #cbd5e1; color:#64748b; font-size:12.5px; text-align:center;';
                empty.textContent = 'Tidak ada pertanyaan tambahan. Responden hanya akan mengisi 3 data diri wajib (Nama Siswa, Asal Sekolah, No. WA).';
                container.appendChild(empty);
                syncHiddenInput();
                return;
            }

            questions.forEach(function (q, index) {
                const card = document.createElement('div');
                card.style.cssText = 'background:#f8fafc; border:1px solid #cbd5e1; border-radius:14px; padding:14px; display:grid; gap:10px;';

                const topBar = document.createElement('div');
                topBar.style.cssText = 'display:flex; align-items:center; justify-content:space-between; gap:8px;';
                topBar.innerHTML = '<strong style="font-size:12.5px; color:#1e40af;">Pertanyaan #' + (index + 1) + '</strong>';

                if (canEdit) {
                    const btnGroup = document.createElement('div');
                    btnGroup.style.cssText = 'display:flex; gap:6px; align-items:center;';

                    const reqLabel = document.createElement('label');
                    reqLabel.style.cssText = 'display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:700; color:#334155; margin-right:6px; cursor:pointer;';
                    const reqCheck = document.createElement('input');
                    reqCheck.type = 'checkbox';
                    reqCheck.checked = !!q.required;
                    reqCheck.addEventListener('change', function () {
                        q.required = reqCheck.checked;
                        syncHiddenInput();
                    });
                    reqLabel.appendChild(reqCheck);
                    reqLabel.appendChild(document.createTextNode('Wajib'));
                    btnGroup.appendChild(reqLabel);

                    const delBtn = document.createElement('button');
                    delBtn.type = 'button';
                    delBtn.className = 'danger-button compact-button';
                    delBtn.style.cssText = 'padding:4px 10px; font-size:11.5px;';
                    delBtn.textContent = 'Hapus';
                    delBtn.addEventListener('click', function () {
                        questions.splice(index, 1);
                        renderQuestions();
                    });
                    btnGroup.appendChild(delBtn);
                    topBar.appendChild(btnGroup);
                }
                card.appendChild(topBar);

                const rowGrid = document.createElement('div');
                rowGrid.style.cssText = 'display:grid; grid-template-columns: 2fr 1fr; gap:10px;';

                const labelWrap = document.createElement('div');
                const labelInput = document.createElement('input');
                labelInput.type = 'text';
                labelInput.className = 'control compact';
                labelInput.placeholder = 'Tulis kalimat pertanyaan (contoh: Minat Program Studi)';
                labelInput.value = q.label || '';
                labelInput.readOnly = !canEdit;
                labelInput.required = true;
                labelInput.addEventListener('input', function () {
                    q.label = labelInput.value;
                    syncHiddenInput();
                });
                labelWrap.appendChild(labelInput);

                const typeWrap = document.createElement('div');
                const typeSelect = document.createElement('select');
                typeSelect.className = 'control compact';
                typeSelect.disabled = !canEdit;
                [
                    { value: 'text', text: 'Isian Singkat (Text)' },
                    { value: 'textarea', text: 'Paragraf (Textarea)' },
                    { value: 'select', text: 'Pilihan Dropdown' },
                    { value: 'radio', text: 'Pilihan Tunggal (Radio)' },
                    { value: 'checkbox', text: 'Pilihan Ganda (Checkbox)' }
                ].forEach(function (opt) {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.text;
                    if ((q.type || 'text') === opt.value) o.selected = true;
                    typeSelect.appendChild(o);
                });
                typeWrap.appendChild(typeSelect);

                rowGrid.appendChild(labelWrap);
                rowGrid.appendChild(typeWrap);
                card.appendChild(rowGrid);

                const optionsWrap = document.createElement('div');
                const needsOptions = ['select', 'radio', 'checkbox'].indexOf(q.type || 'text') !== -1;
                optionsWrap.style.display = needsOptions ? 'block' : 'none';
                const optionsLabel = document.createElement('small');
                optionsLabel.style.cssText = 'display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;';
                optionsLabel.textContent = 'Pilihan Jawaban (pisahkan dengan koma):';
                const optionsInput = document.createElement('input');
                optionsInput.type = 'text';
                optionsInput.className = 'control compact';
                optionsInput.placeholder = 'Contoh: Sistem Informasi, Ilmu Komunikasi, Manajemen';
                optionsInput.value = Array.isArray(q.options) ? q.options.join(', ') : (q.options || '');
                optionsInput.readOnly = !canEdit;
                optionsInput.addEventListener('input', function () {
                    q.options = optionsInput.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                    syncHiddenInput();
                });
                optionsWrap.appendChild(optionsLabel);
                optionsWrap.appendChild(optionsInput);
                card.appendChild(optionsWrap);

                typeSelect.addEventListener('change', function () {
                    q.type = typeSelect.value;
                    const showOpt = ['select', 'radio', 'checkbox'].indexOf(q.type) !== -1;
                    optionsWrap.style.display = showOpt ? 'block' : 'none';
                    if (showOpt && (!Array.isArray(q.options) || q.options.length === 0)) {
                        q.options = ['Pilihan 1', 'Pilihan 2'];
                        optionsInput.value = q.options.join(', ');
                    }
                    syncHiddenInput();
                });

                container.appendChild(card);
            });

            syncHiddenInput();
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                questions.push({
                    id: 'q_custom_' + Date.now(),
                    label: '',
                    type: 'text',
                    required: false,
                    placeholder: '',
                    options: []
                });
                renderQuestions();
            });
        }

        renderQuestions();

        // Event Table Prospect Filter Pills
        const filterPills = document.querySelectorAll('#event-qst-filter-pills [data-filter-prospect]');
        const tableRows = document.querySelectorAll('#event-qst-table tbody tr[data-prospect]');
        filterPills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                const target = pill.getAttribute('data-filter-prospect');
                filterPills.forEach(function (p) {
                    p.classList.remove('is-active');
                    p.style.boxShadow = 'none';
                });
                pill.classList.add('is-active');
                pill.style.boxShadow = '0 0 0 2px rgba(37,99,235,0.3)';

                tableRows.forEach(function (row) {
                    const rowProspect = row.getAttribute('data-prospect');
                    if (target === 'all' || rowProspect === target) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    })();
    </script>

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
