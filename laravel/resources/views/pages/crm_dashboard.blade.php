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
$quickFilterUrl = static function (array $overrides) use ($filters): string {
    $merged = array_merge($filters, $overrides);
    $merged = array_filter($merged, static fn ($v): bool => (string) $v !== '');
    return site_url('crm' . ($merged !== [] ? '?' . http_build_query($merged) : ''));
};
$crmLabels = [
    ['name' => 'Closing', 'class' => 'crm-label-closing', 'stat' => 'closing'],
    ['name' => 'Minat/Tertarik', 'class' => 'crm-label-green', 'stat' => 'interested'],
    ['name' => 'Ragu-ragu', 'class' => 'crm-label-yellow', 'stat' => 'hesitant'],
    ['name' => 'Menolak', 'class' => 'crm-label-red', 'stat' => 'rejected'],
    ['name' => 'Tidak Merespon', 'class' => 'crm-label-gray', 'stat' => 'no_response'],
];
$warmTotal = (int) ($stats['interested'] ?? 0) + (int) ($stats['hesitant'] ?? 0);
$convPct = (int) ($stats['total'] ?? 0) > 0
    ? round(((int) ($stats['closing'] ?? 0) / (int) $stats['total']) * 100, 1)
    : 0.0;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<x-head :title="$title ?? 'CRM Workspace'" />
