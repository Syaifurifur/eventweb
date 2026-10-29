<?php
$eventCode = (string) ($event['code'] ?? $event['id'] ?? '');
$questions = is_array($questionnaire['questions'] ?? null) ? $questionnaire['questions'] : [];
$isActive = (int) ($questionnaire['is_active'] ?? 1) === 1;
$oldAnswers = old('custom_answers');
$oldAnswers = is_array($oldAnswers) ? $oldAnswers : [];
$flashError = session('error') ?: (is_object(session()) && method_exists(session(), 'getFlashdata') ? session()->getFlashdata('error') : null);
$flashSuccess = session('success') ?: (is_object(session()) && method_exists(session(), 'getFlashdata') ? session()->getFlashdata('success') : null);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Form Kuesioner Event') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(155deg, #0b1938 0%, #172b5c 45%, #f1f5f9 45.2%, #f8fafc 100%);
            background-attachment: fixed;
            color: #0f172a;
            min-height: 100vh;
            padding: 28px 16px 60px;
        }
        .qst-shell {
            max-width: 720px;
            margin: 0 auto;
        }
        .qst-brand-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            color: #ffffff;
        }
        .qst-brand-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .qst-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            color: #fff;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        }
        .qst-brand-title strong {
            display: block;
            font-size: 16px;
            letter-spacing: -0.02em;
        }
        .qst-brand-title span {
            font-size: 12px;
            color: #93c5fd;
        }
        .qst-crm-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.18);
            border: 1px solid rgba(74, 222, 128, 0.45);
            color: #bbf7d0;
            font-size: 11.5px;
            font-weight: 700;
        }
        .qst-card {
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .qst-hero {
            padding: 28px 28px 24px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #4f46e5 100%);
            color: #ffffff;
            position: relative;
        }
        .qst-event-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .qst-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(6px);
            font-size: 11.5px;
            font-weight: 600;
            color: #f8fafc;
        }
        .qst-hero h1 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.02em;
        }
        .qst-hero p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: #dbeafe;
        }
        .qst-body {
            padding: 28px;
        }
        .qst-alert {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.5;
        }
        .qst-alert.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .qst-alert.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .qst-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            margin-bottom: 18px;
            border-bottom: 2px solid #f1f5f9;
        }
        .qst-section-head h2 {
            margin: 0;
            font-size: 15.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .qst-section-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .qst-field {
            margin-bottom: 18px;
        }
        .qst-label {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 7px;
        }
        .qst-req {
            color: #ef4444;
            margin-left: 3px;
        }
        .qst-hint {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        .qst-input, .qst-select, .qst-textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid #cbd5e1;
            font-family: inherit;
            font-size: 14px;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.18s ease;
        }
        .qst-input:focus, .qst-select:focus, .qst-textarea:focus {
            outline: none;
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .qst-options-group {
            display: grid;
            gap: 9px;
            margin-top: 6px;
        }
        .qst-option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            transition: all 0.15s ease;
        }
        .qst-option-item:hover {
            border-color: #93c5fd;
            background: #eff6ff;
        }
        .qst-option-item input {
            width: 17px;
            height: 17px;
            accent-color: #2563eb;
            margin: 0;
            flex-shrink: 0;
        }
        .qst-radio-pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
        }
        .qst-pill-option {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            transition: all 0.15s ease;
            user-select: none;
        }
        .qst-pill-option:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .qst-pill-option.active, .qst-pill-option:has(input:checked) {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }
        .qst-pill-option input {
            accent-color: #2563eb;
            width: 17px;
            height: 17px;
            margin: 0;
        }
        .qst-student-class-box {
            padding: 16px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            margin-top: 6px;
        }
        .qst-submit-btn {
            width: 100%;
            padding: 15px 22px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.28);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            margin-top: 10px;
        }
        .qst-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.36);
        }
        .qst-success-state {
            text-align: center;
            padding: 26px 12px;
        }
        .qst-success-icon {
            width: 68px;
            height: 68px;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
        }
        .qst-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
        }
        @media (max-width: 600px) {
            .qst-hero, .qst-body { padding: 20px 18px; }
            .qst-hero h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="qst-shell">
        <div class="qst-brand-bar">
            <div class="qst-brand-left">
                <div class="qst-logo">EH</div>
                <div class="qst-brand-title">
                    <strong>EventHub Questionnaire</strong>
                    <span><?= esc($event['campus'] ?? 'Kampus Pelaksana') ?></span>
                </div>
            </div>
            <span class="qst-crm-pill">● Presensi & Layanan Resmi Event</span>
        </div>

        <div class="qst-card">
            <div class="qst-hero">
                <div class="qst-event-chips">
                    <span class="qst-chip">Kode Event: <?= esc($eventCode) ?></span>
                    <span class="qst-chip"><?= esc($event['category'] ?? 'Event') ?></span>
                    <span class="qst-chip"><?= esc($event['date_range_label'] ?? $event['date'] ?? '-') ?></span>
                    <?php if (! empty($event['location'])): ?>
                        <span class="qst-chip">📍 <?= esc($event['location']) ?></span>
                    <?php endif; ?>
                </div>
                <h1><?= esc($questionnaire['title'] ?? ('Kuesioner - ' . $event['title'])) ?></h1>
                <?php if (! empty($questionnaire['description'])): ?>
                    <p><?= nl2br(esc($questionnaire['description'])) ?></p>
                <?php endif; ?>
            </div>

            <div class="qst-body">
                <?php if (! empty($flashError)): ?>
                    <div class="qst-alert error"><?= esc($flashError) ?></div>
                <?php endif; ?>

                <?php if (! empty($submitted) || ! empty($flashSuccess)): ?>
                    <div class="qst-success-state">
                        <div class="qst-success-icon">✓</div>
                        <h2 style="margin:0 0 10px; font-size:22px; color:#0f172a;">Terima Kasih! Data Anda Berhasil Dikirim</h2>
                        <p style="margin:0 auto 22px; max-width:480px; color:#475569; font-size:14px; line-height:1.6;">
                            <?= esc($flashSuccess ?: 'Jawaban kuesioner Anda telah tersimpan dan otomatis tersambung ke sistem layanan informasi & CRM EventHub.') ?>
                        </p>
                        <a href="<?= site_url('kuesioner/' . $eventCode) ?>" style="display:inline-flex; align-items:center; gap:8px; padding:12px 22px; border-radius:12px; background:#eff6ff; color:#1d4ed8; font-weight:700; font-size:13.5px; text-decoration:none; border:1px solid #bfdbfe;">
                            + Isi Form untuk Responden Lainnya
                        </a>
                    </div>
                <?php elseif (! $isActive): ?>
                    <div class="qst-alert error" style="margin-bottom:0;">
                        Pengisian form kuesioner untuk event <strong><?= esc($event['title']) ?></strong> saat ini sedang ditutup oleh penyelenggara.
                    </div>
                <?php else: ?>
                    <form method="post" action="<?= site_url('kuesioner/' . $eventCode) ?>">
                        <?= csrf_field() ?>

                        <div class="qst-section-head">
                            <h2>1. Data Diri Peserta</h2>
                            <span class="qst-section-badge">Wajib Diisi</span>
                        </div>

                        <div class="qst-field">
                            <label class="qst-label" for="qst-student-name">
                                Nama Lengkap / Siswa <span class="qst-req">*</span>
                            </label>
                            <input
                                id="qst-student-name"
                                class="qst-input"
                                type="text"
                                name="student_name"
                                value="<?= esc((string) old('student_name')) ?>"
                                placeholder="Contoh: Raka Pratama"
                                required
                            >
                        </div>

                        <div class="qst-field">
                            <label class="qst-label" for="qst-email">
                                Alamat Email Aktif <span class="qst-req">*</span>
                            </label>
                            <input
                                id="qst-email"
                                class="qst-input"
                                type="email"
                                name="email"
                                value="<?= esc((string) old('email')) ?>"
                                placeholder="Contoh: raka.pratama@gmail.com"
                                required
                            >
                            <span class="qst-hint">Email aktif untuk pengiriman materi acara dan e-sertifikat kegiatan.</span>
                        </div>

                        <div class="qst-field">
                            <label class="qst-label" for="qst-whatsapp">
                                Nomor WhatsApp Aktif (No. WA) <span class="qst-req">*</span>
                            </label>
                            <input
                                id="qst-whatsapp"
                                class="qst-input"
                                type="tel"
                                name="whatsapp"
                                value="<?= esc((string) old('whatsapp')) ?>"
                                placeholder="Contoh: 081234567890"
                                required
                            >
                            <span class="qst-hint">Pastikan nomor terhubung dengan WhatsApp aktif untuk notifikasi dan layanan informasi.</span>
                        </div>

                        <div class="qst-field">
                            <label class="qst-label" for="qst-school-origin">
                                Asal Sekolah / Instansi <span class="qst-req">*</span>
                            </label>
                            <input
                                id="qst-school-origin"
                                class="qst-input"
                                type="text"
                                name="school_origin"
                                value="<?= esc((string) old('school_origin')) ?>"
                                placeholder="Contoh: SMA Negeri 1 Yogyakarta / Instansi"
                                required
                            >
                        </div>

                        <?php
                        $selectedStatus = (string) old('participant_status', 'Siswa');
                        $selectedClass = (string) old('student_class', '');
                        ?>
                        <div class="qst-field">
                            <label class="qst-label">
                                Status Peserta <span class="qst-req">*</span>
                            </label>
                            <div class="qst-radio-pill-group">
                                <?php foreach (['Siswa', 'Umum', 'Guru'] as $st): ?>
                                    <label class="qst-pill-option <?= $selectedStatus === $st ? 'active' : '' ?>">
                                        <input
                                            type="radio"
                                            name="participant_status"
                                            value="<?= esc($st) ?>"
                                            <?= $selectedStatus === $st ? 'checked' : '' ?>
                                            required
                                            onchange="updateParticipantStatusUI(this.value)"
                                        >
                                        <span><?= esc($st) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="qst-field" id="student-class-wrapper" style="<?= $selectedStatus === 'Siswa' ? '' : 'display:none;' ?>">
                            <div class="qst-student-class-box">
                                <label class="qst-label" style="margin-bottom:8px;">
                                    Kelas Berapa Saat Ini? (Khusus Siswa) <span class="qst-req">*</span>
                                </label>
                                <div class="qst-radio-pill-group">
                                    <?php foreach (['X', 'XI', 'XII'] as $cls): ?>
                                        <label class="qst-pill-option <?= $selectedClass === $cls ? 'active' : '' ?>">
                                            <input
                                                type="radio"
                                                name="student_class"
                                                value="<?= esc($cls) ?>"
                                                <?= $selectedClass === $cls ? 'checked' : '' ?>
                                                <?= $selectedStatus === 'Siswa' ? 'required' : '' ?>
                                            >
                                            <span>Kelas <?= esc($cls) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <span class="qst-hint" style="margin-top:6px; display:block;">Pilih tingkatan kelas Anda saat ini (SMA / SMK / MA sederajat).</span>
                            </div>
                        </div>

                        <?php if ($questions !== []): ?>
                            <div class="qst-section-head" style="margin-top: 28px;">
                                <h2>2. Pertanyaan Kuesioner Kegiatan</h2>
                                <span class="qst-section-badge"><?= count($questions) ?> Pertanyaan</span>
                            </div>

                            <?php foreach ($questions as $idx => $q): ?>
                                <?php
                                $qId = (string) ($q['id'] ?? ('q_' . ($idx + 1)));
                                $qLabel = (string) ($q['label'] ?? ('Pertanyaan ' . ($idx + 1)));
                                $qType = strtolower((string) ($q['type'] ?? 'text'));
                                $qReq = ! empty($q['required']);
                                $qPlaceholder = (string) ($q['placeholder'] ?? 'Ketik jawaban Anda...');
                                $qOptions = is_array($q['options'] ?? null) ? $q['options'] : [];
                                $oldVal = $oldAnswers[$qId] ?? '';
                                ?>
                                <div class="qst-field">
                                    <label class="qst-label">
                                        <?= ($idx + 1) ?>. <?= esc($qLabel) ?>
                                        <?php if ($qReq): ?><span class="qst-req">*</span><?php endif; ?>
                                    </label>

                                    <?php if ($qType === 'textarea'): ?>
                                        <textarea
                                            class="qst-textarea"
                                            name="custom_answers[<?= esc($qId) ?>]"
                                            rows="3"
                                            placeholder="<?= esc($qPlaceholder ?: 'Tulis jawaban Anda...') ?>"
                                            <?= $qReq ? 'required' : '' ?>
                                        ><?= esc(is_string($oldVal) ? $oldVal : '') ?></textarea>

                                    <?php elseif ($qType === 'select'): ?>
                                        <select
                                            class="qst-select"
                                            name="custom_answers[<?= esc($qId) ?>]"
                                            <?= $qReq ? 'required' : '' ?>
                                        >
                                            <option value=""><?= esc($qPlaceholder ?: '-- Pilih salah satu jawaban --') ?></option>
                                            <?php foreach ($qOptions as $opt): ?>
                                                <option value="<?= esc((string) $opt) ?>" <?= (is_string($oldVal) && $oldVal === (string) $opt) ? 'selected' : '' ?>>
                                                    <?= esc((string) $opt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php elseif ($qType === 'radio'): ?>
                                        <div class="qst-options-group">
                                            <?php foreach ($qOptions as $optIdx => $opt): ?>
                                                <label class="qst-option-item">
                                                    <input
                                                        type="radio"
                                                        name="custom_answers[<?= esc($qId) ?>]"
                                                        value="<?= esc((string) $opt) ?>"
                                                        <?= ($qReq && $optIdx === 0) ? 'required' : '' ?>
                                                        <?= (is_string($oldVal) && $oldVal === (string) $opt) ? 'checked' : '' ?>
                                                    >
                                                    <span><?= esc((string) $opt) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                    <?php elseif ($qType === 'checkbox'): ?>
                                        <?php $oldArr = is_array($oldVal) ? $oldVal : []; ?>
                                        <div class="qst-options-group">
                                            <?php foreach ($qOptions as $opt): ?>
                                                <label class="qst-option-item">
                                                    <input
                                                        type="checkbox"
                                                        name="custom_answers[<?= esc($qId) ?>][]"
                                                        value="<?= esc((string) $opt) ?>"
                                                        <?= in_array((string) $opt, $oldArr, true) ? 'checked' : '' ?>
                                                    >
                                                    <span><?= esc((string) $opt) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                    <?php else: ?>
                                        <input
                                            class="qst-input"
                                            type="text"
                                            name="custom_answers[<?= esc($qId) ?>]"
                                            value="<?= esc(is_string($oldVal) ? $oldVal : '') ?>"
                                            placeholder="<?= esc($qPlaceholder ?: 'Ketik jawaban Anda...') ?>"
                                            <?= $qReq ? 'required' : '' ?>
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <button type="submit" class="qst-submit-btn">
                            Kirim Jawaban Kuesioner →
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="qst-footer">
            © <?= date('Y') ?> EventHub Integrated Scheduler & CRM • <?= esc($event['title']) ?>
        </div>
    </div>

    <script>
        function updateParticipantStatusUI(status) {
            const classWrapper = document.getElementById('student-class-wrapper');
            const classInputs = classWrapper ? classWrapper.querySelectorAll('input[name="student_class"]') : [];

            if (status === 'Siswa') {
                if (classWrapper) classWrapper.style.display = 'block';
                classInputs.forEach(i => { i.required = true; });
            } else {
                if (classWrapper) classWrapper.style.display = 'none';
                classInputs.forEach(i => {
                    i.required = false;
                    i.checked = false;
                    i.closest('.qst-pill-option')?.classList.remove('active');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.qst-pill-option input').forEach(input => {
                input.addEventListener('change', function () {
                    const groupName = this.name;
                    document.querySelectorAll(`input[name="${groupName}"]`).forEach(other => {
                        other.closest('.qst-pill-option')?.classList.toggle('active', other.checked);
                    });
                });
            });

            const checkedStatus = document.querySelector('input[name="participant_status"]:checked');
            if (checkedStatus) {
                updateParticipantStatusUI(checkedStatus.value);
            }
        });
    </script>
</body>
</html>
