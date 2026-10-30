<?php
$event = $event ?? [];
$allEvents = $allEvents ?? [];
$templates = $templates ?? [];
$selectedDocType = $selectedDocType ?? 'proposal';
$selectedTemplateId = (int) ($selectedTemplateId ?? ($templates[0]['id'] ?? 0));
$existingDocument = $existingDocument ?? null;
$initialContent = $initialContent ?? ($existingDocument['content'] ?? '');
$isGenerated = ! empty($initialContent);
$isAdminSystem = session('user.role') === 'Admin Sistem';

$docTitleLabel = $selectedDocType === 'proposal' ? 'Proposal' : 'Laporan';
$eventCode = (string) ($event['id'] ?? $event['code'] ?? 'EVT-000');
$eventDbId = (int) ($event['db_id'] ?? 0);
$eventTitle = (string) ($event['title'] ?? 'Event Universitas BSI');
$eventCampus = trim((string) ($event['campus'] ?? $event['campus_name'] ?? ''));
if ($eventCampus === '' || $eventCampus === 'Belum ditentukan') {
    $cId = (int) ($event['campus_id'] ?? 0);
    if ($cId > 0) {
        $cRow = (new \App\Models\CampusModel())->find($cId);
        if ($cRow && ! empty($cRow['name'])) {
            $eventCampus = (string) $cRow['name'];
        }
    }
}
if ($eventCampus === '' || $eventCampus === 'Belum ditentukan') {
    $eventCampus = 'UBSI Kampus Pelaksana';
}
$eventDate = (string) ($event['date_range_label'] ?? $event['date'] ?? date('d F Y'));
$eventLocation = (string) ($event['location'] ?? 'Auditorium Kampus');
$eventCategory = (string) ($event['category'] ?? 'Akademik');
$eventPic = (string) ($event['pic_name'] ?? 'Panitia Pelaksana');
$eventPicPhone = (string) ($event['pic_phone'] ?? '0812-3456-7890');

$defaultGoals = 'Menjelaskan program PMB, Beasiswa Jalur Undangan (BJU), dan memperkenalkan kampus UBSI kepada siswa kelas XII.';
$defaultTarget = 'Siswa kelas XII, guru BK, dan perwakilan sekolah.';

$allSections = [
    'latar_belakang' => ['label' => 'Latar Belakang Kegiatan', 'checked' => true],
    'tujuan' => ['label' => 'Tujuan Kegiatan', 'checked' => true],
    'manfaat' => ['label' => 'Manfaat Kegiatan', 'checked' => true],
    'sasaran' => ['label' => 'Sasaran Peserta', 'checked' => true],
    'konsep' => ['label' => 'Konsep Kegiatan', 'checked' => true],
    'panitia' => ['label' => 'Susunan Panitia', 'checked' => true],
    'rundown' => ['label' => 'Rundown Kegiatan', 'checked' => true],
    'indikator' => ['label' => 'Indikator Keberhasilan', 'checked' => true],
    'rab' => ['label' => 'Rencana Anggaran Biaya (RAB)', 'checked' => true],
    'penutup' => ['label' => 'Penutup', 'checked' => true],
    'lampiran' => ['label' => 'Lampiran (opsional)', 'checked' => false],
    'saran' => ['label' => 'Saran (opsional)', 'checked' => false],
];
?>