<body class="crm-page dark:bg-neutral-800 bg-neutral-100 dark:text-white">
    <style>
        /* Upgraded WhatsApp & AI CRM Workspace Styles */
        .crm-ai-copilot-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #064e3b 100%);
            border-radius: 16px;
            padding: 18px 22px;
            color: #f8fafc;
            margin-bottom: 18px;
            box-shadow: 0 12px 28px -8px rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .crm-ai-copilot-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .crm-ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(52, 211, 153, 0.45);
            color: #6ee7b7;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .crm-ai-copilot-title {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            margin: 6px 0 3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .crm-ai-copilot-sub {
            font-size: 13px;
            color: #cbd5e1;
            margin: 0;
            line-height: 1.5;
        }
        .crm-ai-copilot-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .crm-ai-prompt-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff;
            border-radius: 10px;
            padding: 8px 13px;
            font-size: 12.5px;
            min-width: 250px;
            outline: none;
        }
        .crm-ai-prompt-input::placeholder {
            color: #94a3b8;
        }
        .crm-ai-prompt-input:focus {
            border-color: #34d399;
            background: rgba(255, 255, 255, 0.15);
        }
        .crm-ai-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .crm-ai-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
        }
        .crm-ai-insight-panel {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 10px;
        }
        .crm-ai-priority-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 10px 13px;
            font-size: 12px;
            color: #e2e8f0;
            line-height: 1.45;
        }
        .crm-quick-pills {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
        .crm-quick-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            transition: all 0.15s ease;
        }
        .crm-quick-pill:hover, .crm-quick-pill.is-active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }
        .crm-wa-action-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 150px;
        }
        .crm-wa-workspace-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 3px 8px rgba(37, 211, 102, 0.25);
            transition: all 0.15s ease;
        }
        .crm-wa-workspace-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(18, 140, 126, 0.35);
        }
        .crm-wa-direct-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 11px;
            color: #0f766e;
            text-decoration: none;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }
        .crm-wa-direct-link:hover {
            background: #dcfce7;
        }
        .crm-proof-cell {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 135px;
        }
        .crm-proof-thumb-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .crm-proof-thumb {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #10b981;
            cursor: pointer;
            transition: transform 0.15s ease;
            background: #f1f5f9;
        }
        .crm-proof-thumb:hover {
            transform: scale(1.08);
        }
        .crm-proof-capture-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: #1e40af;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 7px;
            padding: 4px 8px;
            cursor: pointer;
        }
        .crm-proof-capture-btn:hover {
            background: #dbeafe;
        }

        /* Slide-over In-App WhatsApp & AI CRM Workspace Modal */
        .crm-wa-drawer {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: stretch;
            justify-content: flex-end;
            background: rgba(15, 23, 42, 0.62);
            backdrop-filter: blur(3px);
        }
        .crm-wa-drawer[hidden] {
            display: none !important;
        }
        .crm-wa-drawer-panel {
            width: min(1160px, 96vw);
            height: 100vh;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            box-shadow: -16px 0 45px rgba(15, 23, 42, 0.35);
            overflow: hidden;
        }
        .crm-wa-drawer-header {
            background: #075e54;
            color: #ffffff;
            padding: 13px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .crm-wa-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .crm-wa-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #25d366;
            color: #052e16;
            font-weight: 800;
            font-size: 17px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .crm-wa-user-meta h3 {
            margin: 0;
            font-size: 15.5px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .crm-wa-user-meta p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #d1fae5;
        }
        .crm-wa-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .crm-wa-close-btn {
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
        }
        .crm-wa-close-btn:hover {
            background: rgba(255,255,255,0.25);
        }
        .crm-wa-body {
            flex: 1;
            display: grid;
            grid-template-columns: 1.12fr 0.88fr;
            overflow: hidden;
        }
        @media (max-width: 960px) {
            .crm-wa-body {
                grid-template-columns: 1fr;
                overflow-y: auto;
            }
        }
        /* Left Column: WhatsApp Chat Room */
        .crm-wa-chat-col {
            display: flex;
            flex-direction: column;
            background: #efeae2;
            border-right: 1px solid #cbd5e1;
            overflow: hidden;
        }
        .crm-wa-chat-subbar {
            background: #f0f2f5;
            padding: 8px 14px;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            color: #334155;
        }
        .crm-wa-messages-stream {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-image: radial-gradient(rgba(0,0,0,0.04) 1px, transparent 0);
            background-size: 18px 18px;
        }
        .crm-wa-date-chip {
            align-self: center;
            background: #e2f3fb;
            color: #0f172a;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }
        .crm-wa-bubble {
            max-width: 82%;
            padding: 9px 12px 7px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.45;
            position: relative;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .crm-wa-bubble.is-agent {
            align-self: flex-end;
            background: #d9fdd3;
            color: #111b21;
            border-top-right-radius: 2px;
        }
        .crm-wa-bubble.is-student {
            align-self: flex-start;
            background: #ffffff;
            color: #111b21;
            border-top-left-radius: 2px;
        }
        .crm-wa-bubble-sender {
            font-size: 10.5px;
            font-weight: 700;
            margin-bottom: 3px;
            color: #075e54;
        }
        .crm-wa-bubble.is-student .crm-wa-bubble-sender {
            color: #2563eb;
        }
        .crm-wa-bubble-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }
        .crm-wa-composer {
            background: #f0f2f5;
            padding: 12px 14px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .crm-wa-mode-tabs {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }
        .crm-wa-sender-switch {
            display: inline-flex;
            background: #e2e8f0;
            padding: 3px;
            border-radius: 8px;
            gap: 3px;
        }
        .crm-wa-sender-switch button {
            border: none;
            background: transparent;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            cursor: pointer;
        }
        .crm-wa-sender-switch button.is-active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .crm-wa-input-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        .crm-wa-textarea {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            resize: vertical;
            min-height: 62px;
            max-height: 140px;
            background: #ffffff;
            color: #0f172a;
        }
        .crm-wa-send-btns {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .crm-wa-btn-send-wa {
            background: #128c7e;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }
        .crm-wa-btn-send-wa:hover {
            background: #075e54;
        }
        .crm-wa-btn-log-only {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .crm-wa-capture-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
        }
        .crm-wa-capture-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
        }
        .crm-wa-capture-btn:hover {
            filter: brightness(1.07);
        }
        .crm-wa-secondary-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Right Column: AI Assistant & CRM Status Updater */
        .crm-wa-ai-col {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            overflow-y: auto;
            padding: 16px;
            gap: 14px;
        }
        .crm-wa-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 14px;
        }
        .crm-wa-card-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .crm-ai-intent-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        .crm-ai-intent-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 8px;
            font-size: 11.5px;
            font-weight: 600;
            color: #1e293b;
            text-align: left;
            cursor: pointer;
            transition: all 0.14s ease;
        }
        .crm-ai-intent-btn:hover, .crm-ai-intent-btn.is-active {
            background: #ecfdf5;
            border-color: #10b981;
            color: #065f46;
        }
        .crm-ai-variants-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
        }
        .crm-ai-variant-item {
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 9px;
            padding: 9px 11px;
            font-size: 12px;
        }
        .crm-ai-variant-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
            font-weight: 700;
            color: #1e40af;
        }
        .crm-ai-variant-text {
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.4;
            max-height: 95px;
            overflow-y: auto;
            font-size: 11.5px;
        }
        .crm-ai-use-btn {
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .crm-image-preview-box {
            border: 2px dashed #94a3b8;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            background: #ffffff;
        }
        .crm-image-preview-box img {
            max-width: 100%;
            max-height: 190px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            margin-top: 6px;
        }
        .crm-saved-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }
        .crm-saved-gallery-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px;
            background: #ffffff;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        .crm-saved-gallery-item img {
            width: 100%;
            height: 68px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>

    <header class="crm-topbar">
        <a class="crm-brand" href="<?= site_url('crm') ?>">
            <img class="crm-brand-logo" src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub">
            CRM WhatsApp & AI Workspace
        </a>
        <nav class="crm-nav" aria-label="Navigasi CRM">
            <span><?= esc($currentUser['name'] ?? 'Agent') ?></span>
            <a href="<?= site_url('laporan-crm') ?>">Laporan CRM</a>
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

        <!-- AI CRM Copilot & Smart Prioritizer Banner -->
        <section class="crm-ai-copilot-banner" aria-label="AI CRM Copilot">
            <div class="crm-ai-copilot-top">
                <div>
                    <span class="crm-ai-badge">✨ Gemini AI CRM Copilot & WhatsApp Workspace Aktif</span>
                    <h2 class="crm-ai-copilot-title" id="crm-ai-headline">
                        Total <?= esc((string) ($stats['total'] ?? 0)) ?> Leads (Konversi Closing <?= esc((string) $convPct) ?>%) —
                        <?= esc((string) $warmTotal) ?> Prospek Hangat Siap Follow-Up WhatsApp Hari Ini
                    </h2>
                    <p class="crm-ai-copilot-sub" id="crm-ai-subtext">
                        Klik tombol <strong>"💬 Chat WA & AI"</strong> pada baris siswa untuk langsung chat dari aplikasi, membuat script WA otomatis dengan AI, menganalisis balasan siswa, dan menyimpan tangkapan gambar bukti chat ke CRM dalam 1 klik.
                    </p>
                </div>
                <div class="crm-ai-copilot-controls">
                    <input
                        type="text"
                        id="crm-ai-workspace-question"
                        class="crm-ai-prompt-input"
                        placeholder="Tanya AI (cth: Strategi atasi siswa ragu biaya)..."
                    >
                    <button type="button" class="crm-ai-btn" id="crm-ai-workspace-btn">
                        ✨ Analisis Strategi AI
                    </button>
                </div>
            </div>
            <div class="crm-ai-insight-panel" id="crm-ai-insight-cards">
                <div class="crm-ai-priority-card">
                    <strong>🔥 Quick Wins (Prospek Hangat):</strong> Terdapat <strong><?= esc((string) ($stats['interested'] ?? 0)) ?> Minat/Tertarik</strong> & <strong><?= esc((string) ($stats['hesitant'] ?? 0)) ?> Ragu-ragu</strong>. Gunakan generator pesan AI "Info Beasiswa & Promo" di ruang chat WA.
                </div>
                <div class="crm-ai-priority-card">
                    <strong>⚡ Eksekusi Tahap 1 (Fresh Leads):</strong> Terdapat <strong><?= esc((string) ($stats['stage1'] ?? 0)) ?> data Fresh</strong> dan <strong><?= esc((string) ($stats['no_response'] ?? 0)) ?> Tidak Merespon</strong>. Lakukan sapaan personal menyebutkan nama sekolah & event.
                </div>
                <div class="crm-ai-priority-card">
                    <strong>📸 Bukti Gambar Otomatis:</strong> Setiap selesai chat di Workspace, klik <strong>"Simpan Chat Jadi Gambar Bukti CRM"</strong> atau tekan <strong>Ctrl+V</strong> untuk langsung menyimpan screenshot ke kolom Bukti Follow Up.
                </div>
            </div>
        </section>

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

        <!-- Quick Filter Pills for Fast Workflow -->
        <nav class="crm-quick-pills" aria-label="Filter Cepat CRM">
            <a class="crm-quick-pill <?= ($filters['stage'] ?? '') === '' && ($filters['label'] ?? '') === '' && ($filters['unassigned'] ?? '') === '' && ($filters['prospect_priority'] ?? '') === '' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => '', 'label' => '', 'unassigned' => '', 'prospect_priority' => ''])) ?>">
                📋 Semua Data (<?= esc((string) ($stats['total'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['prospect_priority'] ?? '') === 'priority' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['prospect_priority' => 'priority', 'stage' => '', 'label' => '', 'unassigned' => ''])) ?>" style="<?= ($filters['prospect_priority'] ?? '') === 'priority' ? 'background:#d97706; color:#ffffff; border-color:#b45309;' : 'background:#fffbeb; color:#b45309; border-color:#f59e0b;' ?> font-weight:800;">
                ⭐ Prospek Prioritas (Chat di Awal) (<?= esc((string) ($stats['prospect_priority'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['prospect_priority'] ?? '') === 'nurture' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['prospect_priority' => 'nurture', 'stage' => '', 'label' => '', 'unassigned' => ''])) ?>" style="<?= ($filters['prospect_priority'] ?? '') === 'nurture' ? 'background:#475569; color:#ffffff; border-color:#334155;' : 'background:#f8fafc; color:#475569; border-color:#cbd5e1;' ?> font-weight:700;">
                🕒 Follow-up Lanjutan (Setelah Prospek) (<?= esc((string) ($stats['prospect_nurture'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['stage'] ?? '') === 'Tahap 1' && ($filters['prospect_priority'] ?? '') === '' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => 'Tahap 1', 'label' => '', 'unassigned' => '', 'prospect_priority' => ''])) ?>">
                ⚡ Fresh Tahap 1 (<?= esc((string) ($stats['stage1'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['label'] ?? '') === 'Minat/Tertarik' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => '', 'label' => 'Minat/Tertarik', 'unassigned' => '', 'prospect_priority' => ''])) ?>">
                🔥 Minat/Tertarik (<?= esc((string) ($stats['interested'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['label'] ?? '') === 'Ragu-ragu' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => '', 'label' => 'Ragu-ragu', 'unassigned' => '', 'prospect_priority' => ''])) ?>">
                🤔 Ragu-ragu (<?= esc((string) ($stats['hesitant'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['label'] ?? '') === 'Tidak Merespon' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => '', 'label' => 'Tidak Merespon', 'unassigned' => '', 'prospect_priority' => ''])) ?>">
                🔕 Tidak Merespon (<?= esc((string) ($stats['no_response'] ?? 0)) ?>)
            </a>
            <a class="crm-quick-pill <?= ($filters['label'] ?? '') === 'Closing' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => '', 'label' => 'Closing', 'unassigned' => '', 'prospect_priority' => ''])) ?>">
                ✅ Closing (<?= esc((string) ($stats['closing'] ?? 0)) ?>)
            </a>
            <?php if ($isAdmin): ?>
                <a class="crm-quick-pill <?= ($filters['unassigned'] ?? '') === '1' ? 'is-active' : '' ?>" href="<?= esc($quickFilterUrl(['stage' => '', 'label' => '', 'unassigned' => '1', 'prospect_priority' => ''])) ?>">
                    ⏳ Belum Assign (<?= esc((string) ($stats['unassigned'] ?? 0)) ?>)
                </a>
            <?php endif; ?>
        </nav>

        <div class="crm-main-grid">
            <section class="crm-board">
                <div class="crm-board-header">
                    <h2>Data Siswa CRM & WhatsApp Workspace</h2>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <a href="<?= site_url('crm/export-excel?' . http_build_query($leadPagination['query'] ?? [])) ?>" class="primary-button compact-button" style="background:linear-gradient(135deg, #10b981, #059669); border-color:#059669; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                            📥 Download Excel CRM
                        </a>
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
                    <select class="control" name="prospect_priority">
                        <option value="">Semua Prioritas Prospek</option>
                        <option value="priority" <?= ($filters['prospect_priority'] ?? '') === 'priority' ? 'selected' : '' ?>>⭐ Prospek Prioritas (Chat di Awal)</option>
                        <option value="nurture" <?= ($filters['prospect_priority'] ?? '') === 'nurture' ? 'selected' : '' ?>>🕒 Follow-up Lanjutan (Setelah Prospek)</option>
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
                        <a class="ghost-button compact-button" href="<?= site_url('crm/export-excel?' . http_build_query($leadPagination['query'] ?? [])) ?>" style="background:#ecfdf5; color:#065f46; border-color:#a7f3d0; font-weight:700;" title="Unduh data Excel sesuai filter yang aktif">
                            📥 Download Excel
                        </a>
                    </div>
                </form>

                <div class="report-table-summary crm-table-summary">
                    <span><?= esc((string) $leadPagination['from']) ?>-<?= esc((string) $leadPagination['to']) ?> dari <?= esc((string) $leadPagination['total']) ?> data siswa CRM</span>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <a href="<?= site_url('crm/export-excel?' . http_build_query($leadPagination['query'] ?? [])) ?>" style="color:#059669; font-weight:700; text-decoration:none; font-size:12px; display:inline-flex; align-items:center; gap:4px;">
                            📥 Unduh Excel (<?= esc((string) $leadPagination['total']) ?> Data)
                        </a>
                        <span>Klik <strong>"💬 Chat WA & AI"</strong> untuk chat langsung & simpan bukti</span>
                    </div>
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
                                <th>WhatsApp & AI Chat</th>
                                <th>Event</th>
                                <th>Kampus</th>
                                <th>Tahap</th>
                                <th>Label</th>
                                <th>Alasan/Keterangan</th>
                                <th>Agent</th>
                                <th>Bukti Follow Up (Gambar)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <?php
                                    $leadFormId = 'crm-lead-' . $lead['id'];
                                    $rowLogs = $leadLogsByLeadId[(int) $lead['id']] ?? [];
                                    $latestImageLog = null;
                                    foreach ($rowLogs as $rLog) {
                                        if (! empty($rLog['follow_up_image'])) {
                                            $latestImageLog = $rLog;
                                            break;
                                        }
                                    }
                                ?>
                                <tr id="crm-lead-row-<?= esc((string) $lead['id'], 'attr') ?>">
                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <input form="crm-assign-form" type="checkbox" name="lead_ids[]" value="<?= esc($lead['id']) ?>" data-crm-row-check aria-label="Pilih <?= esc($lead['student_name'], 'attr') ?>">
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <strong><?= esc($lead['student_name']) ?></strong>
                                        <?php if (! empty($lead['email'])): ?>
                                            <div style="font-size:11px; color:#64748b; margin-top:2px;">✉ <?= esc($lead['email']) ?></div>
                                        <?php endif; ?>
                                        <?php
                                            $isLeadPriority = ($lead['prospect_status'] ?? '') === 'priority'
                                                || str_contains((string) ($lead['notes'] ?? ''), 'PROSPEK PRIORITAS');
                                            $isLeadNurture = ($lead['prospect_status'] ?? '') === 'nurture'
                                                || str_contains((string) ($lead['notes'] ?? ''), 'FOLLOW UP LANJUTAN');
                                        ?>
                                        <?php if ($isLeadPriority): ?>
                                            <div style="margin-top:4px;">
                                                <span style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:999px; background:#fef3c7; border:1px solid #f59e0b; color:#92400e; font-size:10.5px; font-weight:800; white-space:nowrap;">
                                                    ⭐ Prospek Prioritas (Chat Diawal)
                                                </span>
                                            </div>
                                        <?php elseif ($isLeadNurture): ?>
                                            <div style="margin-top:4px;">
                                                <span style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:999px; background:#f1f5f9; border:1px solid #cbd5e1; color:#475569; font-size:10.5px; font-weight:700; white-space:nowrap;">
                                                    🕒 Follow-up Lanjutan (Setelah Prospek)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= esc($lead['school_origin'] ?: '-') ?></div>
                                        <?php if (! empty($lead['participant_status'])): ?>
                                            <div style="margin-top:3px;">
                                                <span style="display:inline-block; padding:1px 6px; border-radius:6px; background:#eff6ff; color:#1d4ed8; font-size:10px; font-weight:700; border:1px solid #bfdbfe;">
                                                    <?= esc($lead['participant_status']) ?><?= ($lead['participant_status'] === 'Siswa' && ! empty($lead['student_class'])) ? ' (Kelas ' . esc($lead['student_class']) . ')' : '' ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php
                                        $rawWhatsapp = preg_replace('/\D+/', '', (string) ($lead['whatsapp'] ?? '')) ?? '';
                                        $whatsappLinkNumber = str_starts_with($rawWhatsapp, '0')
                                            ? '62' . substr($rawWhatsapp, 1)
                                            : (str_starts_with($rawWhatsapp, '8') ? '62' . $rawWhatsapp : $rawWhatsapp);
                                    ?>
                                    <td>
                                        <div class="crm-wa-action-group">
                                            <button
                                                type="button"
                                                class="crm-wa-workspace-btn"
                                                data-open-wa-workspace="<?= esc((string) $lead['id'], 'attr') ?>"
                                                title="Buka Ruang Chat WhatsApp & AI Assistant di Aplikasi"
                                            >
                                                <span>💬 Chat WA & AI</span>
                                            </button>
                                            <?php if ($whatsappLinkNumber !== ''): ?>
                                                <a
                                                    class="crm-wa-direct-link"
                                                    href="https://wa.me/<?= esc($whatsappLinkNumber) ?>"
                                                    target="_blank"
                                                    rel="noopener"
                                                    title="Buka WhatsApp Web/App Eksternal"
                                                >
                                                    <span>📱 <?= esc($lead['whatsapp']) ?> ↗</span>
                                                </a>
                                            <?php else: ?>
                                                <small style="color:#94a3b8;text-align:center;">No WA belum diisi</small>
                                            <?php endif; ?>
                                        </div>
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
                                        <select class="crm-inline-select crm-stage-select" id="crm-row-stage-<?= esc((string) $lead['id'], 'attr') ?>" form="<?= esc($leadFormId) ?>" name="stage" data-crm-stage-select data-crm-lead-form="<?= esc($leadFormId, 'attr') ?>">
                                            <?php foreach (['Tahap 1', 'Tahap 2', 'Tahap 3'] as $stage): ?>
                                                <option <?= ($lead['stage'] ?? '') === $stage ? 'selected' : '' ?>><?= esc($stage) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="crm-inline-select" id="crm-row-label-<?= esc((string) $lead['id'], 'attr') ?>" form="<?= esc($leadFormId) ?>" name="label" data-crm-label-select data-crm-lead-form="<?= esc($leadFormId, 'attr') ?>">
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
                                            id="crm-row-reason-<?= esc((string) $lead['id'], 'attr') ?>"
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
                                        <div class="crm-proof-cell" id="crm-proof-cell-<?= esc((string) $lead['id'], 'attr') ?>">
                                            <div class="crm-proof-thumb-row" id="crm-proof-thumbs-<?= esc((string) $lead['id'], 'attr') ?>">
                                                <?php if ($latestImageLog !== null): ?>
                                                    <a href="<?= site_url('crm/logs/' . $latestImageLog['id'] . '/image') ?>" target="_blank" rel="noopener" title="Klik untuk melihat gambar bukti chat penuh">
                                                        <img class="crm-proof-thumb" src="<?= site_url('crm/logs/' . $latestImageLog['id'] . '/image') ?>" alt="Bukti Chat">
                                                    </a>
                                                    <span style="font-size:11px;color:#059669;font-weight:700;">✔ Tersimpan</span>
                                                <?php else: ?>
                                                    <span style="font-size:11px;color:#94a3b8;">Belum ada bukti</span>
                                                <?php endif; ?>
                                            </div>
                                            <button
                                                type="button"
                                                class="crm-proof-capture-btn"
                                                data-open-wa-workspace="<?= esc((string) $lead['id'], 'attr') ?>"
                                                data-focus-capture="1"
                                            >
                                                📸 Simpan Gambar Chat
                                            </button>
                                            <label class="crm-follow-up-upload" style="margin-top:2px;">
                                                <span>Upload File JPG/PNG</span>
                                                <input
                                                    form="<?= esc($leadFormId, 'attr') ?>"
                                                    type="file"
                                                    name="follow_up_image"
                                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                    aria-label="Upload bukti follow-up <?= esc($lead['student_name'], 'attr') ?>"
                                                >
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <form id="<?= esc($leadFormId) ?>" action="<?= site_url('crm/leads/' . $lead['id'] . '/update') ?>" method="post" enctype="multipart/form-data" class="crm-row-action">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="notes" id="crm-row-notes-<?= esc((string) $lead['id'], 'attr') ?>" value="<?= esc($lead['notes'] ?? '', 'attr') ?>">
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
                                        <details class="crm-row-log-details" id="crm-row-details-<?= esc((string) $lead['id'], 'attr') ?>">
                                            <summary>Riwayat follow-up & bukti chat baris ini (<span id="crm-row-log-count-<?= esc((string) $lead['id'], 'attr') ?>"><?= esc((string) count($rowLogs)) ?></span> terbaru)</summary>
                                            <div class="crm-row-log-list" id="crm-row-log-list-<?= esc((string) $lead['id'], 'attr') ?>">
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

    <!-- In-App WhatsApp & AI CRM Workspace Slide-Over Drawer -->
    <div class="crm-wa-drawer" id="crm-wa-drawer" hidden>
        <div class="crm-wa-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="crm-wa-student-name">
            <header class="crm-wa-drawer-header">
                <div class="crm-wa-user-info">
                    <div class="crm-wa-avatar" id="crm-wa-avatar">S</div>
                    <div class="crm-wa-user-meta">
                        <h3>
                            <span id="crm-wa-student-name">Nama Siswa</span>
                            <span id="crm-wa-stage-badge" style="background:rgba(255,255,255,0.2);font-size:11px;padding:2px 8px;border-radius:999px;">Tahap 1</span>
                            <span id="crm-wa-prospect-badge" style="display:none;font-size:11px;padding:2px 8px;border-radius:999px;font-weight:700;"></span>
                        </h3>
                        <p id="crm-wa-student-sub">Sekolah • No WA • Event • Kampus</p>
                    </div>
                </div>
                <div class="crm-wa-header-actions">
                    <a id="crm-wa-external-btn" href="#" target="_blank" rel="noopener" class="crm-wa-close-btn" style="text-decoration:none;background:#25d366;color:#052e16;border-color:#25d366;">
                        📱 Buka WhatsApp App/Web ↗
                    </a>
                    <button type="button" class="crm-wa-close-btn" id="crm-wa-close-btn">✕ Tutup Workspace</button>
                </div>
            </header>

            <div class="crm-wa-body">
                <!-- Left Column: Interactive WhatsApp Chat & Image Generator -->
                <section class="crm-wa-chat-col">
                    <div class="crm-wa-chat-subbar">
                        <span>💬 <strong>Ruang Chat WhatsApp CRM</strong> (Tersimpan di Aplikasi & Siap Jadi Bukti Gambar)</span>
                        <span id="crm-wa-status-toast" style="color:#059669;font-weight:700;"></span>
                    </div>

                    <div class="crm-wa-messages-stream" id="crm-wa-messages-stream">
                        <div class="crm-wa-date-chip">Percakapan Follow-Up PMB</div>
                    </div>

                    <div class="crm-wa-composer">
                        <div class="crm-wa-mode-tabs">
                            <div class="crm-wa-sender-switch" role="tablist">
                                <button type="button" class="is-active" data-wa-sender-mode="agent" id="wa-mode-agent-btn">
                                    📤 Kirim Pesan Agent (Keluar)
                                </button>
                                <button type="button" data-wa-sender-mode="student" id="wa-mode-student-btn">
                                    📥 Catat Balasan Siswa (Masuk)
                                </button>
                            </div>
                            <small style="color:#64748b;font-size:11px;">Tips: Tekan <strong>Ctrl+V</strong> untuk tempel screenshot</small>
                        </div>

                        <div class="crm-wa-input-row">
                            <textarea
                                id="crm-wa-message-input"
                                class="crm-wa-textarea"
                                placeholder="Ketik pesan WhatsApp untuk siswa atau klik tombol AI di sebelah kanan..."
                            ></textarea>
                            <div class="crm-wa-send-btns">
                                <button type="button" class="crm-wa-btn-send-wa" id="crm-wa-send-and-open-btn" title="Simpan ke riwayat chat aplikasi & buka WhatsApp siswa dengan pesan ini">
                                    🚀 Kirim ke WA & Simpan
                                </button>
                                <button type="button" class="crm-wa-btn-log-only" id="crm-wa-save-bubble-btn" title="Tambahkan ke gelembung chat di aplikasi tanpa membuka tab baru">
                                    ➕ Simpan di Bubble Chat
                                </button>
                            </div>
                        </div>

                        <div class="crm-wa-capture-bar">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button type="button" class="crm-wa-capture-btn" id="crm-wa-render-save-btn">
                                    📸 Simpan Chat Jadi Gambar Bukti CRM
                                </button>
                                <button type="button" class="crm-wa-secondary-btn" id="crm-wa-download-png-btn">
                                    ⬇️ Download PNG Chat
                                </button>
                            </div>
                            <label class="crm-wa-secondary-btn" style="margin:0;">
                                📁 Upload / Pilih Screenshot
                                <input type="file" id="crm-wa-file-input" accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Right Column: AI CRM Assistant & Status/Proof Saver -->
                <section class="crm-wa-ai-col">
                    <!-- 1. AI Smart Script Generator -->
                    <div class="crm-wa-card">
                        <div class="crm-wa-card-title">
                            <span>🤖 AI Generator Pesan WhatsApp (Gemini AI)</span>
                            <small id="crm-ai-source-label" style="color:#059669;font-weight:700;font-size:11px;">Siap</small>
                        </div>
                        <div class="crm-ai-intent-grid">
                            <button type="button" class="crm-ai-intent-btn is-active" data-ai-intent="sapaan_awal">👋 Sapaan Awal Tahap 1</button>
                            <button type="button" class="crm-ai-intent-btn" data-ai-intent="follow_up_minat">🔥 Follow-Up Siswa Minat</button>
                            <button type="button" class="crm-ai-intent-btn" data-ai-intent="atasi_ragu">🤝 Atasi Siswa Ragu-ragu</button>
                            <button type="button" class="crm-ai-intent-btn" data-ai-intent="info_beasiswa">🎓 Info Beasiswa & Promo</button>
                            <button type="button" class="crm-ai-intent-btn" data-ai-intent="reengage">🔔 Sapa Ulang (Belum Respon)</button>
                            <button type="button" class="crm-ai-intent-btn" data-ai-intent="closing_daftar">🏆 Panduan Daftar / Closing</button>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <input
                                type="text"
                                id="crm-ai-custom-prompt"
                                class="control"
                                style="flex:1;font-size:12px;padding:6px 10px;"
                                placeholder="Instruksi tambahan ke AI (opsional, cth: tawarkan kelas sore)..."
                            >
                            <button type="button" class="crm-ai-btn" id="crm-ai-generate-msg-btn" style="padding:6px 12px;font-size:12px;">
                                ✨ Buat Pesan AI
                            </button>
                        </div>
                        <div id="crm-ai-strategy-tip" style="margin-top:7px;font-size:11.5px;color:#065f46;background:#ecfdf5;padding:6px 9px;border-radius:7px;border:1px solid #a7f3d0;">Tips AI: Pilih skenario di atas untuk menyusun pesan WhatsApp yang dipersonalisasi bagi siswa ini.</div>
                        <div class="crm-ai-variants-list" id="crm-ai-variants-list"></div>
                    </div>

                    <!-- 2. AI Reply Analyzer & Status Recommender -->
                    <div class="crm-wa-card">
                        <div class="crm-wa-card-title">
                            <span>🧠 AI Analisis Balasan & Rekomendasi Status</span>
                            <button type="button" class="crm-ai-use-btn" id="crm-ai-analyze-btn" style="background:#0f766e;padding:5px 10px;">
                                🔍 Analisis Percakapan
                            </button>
                        </div>
                        <div id="crm-ai-analysis-box" style="font-size:12px;color:#475569;">
                            Catat balasan siswa di ruang chat, lalu klik <strong>"🔍 Analisis Percakapan"</strong> agar AI merekomendasikan Tahap, Label, Alasan, dan Balasan Terbaik secara otomatis.
                        </div>
                    </div>

                    <!-- 3. Update Status CRM & Preview Gambar Bukti Chat -->
                    <div class="crm-wa-card" style="border-color:#93c5fd;background:#eff6ff;">
                        <div class="crm-wa-card-title">
                            <span>💾 Simpan Status CRM & Gambar Bukti Chat</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                            <label style="font-size:12px;font-weight:700;color:#1e293b;">
                                Tahap Follow-Up
                                <select id="crm-wa-form-stage" class="control" style="margin-top:3px;font-size:12.5px;">
                                    <option value="Tahap 1">Tahap 1</option>
                                    <option value="Tahap 2">Tahap 2</option>
                                    <option value="Tahap 3">Tahap 3</option>
                                </select>
                            </label>
                            <label style="font-size:12px;font-weight:700;color:#1e293b;">
                                Label Hasil
                                <select id="crm-wa-form-label" class="control" style="margin-top:3px;font-size:12.5px;">
                                    <option value="">- Pilih Label -</option>
                                    <option value="Closing">Closing</option>
                                    <option value="Minat/Tertarik">Minat/Tertarik</option>
                                    <option value="Ragu-ragu">Ragu-ragu</option>
                                    <option value="Menolak">Menolak</option>
                                    <option value="Tidak Merespon">Tidak Merespon</option>
                                </select>
                            </label>
                        </div>
                        <label style="font-size:12px;font-weight:700;color:#1e293b;display:block;margin-bottom:8px;">
                            Alasan / Keterangan Hasil Follow-Up
                            <textarea id="crm-wa-form-reason" class="control" rows="2" style="margin-top:3px;font-size:12px;" placeholder="Alasan minat, ragu-ragu, menolak, atau keterangan hasil chat..."></textarea>
                        </label>
                        <label style="font-size:12px;font-weight:700;color:#1e293b;display:block;margin-bottom:8px;">
                            Catatan Internal Agent
                            <input type="text" id="crm-wa-form-notes" class="control" style="margin-top:3px;font-size:12px;" placeholder="Catatan tambahan...">
                        </label>

                        <div class="crm-image-preview-box" id="crm-wa-image-preview-box">
                            <div style="font-size:11.5px;color:#475569;font-weight:600;" id="crm-wa-preview-caption">
                                Belum ada gambar dipilih. Klik <strong>"📸 Simpan Chat Jadi Gambar Bukti CRM"</strong> atau tekan <strong>Ctrl+V</strong> untuk menempel screenshot WA.
                            </div>
                            <img id="crm-wa-preview-img" src="" alt="Preview Bukti Chat" hidden>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:10px;">
                            <button type="button" class="crm-ai-btn" id="crm-wa-save-all-btn" style="flex:1;justify-content:center;padding:10px;">
                                ✅ Simpan Status & Bukti Gambar ke CRM
                            </button>
                        </div>

                        <!-- Galeri Bukti Tersimpan -->
                        <div style="margin-top:12px;">
                            <strong style="font-size:11.5px;color:#1e293b;">📂 Galeri Bukti Follow-Up Siswa Ini:</strong>
                            <div class="crm-saved-gallery" id="crm-wa-saved-gallery"></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

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

    <script>
    (function () {
        const BASE_CRM_URL = <?= json_encode(rtrim(site_url('crm'), '/')) ?>;
        const CSRF_TOKEN = <?= json_encode(function_exists('csrf_hash') ? csrf_hash() : csrf_token()) ?>;
        const CSRF_NAME = <?= json_encode('_token') ?>;
        const WORKSPACE_STATS = <?= json_encode($stats) ?>;

        let activeLead = null;
        let activeMessages = [];
        let activeSenderMode = 'agent';
        let activeIntent = 'sapaan_awal';
        let pendingBase64Image = '';

        const drawer = document.getElementById('crm-wa-drawer');
        const closeBtn = document.getElementById('crm-wa-close-btn');
        const avatarEl = document.getElementById('crm-wa-avatar');
        const studentNameEl = document.getElementById('crm-wa-student-name');
        const stageBadgeEl = document.getElementById('crm-wa-stage-badge');
        const prospectBadgeEl = document.getElementById('crm-wa-prospect-badge');
        const studentSubEl = document.getElementById('crm-wa-student-sub');
        const externalBtn = document.getElementById('crm-wa-external-btn');
        const streamEl = document.getElementById('crm-wa-messages-stream');
        const msgInput = document.getElementById('crm-wa-message-input');
        const sendAndOpenBtn = document.getElementById('crm-wa-send-and-open-btn');
        const saveBubbleBtn = document.getElementById('crm-wa-save-bubble-btn');
        const renderSaveBtn = document.getElementById('crm-wa-render-save-btn');
        const downloadPngBtn = document.getElementById('crm-wa-download-png-btn');
        const fileInput = document.getElementById('crm-wa-file-input');
        const statusToast = document.getElementById('crm-wa-status-toast');

        const aiSourceLabel = document.getElementById('crm-ai-source-label');
        const aiCustomPrompt = document.getElementById('crm-ai-custom-prompt');
        const aiGenerateMsgBtn = document.getElementById('crm-ai-generate-msg-btn');
        const aiStrategyTip = document.getElementById('crm-ai-strategy-tip');
        const aiVariantsList = document.getElementById('crm-ai-variants-list');
        const aiAnalyzeBtn = document.getElementById('crm-ai-analyze-btn');
        const aiAnalysisBox = document.getElementById('crm-ai-analysis-box');

        const formStage = document.getElementById('crm-wa-form-stage');
        const formLabel = document.getElementById('crm-wa-form-label');
        const formReason = document.getElementById('crm-wa-form-reason');
        const formNotes = document.getElementById('crm-wa-form-notes');
        const previewCaption = document.getElementById('crm-wa-preview-caption');
        const previewImg = document.getElementById('crm-wa-preview-img');
        const saveAllBtn = document.getElementById('crm-wa-save-all-btn');
        const savedGallery = document.getElementById('crm-wa-saved-gallery');

        function showToast(text, isError) {
            if (!statusToast) return;
            statusToast.textContent = text;
            statusToast.style.color = isError ? '#dc2626' : '#059669';
            setTimeout(() => {
                if (statusToast.textContent === text) statusToast.textContent = '';
            }, 4500);
        }

        function postForm(url, dataObj) {
            const formData = new FormData();
            formData.append(CSRF_NAME, CSRF_TOKEN);
            formData.append('_token', CSRF_TOKEN);
            Object.keys(dataObj).forEach(k => {
                if (dataObj[k] !== undefined && dataObj[k] !== null) {
                    formData.append(k, dataObj[k]);
                }
            });
            return fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json());
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : String(str);
            return div.innerHTML;
        }

        function renderChatStream() {
            streamEl.innerHTML = '<div class="crm-wa-date-chip">Percakapan Follow-Up PMB • ' + escapeHtml(activeLead ? activeLead.campus_name || 'Kampus' : '') + '</div>';
            if (!activeMessages || activeMessages.length === 0) {
                const empty = document.createElement('div');
                empty.style.cssText = 'text-align:center;color:#64748b;font-size:12px;margin:24px auto;max-width:340px;background:rgba(255,255,255,0.85);padding:12px 16px;border-radius:10px;';
                empty.innerHTML = 'Belum ada riwayat chat di aplikasi untuk <strong>' + escapeHtml(activeLead ? activeLead.student_name : '') + '</strong>.<br>Pilih pesan dari <strong>AI Generator</strong> di kanan atau ketik pesan di bawah untuk memulai.';
                streamEl.appendChild(empty);
                return;
            }

            activeMessages.forEach(msg => {
                const isStudent = msg.sender_type === 'student';
                const bubble = document.createElement('div');
                bubble.className = 'crm-wa-bubble ' + (isStudent ? 'is-student' : 'is-agent');
                const senderLabel = isStudent
                    ? ('📥 ' + (msg.sender_name || (activeLead ? activeLead.student_name : 'Siswa')))
                    : ('📤 ' + (msg.sender_name || 'Agent CRM'));
                const ticks = isStudent ? '' : '<span style="color:#0284c7;font-weight:800;">✓✓</span>';
                bubble.innerHTML =
                    '<div class="crm-wa-bubble-sender">' + escapeHtml(senderLabel) + '</div>' +
                    '<div>' + escapeHtml(msg.message) + '</div>' +
                    '<div class="crm-wa-bubble-meta"><span>' + escapeHtml(msg.formatted_time || msg.created_at || '') + '</span>' + ticks + '</div>';
                streamEl.appendChild(bubble);
            });
            streamEl.scrollTop = streamEl.scrollHeight;
        }

        function renderAiVariants(aiData) {
            if (!aiData) return;
            aiSourceLabel.textContent = aiData.ai_source || 'AI Ready';
            if (aiData.strategy_tip) {
                var cleanTip = (aiData.strategy_tip || '').replace(/^[^a-zA-Z0-9]+/, '').trim();
                aiStrategyTip.textContent = 'Tips AI: ' + cleanTip;
            }
            aiVariantsList.innerHTML = '';
            const variants = aiData.variants || [];
            variants.forEach((v, idx) => {
                const card = document.createElement('div');
                card.className = 'crm-ai-variant-item';
                const cleanText = (v.text || '').replace(/\*+([^*]+)\*+/g, '').replace(/\*/g, '');
                card.innerHTML =
                    '<div class="crm-ai-variant-head">' +
                        '<span>' + escapeHtml(v.title || ('Variasi ' + (idx + 1))) + '</span>' +
                        '<div style="display:flex;gap:5px;">' +
                            '<button type="button" class="crm-ai-use-btn" data-use-variant="' + idx + '">Gunakan ke Chat</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="crm-ai-variant-text">' + escapeHtml(cleanText) + '</div>';
                card.querySelector('[data-use-variant]').addEventListener('click', () => {
                    setSenderMode('agent');
                    msgInput.value = cleanText;
                    msgInput.focus();
                    showToast('Script AI dimasukkan ke kotak chat WA!');
                });
                aiVariantsList.appendChild(card);
            });

            if (aiData.primary_message && msgInput.value.trim() === '') {
                msgInput.value = (aiData.primary_message || '').replace(/\*+([^*]+)\*+/g, '').replace(/\*/g, '');
            }
        }

        function renderSavedGallery(logs) {
            savedGallery.innerHTML = '';
            const withImages = (logs || []).filter(l => l.image_url);
            if (withImages.length === 0) {
                savedGallery.innerHTML = '<small style="color:#64748b;grid-column:1/-1;">Belum ada gambar tersimpan.</small>';
                return;
            }
            withImages.forEach(log => {
                const item = document.createElement('div');
                item.className = 'crm-saved-gallery-item';
                item.innerHTML =
                    '<a href="' + escapeHtml(log.image_url) + '" target="_blank" rel="noopener">' +
                        '<img src="' + escapeHtml(log.image_url) + '" alt="Bukti Chat">' +
                    '</a>' +
                    '<div style="margin-top:3px;">' + escapeHtml(log.formatted_time || '') + '</div>';
                savedGallery.appendChild(item);
            });
        }

        function updateTableRowDom(leadId, leadData, logs) {
            const stageSel = document.getElementById('crm-row-stage-' + leadId);
            const labelSel = document.getElementById('crm-row-label-' + leadId);
            const reasonTxt = document.getElementById('crm-row-reason-' + leadId);
            const notesInp = document.getElementById('crm-row-notes-' + leadId);
            if (stageSel && leadData.stage) stageSel.value = leadData.stage;
            if (labelSel && leadData.label !== undefined) labelSel.value = leadData.label || '';
            if (reasonTxt) {
                const active = ['Tahap 2', 'Tahap 3'].includes(leadData.stage) && ['Minat/Tertarik', 'Ragu-ragu', 'Menolak'].includes(leadData.label);
                reasonTxt.disabled = !active;
                reasonTxt.value = leadData.rejection_reason || '';
            }
            if (notesInp && leadData.notes !== undefined) notesInp.value = leadData.notes || '';

            if (logs && logs.length > 0) {
                const thumbsBox = document.getElementById('crm-proof-thumbs-' + leadId);
                const latestWithImg = logs.find(l => l.image_url);
                if (thumbsBox && latestWithImg) {
                    thumbsBox.innerHTML =
                        '<a href="' + escapeHtml(latestWithImg.image_url) + '" target="_blank" rel="noopener" title="Klik untuk melihat gambar bukti chat penuh">' +
                            '<img class="crm-proof-thumb" src="' + escapeHtml(latestWithImg.image_url) + '" alt="Bukti Chat">' +
                        '</a>' +
                        '<span style="font-size:11px;color:#059669;font-weight:700;">✔ Tersimpan</span>';
                }
                const countEl = document.getElementById('crm-row-log-count-' + leadId);
                if (countEl) countEl.textContent = String(logs.length);
                const logListEl = document.getElementById('crm-row-log-list-' + leadId);
                if (logListEl) {
                    logListEl.innerHTML = logs.map(log =>
                        '<article class="crm-row-log-item">' +
                            '<div>' +
                                '<strong>' + escapeHtml(log.summary || 'Data CRM diperbarui.') + '</strong>' +
                                (log.new_rejection_reason ? '<small>Alasan: ' + escapeHtml(log.new_rejection_reason) + '</small>' : '') +
                                '<small>' + escapeHtml(log.actor_name || 'User CRM') + ' - ' + escapeHtml(log.formatted_time || '') + '</small>' +
                            '</div>' +
                            (log.image_url
                                ? '<a class="crm-follow-up-preview" href="' + escapeHtml(log.image_url) + '" target="_blank" rel="noopener"><img src="' + escapeHtml(log.image_url) + '" alt="Bukti hasil follow-up"><span>Lihat bukti</span></a>'
                                : '') +
                        '</article>'
                    ).join('');
                }
            }
        }

        function setSenderMode(mode) {
            activeSenderMode = mode === 'student' ? 'student' : 'agent';
            document.getElementById('wa-mode-agent-btn').classList.toggle('is-active', activeSenderMode === 'agent');
            document.getElementById('wa-mode-student-btn').classList.toggle('is-active', activeSenderMode === 'student');
            if (activeSenderMode === 'student') {
                msgInput.placeholder = 'Ketik atau paste balasan WhatsApp dari siswa di sini untuk dicatat & dianalisis AI...';
                sendAndOpenBtn.style.display = 'none';
                saveBubbleBtn.textContent = '📥 Simpan Balasan Siswa ke Chat';
                saveBubbleBtn.style.background = '#0f766e';
                saveBubbleBtn.style.color = '#ffffff';
            } else {
                msgInput.placeholder = 'Ketik pesan WhatsApp untuk siswa atau klik tombol AI di sebelah kanan...';
                sendAndOpenBtn.style.display = '';
                saveBubbleBtn.textContent = '➕ Simpan di Bubble Chat';
                saveBubbleBtn.style.background = '#ffffff';
                saveBubbleBtn.style.color = '#334155';
            }
        }

        document.getElementById('wa-mode-agent-btn').addEventListener('click', () => setSenderMode('agent'));
        document.getElementById('wa-mode-student-btn').addEventListener('click', () => setSenderMode('student'));

        function openWorkspace(leadId, focusCapture) {
            drawer.hidden = false;
            studentNameEl.textContent = 'Memuat data siswa...';
            streamEl.innerHTML = '<div class="crm-wa-date-chip">Memuat riwayat percakapan...</div>';
            pendingBase64Image = '';
            previewImg.hidden = true;
            previewCaption.innerHTML = 'Belum ada gambar dipilih. Klik <strong>"📸 Simpan Chat Jadi Gambar Bukti CRM"</strong> atau tekan <strong>Ctrl+V</strong> untuk menempel screenshot WA.';

            fetch(BASE_CRM_URL + '/leads/' + leadId + '/chat', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(res => {
                    if (!res || !res.ok) {
                        showToast(res.message || 'Gagal memuat data siswa.', true);
                        return;
                    }
                    activeLead = res.lead;
                    activeMessages = res.messages || [];

                    avatarEl.textContent = (activeLead.student_name || 'S').trim().charAt(0).toUpperCase();
                    studentNameEl.textContent = activeLead.student_name || 'Siswa';
                    stageBadgeEl.textContent = (activeLead.stage || 'Tahap 1') + (activeLead.label ? ' • ' + activeLead.label : '');
                    if (prospectBadgeEl) {
                        if (activeLead.prospect_status === 'priority') {
                            prospectBadgeEl.style.display = 'inline-block';
                            prospectBadgeEl.style.background = '#fef3c7';
                            prospectBadgeEl.style.color = '#92400e';
                            prospectBadgeEl.style.border = '1px solid #fde68a';
                            prospectBadgeEl.textContent = '⭐ Prospek Prioritas (Chat di Awal)';
                        } else if (activeLead.prospect_status === 'nurture') {
                            prospectBadgeEl.style.display = 'inline-block';
                            prospectBadgeEl.style.background = '#f1f5f9';
                            prospectBadgeEl.style.color = '#334155';
                            prospectBadgeEl.style.border = '1px solid #cbd5e1';
                            prospectBadgeEl.textContent = '🕒 Follow-up Lanjutan (Setelah Prospek)';
                        } else {
                            prospectBadgeEl.style.display = 'none';
                        }
                    }
                    let statusDesc = activeLead.participant_status || '';
                    if (statusDesc === 'Siswa' && activeLead.student_class) {
                        statusDesc += ' (Kelas ' + activeLead.student_class + ')';
                    }
                    studentSubEl.textContent = [
                        statusDesc,
                        activeLead.school_origin || 'Sekolah -',
                        activeLead.whatsapp || 'Tanpa No WA',
                        activeLead.email ? ('✉ ' + activeLead.email) : '',
                        activeLead.event_code ? (activeLead.event_code + ' - ' + activeLead.event_title) : '',
                        activeLead.campus_name || ''
                    ].filter(Boolean).join(' • ');

                    if (activeLead.whatsapp_normalized) {
                        externalBtn.href = 'https://wa.me/' + encodeURIComponent(activeLead.whatsapp_normalized);
                        externalBtn.style.display = '';
                    } else {
                        externalBtn.style.display = 'none';
                    }

                    formStage.value = activeLead.stage || 'Tahap 1';
                    formLabel.value = activeLead.label || '';
                    formReason.value = activeLead.rejection_reason || '';
                    formNotes.value = activeLead.notes || '';

                    msgInput.value = '';
                    renderChatStream();
                    renderAiVariants(res.ai_quick);
                    renderSavedGallery(res.logs || []);

                    if (focusCapture) {
                        setTimeout(() => {
                            const dataUrl = generateWhatsAppChatCanvasImage();
                            setPreviewImage(dataUrl, 'Gambar bukti percakapan WhatsApp siap disimpan ke CRM!');
                        }, 200);
                    }
                })
                .catch(() => showToast('Gagal menghubungi server CRM.', true));
        }

        document.querySelectorAll('[data-open-wa-workspace]').forEach(btn => {
            btn.addEventListener('click', () => {
                const leadId = btn.getAttribute('data-open-wa-workspace');
                const focusCapture = btn.getAttribute('data-focus-capture') === '1';
                openWorkspace(leadId, focusCapture);
            });
        });

        closeBtn.addEventListener('click', () => { drawer.hidden = true; });
        drawer.addEventListener('click', e => {
            if (e.target === drawer) drawer.hidden = true;
        });

        function appendChatMessage(openWaAfter) {
            if (!activeLead) return;
            const text = msgInput.value.trim();
            if (!text) {
                showToast('Ketik atau pilih pesan terlebih dahulu.', true);
                msgInput.focus();
                return;
            }

            if (openWaAfter && activeSenderMode === 'agent') {
                const waNum = activeLead.whatsapp_normalized || '';
                if (waNum) {
                    const waUrl = 'https://wa.me/' + encodeURIComponent(waNum) + '?text=' + encodeURIComponent(text);
                    window.open(waUrl, '_blank', 'noopener');
                } else {
                    showToast('Nomor WhatsApp siswa belum tersedia, pesan tetap disimpan di aplikasi.', true);
                }
            }

            postForm(BASE_CRM_URL + '/leads/' + activeLead.id + '/chat', {
                sender_type: activeSenderMode,
                message: text,
                sent_via_wa: openWaAfter ? 1 : 0
            }).then(res => {
                if (!res || !res.ok) {
                    showToast((res && res.message) || 'Gagal menyimpan pesan.', true);
                    return;
                }
                activeMessages.push(res.message_item);
                if (res.lead_stage) {
                    activeLead.stage = res.lead_stage;
                    formStage.value = res.lead_stage;
                    stageBadgeEl.textContent = res.lead_stage + (activeLead.label ? ' • ' + activeLead.label : '');
                }
                msgInput.value = '';
                renderChatStream();
                showToast(activeSenderMode === 'student'
                    ? '✓ Balasan siswa dicatat! Klik "Analisis Percakapan" untuk rekomendasi AI.'
                    : '✓ Pesan tercatat di ruang chat WhatsApp CRM!');
            });
        }

        sendAndOpenBtn.addEventListener('click', () => appendChatMessage(true));
        saveBubbleBtn.addEventListener('click', () => appendChatMessage(false));

        // HTML5 Canvas Generator to render the WhatsApp Conversation into a clean PNG proof image
        function wrapCanvasLines(ctx, text, maxWidth) {
            const paragraphs = String(text || '').split('\n');
            const lines = [];
            paragraphs.forEach(para => {
                const words = para.split(/\s+/);
                let current = '';
                words.forEach(word => {
                    const test = current ? (current + ' ' + word) : word;
                    if (ctx.measureText(test).width > maxWidth && current) {
                        lines.push(current);
                        current = word;
                    } else {
                        current = test;
                    }
                });
                lines.push(current);
            });
            return lines;
        }

        function generateWhatsAppChatCanvasImage() {
            const width = 760;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.font = '14px Inter, Segoe UI, Arial, sans-serif';

            const msgsToRender = activeMessages.length > 0
                ? activeMessages.slice(-10)
                : [{
                    sender_type: 'agent',
                    sender_name: activeLead ? activeLead.agent_name : 'Agent CRM',
                    message: msgInput.value.trim() || ('Follow-up WhatsApp CRM untuk ' + (activeLead ? activeLead.student_name : 'Siswa')),
                    formatted_time: new Date().toTimeString().slice(0, 5)
                }];

            const prepared = msgsToRender.map(m => {
                ctx.font = '13.5px Inter, Segoe UI, Arial, sans-serif';
                const lines = wrapCanvasLines(ctx, m.message, 460);
                const boxHeight = 32 + (lines.length * 19) + 18;
                return { msg: m, lines, boxHeight };
            });

            const totalChatHeight = prepared.reduce((acc, item) => acc + item.boxHeight + 12, 0);
            const height = Math.max(420, 145 + totalChatHeight + 70);
            canvas.width = width;
            canvas.height = height;

            // Background
            ctx.fillStyle = '#efeae2';
            ctx.fillRect(0, 0, width, height);

            // Top WhatsApp Header
            ctx.fillStyle = '#075e54';
            ctx.fillRect(0, 0, width, 78);

            // Avatar Circle
            ctx.fillStyle = '#25d366';
            ctx.beginPath();
            ctx.arc(38, 39, 22, 0, Math.PI * 2);
            ctx.fill();
            ctx.fillStyle = '#052e16';
            ctx.font = 'bold 18px Inter, Segoe UI, Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(((activeLead && activeLead.student_name) ? activeLead.student_name.charAt(0) : 'S').toUpperCase(), 38, 45);

            // Header Text
            ctx.textAlign = 'left';
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 17px Inter, Segoe UI, Arial, sans-serif';
            const titleText = (activeLead ? activeLead.student_name : 'Siswa') + ' (' + (activeLead && activeLead.whatsapp ? activeLead.whatsapp : 'No WA') + ')';
            ctx.fillText(titleText, 72, 33);

            ctx.fillStyle = '#d1fae5';
            ctx.font = '12.5px Inter, Segoe UI, Arial, sans-serif';
            const statusStr = (activeLead && activeLead.participant_status)
                ? (activeLead.participant_status + (activeLead.student_class ? ' Kelas ' + activeLead.student_class : ''))
                : '';
            const subText = [
                statusStr,
                activeLead ? activeLead.school_origin : '',
                activeLead ? activeLead.email : '',
                activeLead ? activeLead.campus_name : '',
                'Status: ' + (formStage.value || 'Tahap 1') + (formLabel.value ? ' • ' + formLabel.value : '')
            ].filter(Boolean).join(' | ');
            ctx.fillText(subText.slice(0, 95), 72, 56);

            // Date & Verification Banner
            ctx.fillStyle = '#e2f3fb';
            ctx.fillRect(20, 90, width - 40, 32);
            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 12px Inter, Segoe UI, Arial, sans-serif';
            ctx.fillText('BUKTI FOLLOW-UP WHATSAPP CRM • Dicetak: ' + new Date().toLocaleString('id-ID'), 34, 110);

            // Draw Bubbles
            let y = 136;
            prepared.forEach(item => {
                const isStudent = item.msg.sender_type === 'student';
                const bubbleW = 500;
                const bubbleX = isStudent ? 24 : (width - bubbleW - 24);

                ctx.fillStyle = isStudent ? '#ffffff' : '#d9fdd3';
                ctx.strokeStyle = isStudent ? '#cbd5e1' : '#86efac';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.roundRect(bubbleX, y, bubbleW, item.boxHeight, 10);
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = isStudent ? '#1d4ed8' : '#065f46';
                ctx.font = 'bold 11.5px Inter, Segoe UI, Arial, sans-serif';
                const senderTitle = isStudent
                    ? ('Siswa: ' + (item.msg.sender_name || (activeLead ? activeLead.student_name : 'Siswa')))
                    : ('Agent: ' + (item.msg.sender_name || 'Tim Admisi CRM'));
                ctx.fillText(senderTitle, bubbleX + 14, y + 20);

                ctx.fillStyle = '#111b21';
                ctx.font = '13.5px Inter, Segoe UI, Arial, sans-serif';
                item.lines.forEach((line, idx) => {
                    ctx.fillText(line, bubbleX + 14, y + 41 + (idx * 19));
                });

                ctx.fillStyle = '#64748b';
                ctx.font = '11px Inter, Segoe UI, Arial, sans-serif';
                ctx.textAlign = 'right';
                const timeStr = (item.msg.formatted_time || '') + (isStudent ? '' : '  ✓✓');
                ctx.fillText(timeStr, bubbleX + bubbleW - 12, y + item.boxHeight - 8);
                ctx.textAlign = 'left';

                y += item.boxHeight + 12;
            });

            // Footer Reason/Summary bar
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, height - 42, width, 42);
            ctx.fillStyle = '#f8fafc';
            ctx.font = '12px Inter, Segoe UI, Arial, sans-serif';
            const footerNote = 'Keterangan CRM: ' + (formReason.value.trim() || formNotes.value.trim() || 'Follow-up terekam melalui EventHub WhatsApp CRM Workspace');
            ctx.fillText(footerNote.slice(0, 100), 20, height - 16);

            return canvas.toDataURL('image/png');
        }

        function setPreviewImage(dataUrl, captionText) {
            pendingBase64Image = dataUrl;
            previewImg.src = dataUrl;
            previewImg.hidden = false;
            previewCaption.innerHTML = '<span style="color:#059669;font-weight:700;">✔ ' + escapeHtml(captionText) + '</span>';
        }

        function saveWorkspaceToCrm(autoGenerateIfEmpty) {
            if (!activeLead) return;
            if (autoGenerateIfEmpty && !pendingBase64Image) {
                const generated = generateWhatsAppChatCanvasImage();
                setPreviewImage(generated, 'Gambar percakapan WhatsApp berhasil dibuat otomatis!');
            }

            saveAllBtn.disabled = true;
            saveAllBtn.textContent = '⏳ Menyimpan ke CRM...';

            postForm(BASE_CRM_URL + '/leads/' + activeLead.id + '/save-chat-image', {
                stage: formStage.value,
                label: formLabel.value,
                rejection_reason: formReason.value,
                notes: formNotes.value,
                image_base64: pendingBase64Image,
                summary: pendingBase64Image
                    ? 'Bukti chat WhatsApp disimpan sebagai gambar melalui CRM Workspace'
                    : 'Status CRM diperbarui melalui WhatsApp Workspace'
            })
                .then(res => {
                    saveAllBtn.disabled = false;
                    saveAllBtn.textContent = '✅ Simpan Status & Bukti Gambar ke CRM';
                    if (!res || !res.ok) {
                        showToast((res && res.message) || 'Gagal menyimpan ke CRM.', true);
                        return;
                    }
                    activeLead.stage = res.lead.stage;
                    activeLead.label = res.lead.label;
                    activeLead.rejection_reason = res.lead.rejection_reason;
                    activeLead.notes = res.lead.notes;
                    stageBadgeEl.textContent = res.lead.stage + (res.lead.label ? ' • ' + res.lead.label : '');
                    renderSavedGallery(res.logs || []);
                    updateTableRowDom(activeLead.id, res.lead, res.logs || []);
                    showToast('✔ ' + (res.message || 'Tersimpan!'));
                })
                .catch(() => {
                    saveAllBtn.disabled = false;
                    saveAllBtn.textContent = '✅ Simpan Status & Bukti Gambar ke CRM';
                    showToast('Gagal menyimpan ke server.', true);
                });
        }

        renderSaveBtn.addEventListener('click', () => {
            const dataUrl = generateWhatsAppChatCanvasImage();
            setPreviewImage(dataUrl, 'Gambar percakapan WhatsApp berhasil dirender & langsung disimpan!');
            saveWorkspaceToCrm(false);
        });

        downloadPngBtn.addEventListener('click', () => {
            const dataUrl = pendingBase64Image || generateWhatsAppChatCanvasImage();
            setPreviewImage(dataUrl, 'Gambar percakapan siap disimpan atau diunduh.');
            const a = document.createElement('a');
            a.href = dataUrl;
            const safeName = ((activeLead && activeLead.student_name) ? activeLead.student_name : 'siswa').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
            a.download = 'bukti-chat-wa-' + safeName + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });

        saveAllBtn.addEventListener('click', () => saveWorkspaceToCrm(true));

        // File upload & Clipboard Paste (Ctrl+V) support for screenshots
        function handleImageFile(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                if (e.target && typeof e.target.result === 'string') {
                    setPreviewImage(e.target.result, 'Screenshot berhasil dimuat! Klik Simpan untuk mengunggah ke Bukti CRM.');
                    showToast('✓ Gambar screenshot siap disimpan ke Bukti Follow-Up!');
                }
            };
            reader.readAsDataURL(file);
        }

        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files[0]) {
                handleImageFile(fileInput.files[0]);
            }
        });

        window.addEventListener('paste', e => {
            if (drawer.hidden) return;
            const items = (e.clipboardData || e.originalEvent.clipboardData || {}).items || [];
            for (let i = 0; i < items.length; i++) {
                if (items[i].type && items[i].type.indexOf('image/') === 0) {
                    const blob = items[i].getAsFile();
                    handleImageFile(blob);
                    e.preventDefault();
                    break;
                }
            }
        });

        // AI Intent Buttons & Generate Message
        document.querySelectorAll('[data-ai-intent]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-ai-intent]').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                activeIntent = btn.getAttribute('data-ai-intent') || 'sapaan_awal';
                triggerAiMessageGeneration();
            });
        });

        function triggerAiMessageGeneration() {
            if (!activeLead) return;
            aiGenerateMsgBtn.disabled = true;
            aiGenerateMsgBtn.textContent = '⏳ Menyusun...';
            postForm(BASE_CRM_URL + '/ai/assist', {
                mode: 'generate_message',
                lead_id: activeLead.id,
                intent: activeIntent,
                custom_prompt: aiCustomPrompt.value.trim()
            })
                .then(res => {
                    aiGenerateMsgBtn.disabled = false;
                    aiGenerateMsgBtn.textContent = '✨ Buat Pesan AI';
                    if (res && res.ok) {
                        renderAiVariants(res);
                        if (res.primary_message) {
                            setSenderMode('agent');
                            msgInput.value = res.primary_message;
                        }
                        showToast('✨ Pesan WhatsApp berhasil dibuat oleh AI!');
                    }
                })
                .catch(() => {
                    aiGenerateMsgBtn.disabled = false;
                    aiGenerateMsgBtn.textContent = '✨ Buat Pesan AI';
                });
        }

        aiGenerateMsgBtn.addEventListener('click', triggerAiMessageGeneration);

        // AI Analyze Conversation & Auto-fill CRM Status
        aiAnalyzeBtn.addEventListener('click', () => {
            if (!activeLead) return;
            aiAnalyzeBtn.disabled = true;
            aiAnalyzeBtn.textContent = '⏳ Menganalisis...';
            const latestReply = activeSenderMode === 'student' ? msgInput.value.trim() : '';

            postForm(BASE_CRM_URL + '/ai/assist', {
                mode: 'analyze_reply',
                lead_id: activeLead.id,
                latest_reply: latestReply
            })
                .then(res => {
                    aiAnalyzeBtn.disabled = false;
                    aiAnalyzeBtn.textContent = '🔍 Analisis Percakapan';
                    if (!res || !res.ok) return;

                    aiAnalysisBox.innerHTML =
                        '<div style="background:#ffffff;border:1px solid #a7f3d0;border-radius:8px;padding:10px;">' +
                            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">' +
                                '<strong style="color:#065f46;">Sentimen: ' + escapeHtml(res.sentiment || '-') + '</strong>' +
                                '<span style="font-size:11px;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:700;">' +
                                    escapeHtml(res.recommended_stage) + ' • ' + escapeHtml(res.recommended_label) +
                                '</span>' +
                            '</div>' +
                            '<p style="margin:0 0 6px;color:#334155;">' + escapeHtml(res.analysis_summary || '') + '</p>' +
                            '<p style="margin:0 0 8px;font-size:11.5px;color:#0f172a;"><strong>Alasan Direkomendasikan:</strong> ' + escapeHtml(res.recommended_reason || '-') + '</p>' +
                            '<div style="display:flex;gap:6px;flex-wrap:wrap;">' +
                                '<button type="button" class="crm-ai-use-btn" id="crm-ai-apply-status-btn" style="background:#059669;">✅ Terapkan Status & Alasan ke Form CRM</button>' +
                                '<button type="button" class="crm-ai-use-btn" id="crm-ai-apply-reply-btn">💬 Gunakan Saran Balasan AI</button>' +
                            '</div>' +
                        '</div>';

                    const applyStatusBtn = document.getElementById('crm-ai-apply-status-btn');
                    if (applyStatusBtn) {
                        applyStatusBtn.addEventListener('click', () => {
                            if (res.recommended_stage) formStage.value = res.recommended_stage;
                            if (res.recommended_label) formLabel.value = res.recommended_label;
                            if (res.recommended_reason) formReason.value = res.recommended_reason;
                            showToast('✓ Rekomendasi Tahap, Label & Alasan diterapkan ke form CRM!');
                        });
                    }
                    const applyReplyBtn = document.getElementById('crm-ai-apply-reply-btn');
                    if (applyReplyBtn && res.suggested_reply) {
                        applyReplyBtn.addEventListener('click', () => {
                            setSenderMode('agent');
                            msgInput.value = res.suggested_reply;
                            msgInput.focus();
                            showToast('✓ Saran balasan AI dimasukkan ke kotak chat!');
                        });
                    }
                })
                .catch(() => {
                    aiAnalyzeBtn.disabled = false;
                    aiAnalyzeBtn.textContent = '🔍 Analisis Percakapan';
                });
        });

        // Top Banner AI Workspace Strategy Insight
        const workspaceBtn = document.getElementById('crm-ai-workspace-btn');
        const workspaceQuestion = document.getElementById('crm-ai-workspace-question');
        if (workspaceBtn) {
            workspaceBtn.addEventListener('click', () => {
                workspaceBtn.disabled = true;
                workspaceBtn.textContent = '⏳ Menganalisis...';
                postForm(BASE_CRM_URL + '/ai/assist', {
                    mode: 'workspace_insight',
                    stats_json: JSON.stringify(WORKSPACE_STATS),
                    custom_prompt: workspaceQuestion ? workspaceQuestion.value.trim() : ''
                })
                    .then(res => {
                        workspaceBtn.disabled = false;
                        workspaceBtn.textContent = '✨ Analisis Strategi AI';
                        if (!res || !res.ok) return;
                        const headlineEl = document.getElementById('crm-ai-headline');
                        const subEl = document.getElementById('crm-ai-subtext');
                        const cardsEl = document.getElementById('crm-ai-insight-cards');
                        if (headlineEl && res.headline) {
                            headlineEl.textContent = '🤖 [' + (res.ai_source || 'AI') + '] ' + res.headline;
                        }
                        if (subEl && res.custom_answer) {
                            subEl.innerHTML = '<strong>Jawaban AI:</strong> ' + escapeHtml(res.custom_answer);
                        }
                        if (cardsEl && Array.isArray(res.priorities) && res.priorities.length > 0) {
                            cardsEl.innerHTML = res.priorities.map((p, idx) =>
                                '<div class="crm-ai-priority-card"><strong>Langkah ' + (idx + 1) + ':</strong> ' + escapeHtml(p) + '</div>'
                            ).join('');
                        }
                    })
                    .catch(() => {
                        workspaceBtn.disabled = false;
                        workspaceBtn.textContent = '✨ Analisis Strategi AI';
                    });
            });
        }
    })();
    </script>

    <x-script />
</body>
</html>
