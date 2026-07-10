<?php
$active = $active ?? 'dashboard';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => site_url('dashboard'), 'icon' => 'DB'],
    ['key' => 'task-manager', 'label' => 'Task Manager', 'url' => site_url('task-manager'), 'icon' => 'TM'],
    ['key' => 'crm', 'label' => 'CRM', 'url' => site_url('crm'), 'icon' => 'CR'],
    ['key' => 'events', 'label' => 'Event', 'url' => site_url('events'), 'icon' => 'EV'],
    ['key' => 'calendar', 'label' => 'Kalender', 'url' => site_url('calendar'), 'icon' => 'CL'],
    ['key' => 'master-data', 'label' => 'Master Data', 'url' => site_url('master-data'), 'icon' => 'MD'],
    ['key' => 'approvals', 'label' => 'Approval', 'url' => site_url('approvals'), 'icon' => 'AP'],
    ['key' => 'documents', 'label' => 'Dokumen', 'url' => site_url('documents'), 'icon' => 'DK'],
    ['key' => 'generate-documents', 'label' => 'Generate Otomatis', 'url' => site_url('generate-documents/proposal'), 'icon' => 'AI'],
    ['key' => 'reports', 'label' => 'Laporan', 'url' => site_url('reports'), 'icon' => 'RP'],
    ['key' => 'one-campus', 'label' => 'One Campus', 'url' => site_url('one-campus'), 'icon' => 'OC'],
    ['key' => 'analytics', 'label' => 'Analytics', 'url' => site_url('analytics'), 'icon' => 'AN'],
    ['key' => 'special-analytics', 'label' => 'Analisis Khusus', 'url' => site_url('analisis-khusus'), 'icon' => 'AK'],
    ['key' => 'notifications', 'label' => 'Notifikasi', 'url' => site_url('notifications'), 'icon' => 'NT'],
    ['key' => 'users', 'label' => 'Users', 'url' => site_url('users'), 'icon' => 'US'],
];
$userPermissions = $currentUser['permissions'] ?? [];
$isAdminSystem = ($currentUser['role'] ?? '') === 'Admin Sistem';
$canViewSpecialAnalytics = in_array(($currentUser['role'] ?? ''), ['Admin Sistem', 'DMER'], true);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'EventHub') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body>
    <div class="app-shell" data-shell>
        <aside class="sidebar" data-sidebar>
            <a class="brand" href="<?= site_url('dashboard') ?>">
                <img class="brand-logo brand-logo-mark" src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub">
                <span>
                    <strong>EventHub</strong>
                    <small>Integrated Scheduler</small>
                </span>
            </a>

            <nav class="nav-list" aria-label="Navigasi utama">
                <?php foreach ($navItems as $item): ?>
                    <?php $hasNavAccess = $isAdminSystem || in_array($item['key'], $userPermissions, true) || ($item['key'] === 'task-manager' && in_array('dashboard', $userPermissions, true)) || ($item['key'] === 'generate-documents' && in_array('documents', $userPermissions, true)) || ($item['key'] === 'special-analytics' && $canViewSpecialAnalytics); ?>
                    <?php if ($hasNavAccess): ?>
                        <a class="nav-item <?= $active === $item['key'] ? 'is-active' : '' ?>" href="<?= $item['url'] ?>">
                            <span><?= esc($item['icon']) ?></span>
                            <?= esc($item['label']) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="main">
            <header class="topbar">
                <button class="icon-button mobile-only" type="button" data-menu-toggle aria-label="Buka menu">ME</button>
                <div>
                    <span class="eyebrow">Aplikasi Penjadwalan & Manajemen Event</span>
                    <h1><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="topbar-actions">
                    <a class="icon-button" href="<?= site_url('notifications') ?>" aria-label="Notifikasi">NT</a>
                    <form action="<?= site_url('logout') ?>" method="post" class="inline-form">
                        <?= csrf_field() ?>
                        <button class="ghost-button" type="submit">Logout</button>
                    </form>
                    <div class="user-chip">
                        <span><?= esc(substr($currentUser['name'] ?? 'A', 0, 1)) ?></span>
                        <div>
                            <strong><?= esc($currentUser['name'] ?? 'Admin') ?></strong>
                            <small data-role-label><?= esc($currentUser['role'] ?? 'Admin Sistem') ?></small>
                        </div>
                    </div>
                </div>
            </header>

            <section class="content">
                <?php if (session('success')): ?>
                    <div class="alert success"><?= esc(session('success')) ?></div>
                <?php endif; ?>
                <?php if (session('error')): ?>
                    <div class="alert error"><?= esc(session('error')) ?></div>
                <?php endif; ?>
                <?= $content ?? '' ?>
            </section>
        </main>
    </div>
    <script src="<?= base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