<style>
    .ai-gen-page {
        --ai-primary: #2563eb;
        --ai-primary-dark: #1d4ed8;
        --ai-border: #e2e8f0;
        --ai-ink: #0f172a;
        --ai-muted: #64748b;
        display: flex;
        flex-direction: column;
        gap: 18px;
        color: var(--ai-ink);
        font-family: inherit;
    }

    /* Stepper & Header */
    .ai-gen-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .ai-gen-head-left h1 {
        margin: 0 0 4px;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .ai-gen-head-left p {
        margin: 0;
        color: var(--ai-muted);
        font-size: 13.5px;
    }
    .ai-stepper {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 700;
    }
    .ai-step-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
    }
    .ai-step-item.is-done { color: #15803d; }
    .ai-step-item.is-active { color: var(--ai-primary); }
    .ai-step-num {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        display: grid;
        place-items: center;
        font-size: 11px;
    }
    .ai-step-item.is-done .ai-step-num { background: #dcfce7; color: #15803d; }
    .ai-step-item.is-active .ai-step-num { background: var(--ai-primary); color: #fff; }
    .ai-step-line { width: 24px; height: 1.5px; background: #e2e8f0; }

    /* 3-Column Grid */
    .ai-gen-layout {
        display: grid;
        grid-template-columns: 270px minmax(380px, 1fr) minmax(360px, 480px);
        gap: 18px;
        align-items: start;
    }

    /* Cards */
    .ai-card {
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .ai-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    .ai-card-head h2, .ai-card-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--ai-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Left Column Details */
    .ai-event-cover {
        width: 100%;
        height: 110px;
        border-radius: 10px;
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        padding: 12px;
        color: #fff;
    }
    .ai-event-cover-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 3px 8px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        font-size: 10.5px;
        font-weight: 700;
    }
    .ai-event-meta-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        font-size: 12.5px;
    }
    .ai-event-meta-row {
        display: grid;
        grid-template-columns: 85px 1fr;
        gap: 8px;
        color: #475569;
    }
    .ai-event-meta-row strong {
        color: var(--ai-ink);
        font-weight: 700;
    }

    /* Document Type Selector Cards */
    .ai-doc-type-choice {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1.5px solid var(--ai-border);
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        color: inherit;
    }
    .ai-doc-type-choice:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }
    .ai-doc-type-choice.is-active {
        border-color: var(--ai-primary);
        background: #eff6ff;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .ai-doc-type-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: #fff;
        border: 1px solid #bfdbfe;
        color: var(--ai-primary);
        flex-shrink: 0;
    }
    .ai-doc-type-choice.is-active .ai-doc-type-icon {
        background: var(--ai-primary);
        color: #fff;
        border-color: var(--ai-primary);
    }
    .ai-doc-type-choice strong {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
    }
    .ai-doc-type-choice small {
        font-size: 11.5px;
        color: var(--ai-muted);
        line-height: 1.3;
        display: block;
    }

    /* Center Column: Template Selector Grid */
    .ai-tpl-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
    }
    .ai-tpl-card {
        border: 2px solid var(--ai-border);
        border-radius: 12px;
        padding: 8px;
        background: #fff;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
    }
    .ai-tpl-card:hover {
        border-color: #93c5fd;
        transform: translateY(-2px);
    }
    .ai-tpl-card.is-active {
        border-color: var(--ai-primary);
        background: #fafcff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .ai-tpl-radio {
        position: absolute;
        top: 12px;
        right: 12px;
        accent-color: var(--ai-primary);
        width: 16px;
        height: 16px;
    }
    .ai-tpl-thumb {
        width: 100%;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .ai-tpl-title {
        font-size: 12px;
        font-weight: 800;
        color: var(--ai-ink);
        line-height: 1.3;
    }
    .ai-tpl-desc {
        font-size: 10.5px;
        color: var(--ai-muted);
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Brief Tabs & Inputs */
    .ai-brief-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 1px solid var(--ai-border);
        margin-bottom: 12px;
    }
    .ai-brief-tab {
        padding: 6px 12px;
        font-size: 12.5px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: var(--ai-muted);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.15s ease;
    }
    .ai-brief-tab.is-active {
        color: var(--ai-primary);
        border-bottom-color: var(--ai-primary);
    }
    .ai-brief-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .ai-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .ai-field label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        display: flex;
        justify-content: space-between;
    }
    .ai-input, .ai-textarea {
        border: 1px solid var(--ai-border);
        border-radius: 9px;
        padding: 8px 10px;
        font-size: 12.5px;
        font-family: inherit;
        background: #fff;
        color: var(--ai-ink);
        outline: none;
        transition: border-color 0.15s ease;
    }
    .ai-input[readonly] {
        background: #f8fafc;
        color: #64748b;
    }
    .ai-input:focus, .ai-textarea:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
    }
    .ai-char-count {
        font-size: 11px;
        color: #94a3b8;
    }

    /* Section Checkboxes */
    .ai-sec-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px 12px;
    }
    .ai-sec-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        user-select: none;
    }
    .ai-sec-item input {
        width: 15px;
        height: 15px;
        accent-color: var(--ai-primary);
        margin: 0;
    }

    /* Action buttons */
    .ai-actions-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding-top: 10px;
        border-top: 1px solid #f1f5f9;
    }
    .ai-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        border: 1px solid var(--ai-border);
        background: #fff;
        color: var(--ai-ink);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .ai-btn-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        color: #fff;
        box-shadow: 0 6px 18px -4px rgba(37, 99, 235, 0.5);
    }
    .ai-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.6);
        color: #fff;
    }
    .ai-btn-ai-gen {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        border: none;
        color: #fff !important;
        font-weight: 800;
        padding: 11px 22px;
        box-shadow: 0 6px 20px -4px rgba(124, 58, 237, 0.45);
    }
    .ai-btn-ai-gen:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 26px -4px rgba(124, 58, 237, 0.55);
    }
    .ai-btn-success {
        background: #15803d;
        color: #fff;
        border-color: #15803d;
    }

    /* Right Column: Preview Pane */
    .ai-preview-card {
        padding: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .ai-preview-bar {
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid var(--ai-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    .ai-preview-tools {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ai-preview-tool-btn {
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid var(--ai-border);
        background: #fff;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .ai-preview-tool-btn.is-active {
        background: var(--ai-primary);
        color: #fff;
        border-color: var(--ai-primary);
    }

    /* Document Sheet */
    .ai-doc-canvas {
        background: #64748b;
        padding: 16px;
        max-height: 520px;
        overflow-y: auto;
        display: flex;
        justify-content: center;
    }
    .ai-doc-sheet {
        width: 100%;
        max-width: 440px;
        min-height: 600px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        border-radius: 4px;
        padding: 24px;
        font-size: 11px;
        line-height: 1.5;
        color: #1e293b;
        position: relative;
    }
    .ai-doc-cover {
        border-bottom: 2px solid #2563eb;
        padding-bottom: 18px;
        margin-bottom: 18px;
        text-align: center;
    }
    .ai-doc-cover-brand {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 8px;
    }
    .ai-doc-cover-brand strong {
        font-size: 12px;
        color: #1e3a8a;
        font-weight: 900;
    }
    .ai-doc-cover-title {
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 6px;
        text-transform: uppercase;
        letter-spacing: -0.01em;
    }
    .ai-doc-cover-event {
        font-size: 13px;
        font-weight: 800;
        color: #2563eb;
        margin: 0 0 14px;
    }
    .ai-doc-cover-meta {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        display: inline-block;
        text-align: left;
        font-size: 10.5px;
    }
    .ai-doc-cover-meta table td {
        padding: 2px 4px;
    }
    .ai-doc-body h4 {
        margin: 14px 0 6px;
        font-size: 11.5px;
        font-weight: 800;
        color: #1e3a8a;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 3px;
        text-transform: uppercase;
    }
    .ai-doc-body p {
        margin: 0 0 8px;
        text-align: justify;
    }
    .ai-doc-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
        font-size: 10px;
    }
    .ai-doc-body table th {
        background: #f1f5f9;
        font-weight: 700;
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        text-align: left;
    }
    .ai-doc-body table td {
        border: 1px solid #e2e8f0;
        padding: 4px 6px;
    }

    /* Result summary card */
    .ai-result-box {
        padding: 16px;
        background: #fff;
        border-top: 1px solid var(--ai-border);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ai-result-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 9px;
        border-radius: 999px;
        background: #dcfce7;
        color: #15803d;
        font-size: 11.5px;
        font-weight: 800;
        align-self: flex-start;
    }
    .ai-result-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Modal Event Picker */
    .ai-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .ai-modal-overlay[hidden] { display: none; }
    .ai-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
    }
    .ai-modal-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--ai-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ai-modal-body {
        padding: 16px 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .ai-event-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 14px;
        border: 1px solid var(--ai-border);
        border-radius: 10px;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s ease;
    }
    .ai-event-item:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }
    .ai-event-item.is-selected {
        border-color: var(--ai-primary);
        background: #eff6ff;
    }

    @media (max-width: 1200px) {
        .ai-gen-layout { grid-template-columns: 260px 1fr; }
        .ai-preview-card { grid-column: 1 / -1; }
    }
    @media (max-width: 800px) {
        .ai-gen-layout { grid-template-columns: 1fr; }
    }
</style>

<div class="ai-gen-page">
    <!-- Header & Stepper -->
    <div class="ai-gen-head">
        <div class="ai-gen-head-left">
            <div style="font-size:12.5px; color:#64748b; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                <span>Dashboard</span> &rsaquo; <a href="<?= site_url('documents') ?>" style="color:#64748b; text-decoration:none;">Dokumen Event</a> &rsaquo; <strong style="color:#0f172a;">Generate <?= esc($docTitleLabel) ?></strong>
            </div>
            <h1>Generate <?= esc($docTitleLabel) ?> dengan AI</h1>
            <p>Buat dokumen <?= strtolower(esc($docTitleLabel)) ?> menggunakan template resmi dan bantuan AI</p>
        </div>

        <div class="ai-stepper">
            <div class="ai-step-item is-done">
                <span class="ai-step-num">✓</span>
                <span>1 Pilih Event</span>
            </div>
            <div class="ai-step-line"></div>
            <div class="ai-step-item is-active">
                <span class="ai-step-num">2</span>
                <span>2 Konfigurasi &amp; Generate</span>
            </div>
            <div class="ai-step-line"></div>
            <div class="ai-step-item">
                <span class="ai-step-num">3</span>
                <span>3 Preview &amp; Edit</span>
            </div>
            <div class="ai-step-line"></div>
            <div class="ai-step-item">
                <span class="ai-step-num">4</span>
                <span>4 Simpan Dokumen</span>
            </div>
        </div>
    </div>

    <!-- 3-Column Layout -->
    <div class="ai-gen-layout">
        
        <!-- KOLOM 1: Informasi Event & Jenis Dokumen -->
        <div style="display:flex; flex-direction:column; gap:16px;">
            <div class="ai-card">
                <div class="ai-card-head">
                    <h3>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span>Informasi Event</span>
                    </h3>
                    <button type="button" class="ai-btn" style="padding:4px 8px; font-size:11px;" onclick="openEventPickerModal()">
                        ✏️ Ubah Event
                    </button>
                </div>

                <div class="ai-event-cover">
                    <span class="ai-event-cover-badge"><?= esc($eventCategory) ?></span>
                    <div>
                        <strong style="font-size:13px; line-height:1.3; display:block;"><?= esc($eventTitle) ?></strong>
                        <small style="opacity:0.85; font-size:11px;"><?= esc($eventCampus) ?></small>
                    </div>
                </div>

                <div class="ai-event-meta-list">
                    <div class="ai-event-meta-row">
                        <span>Tanggal</span>
                        <strong>: <?= esc($eventDate) ?></strong>
                    </div>
                    <div class="ai-event-meta-row">
                        <span>Lokasi</span>
                        <strong>: <?= esc($eventLocation) ?></strong>
                    </div>
                    <div class="ai-event-meta-row">
                        <span>Jenis Event</span>
                        <strong>: <?= esc($eventCategory) ?></strong>
                    </div>
                    <div class="ai-event-meta-row">
                        <span>PIC</span>
                        <strong>: <?= esc($eventPic) ?> (<?= esc($eventPicPhone) ?>)</strong>
                    </div>
                </div>
            </div>

            <!-- Pilih Jenis Dokumen -->
            <div class="ai-card">
                <div class="ai-card-head">
                    <h3>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                        <span>Pilih Jenis Dokumen</span>
                    </h3>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <a
                        class="ai-doc-type-choice <?= $selectedDocType === 'proposal' ? 'is-active' : '' ?>"
                        href="<?= site_url('documents/generate?event_id=' . urlencode((string) $eventDbId) . '&type=proposal') ?>"
                    >
                        <div class="ai-doc-type-icon">📄</div>
                        <div style="flex:1;">
                            <strong>Proposal Event</strong>
                            <small>Dokumen rencana kegiatan sebelum pelaksanaan</small>
                        </div>
                        <input type="radio" name="ui_doc_type" <?= $selectedDocType === 'proposal' ? 'checked' : '' ?> style="accent-color:#2563eb;">
                    </a>

                    <a
                        class="ai-doc-type-choice <?= $selectedDocType === 'report' ? 'is-active' : '' ?>"
                        href="<?= site_url('documents/generate?event_id=' . urlencode((string) $eventDbId) . '&type=report') ?>"
                    >
                        <div class="ai-doc-type-icon" style="color:#16a34a; border-color:#bbf7d0;">📑</div>
                        <div style="flex:1;">
                            <strong>Laporan Event</strong>
                            <small>Dokumen hasil kegiatan setelah pelaksanaan</small>
                        </div>
                        <input type="radio" name="ui_doc_type" <?= $selectedDocType === 'report' ? 'checked' : '' ?> style="accent-color:#16a34a;">
                    </a>
                </div>
            </div>
        </div>

        <!-- KOLOM 2: Konfigurasi Generate Dokumen -->
        <div class="ai-card" style="gap:18px;">
            <div class="ai-card-head">
                <div>
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3z"/></svg>
                        <span>Konfigurasi Generate <?= esc($docTitleLabel) ?></span>
                    </h2>
                    <small style="color:#64748b; font-size:12px;">Lengkapi informasi tambahan dan pilih bagian yang akan dibuat oleh AI.</small>
                </div>
                <button type="button" class="ai-btn" style="padding:5px 11px; font-size:11.5px;" onclick="syncEventData()">
                    🔄 Gunakan Data Event
                </button>
            </div>

            <form id="aiGenerateForm" onsubmit="event.preventDefault(); processGenerateAi();">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= esc((string) $eventDbId) ?>">
                <input type="hidden" name="type" value="<?= esc($selectedDocType) ?>">

                <!-- A. Pilih Template Resmi -->
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <strong style="font-size:13px; color:#1e293b;">A. Pilih Template Resmi</strong>
                    <div class="ai-tpl-grid">
                        <?php foreach ($templates as $tIdx => $tpl): ?>
                            <?php
                            $isSelected = (int) $tpl['id'] === $selectedTemplateId || ($selectedTemplateId === 0 && $tIdx === 0);
                            $thumbSrc = ! empty($tpl['thumbnail_path']) ? base_url($tpl['thumbnail_path']) : base_url('assets/img/templates/proposal_sosialisasi.svg');
                            ?>
                            <label class="ai-tpl-card <?= $isSelected ? 'is-active' : '' ?>">
                                <input
                                    type="radio"
                                    name="template_id"
                                    value="<?= esc($tpl['id']) ?>"
                                    class="ai-tpl-radio"
                                    <?= $isSelected ? 'checked' : '' ?>
                                    onchange="document.querySelectorAll('.ai-tpl-card').forEach(c=>c.classList.remove('is-active')); this.closest('.ai-tpl-card').classList.add('is-active');"
                                >
                                <img src="<?= esc($thumbSrc) ?>" alt="<?= esc($tpl['name']) ?>" class="ai-tpl-thumb">
                                <span class="ai-tpl-title"><?= esc($tpl['name']) ?></span>
                                <span class="ai-tpl-desc"><?= esc($tpl['description'] ?? 'Template resmi institusi') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- B. Informasi dan Brief untuk AI -->
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong style="font-size:13px; color:#1e293b;">B. Informasi dan Brief untuk AI</strong>
                        <small style="color:#64748b; font-size:11px;">ⓘ Data dasar event terisi otomatis.</small>
                    </div>

                    <div class="ai-brief-tabs">
                        <button type="button" class="ai-brief-tab is-active" onclick="switchBriefTab(this, 'tab-system')">Data dari Sistem</button>
                        <button type="button" class="ai-brief-tab" onclick="switchBriefTab(this, 'tab-brief')">Tambahan Brief untuk AI</button>
                    </div>

                    <div id="tab-system">
                        <div class="ai-brief-grid">
                            <div class="ai-field">
                                <label>Nama Event</label>
                                <input type="text" class="ai-input" name="event_title" value="<?= esc($eventTitle) ?>" readonly>
                            </div>
                            <div class="ai-field">
                                <label>Kampus Pelaksana</label>
                                <input type="text" class="ai-input" name="campus" value="<?= esc($eventCampus) ?>" placeholder="Contoh: UBSI Kampus Dewi Sartika B">
                            </div>
                            <div class="ai-field">
                                <label>Tanggal</label>
                                <input type="text" class="ai-input" name="event_date" value="<?= esc($eventDate) ?>" readonly>
                            </div>
                            <div class="ai-field">
                                <label>Lokasi</label>
                                <input type="text" class="ai-input" name="location" value="<?= esc($eventLocation) ?>" readonly>
                            </div>
                            <div class="ai-field" style="grid-column: 1 / -1;">
                                <label>
                                    <span>Tujuan Kegiatan</span>
                                    <span class="ai-char-count" id="count-goals">104/500</span>
                                </label>
                                <textarea id="input-goals" name="goals" class="ai-textarea" rows="2" oninput="updateCharCount(this, 'count-goals', 500)"><?= esc($defaultGoals) ?></textarea>
                            </div>
                            <div class="ai-field" style="grid-column: 1 / -1;">
                                <label>
                                    <span>Target Peserta</span>
                                    <span class="ai-char-count" id="count-target">56/500</span>
                                </label>
                                <textarea id="input-target" name="target_participants" class="ai-textarea" rows="2" oninput="updateCharCount(this, 'count-target', 500)"><?= esc($defaultTarget) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="tab-brief" style="display:none;">
                        <div class="ai-field">
                            <label>
                                <span>Instruksi Khusus untuk AI</span>
                                <span class="ai-char-count" id="count-brief">0/1000</span>
                            </label>
                            <textarea
                                id="input-custom-brief"
                                name="custom_brief"
                                class="ai-textarea"
                                rows="5"
                                placeholder="Tuliskan poin khusus, narasumber yang ingin dicantumkan, target nominal beasiswa, atau kebutuhan fasilitas khusus yang perlu dirinci oleh AI..."
                                oninput="updateCharCount(this, 'count-brief', 1000)"
                            ></textarea>
                            <small style="color:#64748b; font-size:11px; margin-top:2px;">AI akan menggabungkan instruksi ini ke dalam susunan bab dan tabel.</small>
                        </div>
                    </div>
                </div>

                <!-- C. Pilih Bagian yang Digenerate AI -->
                <div style="display:flex; flex-direction:column; gap:8px; margin-top:14px;">
                    <div>
                        <strong style="font-size:13px; color:#1e293b;">C. Pilih Bagian yang Digenerate AI</strong>
                        <small style="display:block; color:#64748b; font-size:11px;">Centang bagian yang ingin dibuat oleh AI. Anda tetap dapat mengedit hasilnya nanti.</small>
                    </div>

                    <div class="ai-sec-grid">
                        <?php foreach ($allSections as $sKey => $sVal): ?>
                            <label class="ai-sec-item">
                                <input type="checkbox" name="sections[]" value="<?= esc($sKey) ?>" <?= $sVal['checked'] ? 'checked' : '' ?>>
                                <span><?= esc($sVal['label']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="ai-actions-row" style="margin-top:18px;">
                    <button type="button" class="ai-btn" onclick="resetForm()">
                        🔄 Reset Form
                    </button>
                    <button type="submit" id="btnGenerate" class="ai-btn ai-btn-ai-gen">
                        <span>✨ Generate Isi <?= esc($docTitleLabel) ?> dengan AI</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- KOLOM 3: Preview Dokumen -->
        <div class="ai-card ai-preview-card">
            <div class="ai-preview-bar">
                <strong style="font-size:13.5px; display:flex; align-items:center; gap:6px;">
                    <span>📄 Preview Dokumen</span>
                </strong>
                <div class="ai-preview-tools">
                    <button type="button" id="tabBtnPreview" class="ai-preview-tool-btn is-active" onclick="switchPreviewMode('rendered')">👁 Preview</button>
                    <button type="button" id="tabBtnEdit" class="ai-preview-tool-btn" onclick="switchPreviewMode('editor')">✏️ Edit Teks</button>
                    <button type="button" class="ai-preview-tool-btn" onclick="toggleFullscreenCanvas()" title="Layar Penuh">⛶</button>
                </div>
            </div>

            <!-- Canvas View -->
            <div class="ai-doc-canvas" id="docCanvasWrap">
                <!-- Rendered Visual Sheet -->
                <div class="ai-doc-sheet" id="renderedSheet">
                    <div id="sheetContent">
                        <!-- Cover Page -->
                        <div class="ai-doc-cover">
                            <div class="ai-doc-cover-brand">
                                <strong>BSI GROUP</strong>
                                <span style="font-size:9.5px; color:#2563eb; font-weight:700;">Kampus Digital Kreatif</span>
                            </div>
                            <h3 class="ai-doc-cover-title"><?= strtoupper(esc($docTitleLabel)) ?> KEGIATAN</h3>
                            <div class="ai-doc-cover-event"><?= esc($eventTitle) ?></div>
                            <div class="ai-doc-cover-meta">
                                <table>
                                    <tr><td>Tanggal</td><td>: <?= esc($eventDate) ?></td></tr>
                                    <tr><td>Lokasi</td><td>: <?= esc($eventLocation) ?></td></tr>
                                    <tr><td>Kampus</td><td>: <?= esc($eventCampus) ?></td></tr>
                                </table>
                            </div>
                        </div>

                        <!-- Content Body -->
                        <div class="ai-doc-body" id="sheetBodyText">
                            <?php if (! empty($initialContent)): ?>
                                <?= $initialContent ?>
                            <?php else: ?>
                                <h4>BAB I PENDAHULUAN</h4>
                                <p><strong>1.1 Latar Belakang</strong><br>
                                Pendidikan tinggi memegang peranan krusial dalam membentuk generasi profesional yang kompetitif di era digital. Dalam rangka mendukung program sosialisasi pendidikan dan penjaringan calon mahasiswa berprestasi, Universitas Bina Sarana Informatika menyelenggarakan kegiatan ini.</p>
                                
                                <p><strong>1.2 Tujuan Kegiatan</strong><br>
                                <?= esc($defaultGoals) ?></p>

                                <h4>BAB II SUSUNAN PANITIA &amp; RUNDOWN</h4>
                                <table>
                                    <thead>
                                        <tr><th>Waktu</th><th>Sesi Acara</th><th>Pengisi Acara</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>08.30 - 09.00</td><td>Registrasi &amp; Pembukaan</td><td>Tim Panitia</td></tr>
                                        <tr><td>09.00 - 10.30</td><td>Penyampaian Materi Sosialisasi</td><td>Narasumber PMB</td></tr>
                                        <tr><td>10.30 - 11.30</td><td>Tanya Jawab &amp; Konsultasi</td><td>Guru BK &amp; Siswa</td></tr>
                                        <tr><td>11.30 - 12.00</td><td>Penutupan &amp; Foto Bersama</td><td>Seluruh Peserta</td></tr>
                                    </tbody>
                                </table>

                                <h4>BAB III RENCANA ANGGARAN BIAYA (RAB)</h4>
                                <table>
                                    <thead>
                                        <tr><th>Keterangan</th><th>Vol</th><th>Total (Rp)</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>Konsumsi Peserta &amp; Panitia</td><td>1 Paket</td><td>1.500.000</td></tr>
                                        <tr><td>Kit Sosialisasi &amp; Brosur PMB</td><td>1 Paket</td><td>750.000</td></tr>
                                        <tr><td>Dokumentasi &amp; Sertifikat</td><td>1 Paket</td><td>500.000</td></tr>
                                        <tr><th colspan="2">Total Estimasi Anggaran</th><th>2.750.000</th></tr>
                                    </tbody>
                                </table>

                                <h4>BAB IV PENUTUP</h4>
                                <p>Demikian proposal ini kami susun sebagai acuan pelaksanaan kegiatan. Dukungan dan kerjasama dari seluruh pihak sangat kami harapkan demi kelancaran kegiatan ini.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Textarea Editor (Hidden by default) -->
                <textarea
                    id="rawEditorText"
                    class="ai-textarea"
                    style="width:100%; max-width:440px; height:580px; display:none; font-family:monospace; font-size:11.5px; line-height:1.5; padding:16px;"
                    oninput="document.getElementById('sheetBodyText').innerHTML = this.value;"
                ><?= esc($initialContent) ?></textarea>
            </div>

            <!-- Hasil Generate Action Footer -->
            <div class="ai-result-box">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="ai-result-badge" id="genBadge">
                        ✓ <?= $isGenerated ? 'Telah dibuat' : 'Draft Standar Siap' ?>
                    </span>
                    <small style="color:#64748b; font-size:11px;">Template Resmi BSI Group</small>
                </div>
                <p style="margin:0; font-size:12px; color:#475569;" id="genStatusDesc">
                    Dokumen <?= strtolower(esc($docTitleLabel)) ?> telah siap menggunakan template resmi institusi dengan bantuan AI.
                </p>
                <div class="ai-result-buttons">
                    <button type="button" class="ai-btn" style="flex:1; padding:8px 12px; font-size:12px;" onclick="downloadWordDoc()">
                        <span style="color:#2563eb;">⬇</span> Download Word
                    </button>
                    <button type="button" class="ai-btn" style="flex:1; padding:8px 12px; font-size:12px;" onclick="downloadPdfDoc()">
                        <span style="color:#dc2626;">⬇</span> Download PDF
                    </button>
                    <button type="button" class="ai-btn ai-btn-primary" style="width:100%; padding:10px 14px; font-size:13px;" onclick="saveToEventDocument()">
                        💾 Simpan ke Dokumen Event
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Ubah Event -->
<div class="ai-modal-overlay" id="eventPickerModal" hidden onclick="if(event.target===this)closeEventPickerModal()">
    <div class="ai-modal">
        <div class="ai-modal-head">
            <h3 style="margin:0; font-size:16px; font-weight:800;">Pilih Event untuk Generate Dokumen</h3>
            <button type="button" style="background:none; border:none; font-size:20px; cursor:pointer;" onclick="closeEventPickerModal()">&times;</button>
        </div>
        <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0;">
            <input
                type="text"
                class="ai-input"
                style="width:100%;"
                placeholder="🔍 Ketik nama event atau kode untuk mencari..."
                oninput="filterEventList(this.value)"
            >
        </div>
        <div class="ai-modal-body" id="eventListContainer">
            <?php foreach ($allEvents as $ev): ?>
                <?php
                $evId = (int) ($ev['db_id'] ?? $ev['id'] ?? 0);
                $evCode = (string) ($ev['id'] ?? $ev['code'] ?? '');
                $evTitle = (string) ($ev['title'] ?? '');
                $evCampus = (string) ($ev['campus'] ?? '');
                $isSelected = $evId === $eventDbId || $evCode === $eventCode;
                ?>
                <a
                    class="ai-event-item <?= $isSelected ? 'is-selected' : '' ?>"
                    href="<?= site_url('documents/generate?event_id=' . urlencode((string) $evId) . '&type=' . urlencode($selectedDocType)) ?>"
                    data-search="<?= strtolower(esc($evTitle . ' ' . $evCode . ' ' . $evCampus)) ?>"
                >
                    <div>
                        <strong style="font-size:13.5px;"><?= esc($evTitle) ?></strong>
                        <div style="font-size:11.5px; color:#64748b; margin-top:2px;">
                            <span><?= esc($evCode) ?></span> &middot; <span><?= esc($evCampus) ?></span>
                        </div>
                    </div>
                    <?php if ($isSelected): ?>
                        <span style="font-size:11px; font-weight:700; color:#2563eb; background:#eff6ff; padding:2px 8px; border-radius:999px;">Aktif</span>
                    <?php else: ?>
                        <span style="font-size:12px; color:#64748b;">Pilih &rarr;</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function updateCharCount(el, targetId, max) {
    const len = el.value.length;
    document.getElementById(targetId).textContent = len + '/' + max;
}

function switchBriefTab(btn, targetId) {
    document.querySelectorAll('.ai-brief-tab').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
    document.getElementById('tab-system').style.display = targetId === 'tab-system' ? 'block' : 'none';
    document.getElementById('tab-brief').style.display = targetId === 'tab-brief' ? 'block' : 'none';
}

function switchPreviewMode(mode) {
    const btnPreview = document.getElementById('tabBtnPreview');
    const btnEdit = document.getElementById('tabBtnEdit');
    const sheet = document.getElementById('renderedSheet');
    const editor = document.getElementById('rawEditorText');

    if (mode === 'editor') {
        btnPreview.classList.remove('is-active');
        btnEdit.classList.add('is-active');
        editor.value = document.getElementById('sheetBodyText').innerHTML.trim();
        sheet.style.display = 'none';
        editor.style.display = 'block';
    } else {
        btnEdit.classList.remove('is-active');
        btnPreview.classList.add('is-active');
        document.getElementById('sheetBodyText').innerHTML = editor.value;
        editor.style.display = 'none';
        sheet.style.display = 'block';
    }
}

function toggleFullscreenCanvas() {
    const wrap = document.getElementById('docCanvasWrap');
    if (!document.fullscreenElement) {
        wrap.requestFullscreen().catch(()=>{});
    } else {
        document.exitFullscreen().catch(()=>{});
    }
}

function syncEventData() {
    document.getElementById('input-goals').value = '<?= esc($defaultGoals, 'js') ?>';
    document.getElementById('input-target').value = '<?= esc($defaultTarget, 'js') ?>';
    updateCharCount(document.getElementById('input-goals'), 'count-goals', 500);
    updateCharCount(document.getElementById('input-target'), 'count-target', 500);
}

function resetForm() {
    document.getElementById('aiGenerateForm').reset();
    syncEventData();
}

function openEventPickerModal() {
    document.getElementById('eventPickerModal').removeAttribute('hidden');
}
function closeEventPickerModal() {
    document.getElementById('eventPickerModal').setAttribute('hidden', '');
}
function filterEventList(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#eventListContainer .ai-event-item').forEach(item => {
        const text = item.getAttribute('data-search') || '';
        item.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

// Proses Generate dengan AI
function processGenerateAi() {
    const btn = document.getElementById('btnGenerate');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⚡ Menghubungi AI...</span>';

    const form = document.getElementById('aiGenerateForm');
    const formData = new FormData(form);

    fetch('<?= site_url('documents/generate/process') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origText;

        if (data.ok) {
            document.getElementById('sheetBodyText').innerHTML = data.content_html || data.content;
            document.getElementById('rawEditorText').value = data.content_html || data.content;
            
            document.getElementById('genBadge').textContent = '✓ Berhasil dibuat oleh AI';
            document.getElementById('genBadge').style.background = '#dcfce7';
            document.getElementById('genBadge').style.color = '#15803d';
            document.getElementById('genStatusDesc').textContent = 'Dokumen ' + '<?= esc($docTitleLabel) ?>'.toLowerCase() + ' berhasil digenerate menggunakan template resmi dengan AI.';
            
            // Switch to preview mode
            switchPreviewMode('rendered');
        } else {
            alert(data.message || 'Gagal memproses generate dokumen.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origText;
        alert('Terjadi kesalahan koneksi saat generate AI.');
    });
}

// Simpan Dokumen ke Dokumen Event
function saveToEventDocument() {
    const content = document.getElementById('sheetBodyText').innerHTML;
    const templateInput = document.querySelector('input[name="template_id"]:checked');
    const templateId = templateInput ? templateInput.value : '<?= esc($selectedTemplateId) ?>';

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('event_id', '<?= esc((string) $eventDbId) ?>');
    formData.append('type', '<?= esc($selectedDocType) ?>');
    formData.append('template_id', templateId);
    formData.append('content', content);

    fetch('<?= site_url('documents/generate/save') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert('✓ Sukses! ' + data.message);
            window.location.href = '<?= site_url('documents') ?>';
        } else {
            alert('Gagal menyimpan: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => {
        alert('Gagal menyimpan ke Dokumen Event.');
    });
}

function downloadWordDoc() {
    const eventId = '<?= esc((string) $eventDbId) ?>';
    const type = '<?= esc($selectedDocType) ?>';
    window.location.href = '<?= site_url('documents/generate/export-word') ?>?event_id=' + encodeURIComponent(eventId) + '&type=' + encodeURIComponent(type);
}

function downloadPdfDoc() {
    const eventId = '<?= esc((string) $eventDbId) ?>';
    const type = '<?= esc($selectedDocType) ?>';
    window.open('<?= site_url('documents/generate/export-pdf') ?>?event_id=' + encodeURIComponent(eventId) + '&type=' + encodeURIComponent(type), '_blank');
}
</script>

