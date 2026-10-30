<?php
$active = $active ?? 'dashboard';
$navGroups = [
    [
        'title' => 'Menu Utama',
        'items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => site_url('dashboard'), 'icon' => 'solar:home-smile-angle-outline'],
            ['key' => 'task-manager', 'label' => 'Task Manager', 'url' => site_url('task-manager'), 'icon' => 'solar:checklist-minimalistic-outline'],
            ['key' => 'crm', 'label' => 'CRM Workspace', 'url' => site_url('crm'), 'icon' => 'solar:users-group-two-rounded-outline'],
        ],
    ],
    [
        'title' => 'Manajemen Event',
        'items' => [
            ['key' => 'events', 'label' => 'Event', 'url' => site_url('events'), 'icon' => 'solar:calendar-mark-outline'],
            ['key' => 'calendar', 'label' => 'Kalender', 'url' => site_url('calendar'), 'icon' => 'solar:calendar-outline'],
            ['key' => 'approvals', 'label' => 'Approval', 'url' => site_url('approvals'), 'icon' => 'solar:shield-check-outline'],
        ],
    ],
    [
        'title' => 'Dokumen & Laporan',
        'items' => [
            ['key' => 'documents', 'label' => 'Dokumen Event', 'url' => site_url('documents'), 'icon' => 'solar:document-text-outline'],
            ['key' => 'document-templates', 'label' => 'Template Dokumen', 'url' => site_url('document-templates'), 'icon' => 'solar:documents-minimalistic-outline'],
            ['key' => 'crm-report', 'label' => 'Laporan CRM', 'url' => site_url('laporan-crm'), 'icon' => 'solar:pie-chart-3-outline'],
        ],
    ],
    [
        'title' => 'Analitik & Kampus',
        'items' => [
            [
                'key' => 'one-campus',
                'label' => 'One Campus',
                'url' => site_url('one-campus'),
                'icon' => 'solar:buildings-2-outline',
                'children' => [
                    ['key' => 'one-campus-ojt', 'label' => 'One Campus Five OJT', 'url' => site_url('one-campus/ojt'), 'dotClass' => 'text-warning-600'],
                    ['key' => 'one-campus-school', 'label' => 'One Campus Sixty School', 'url' => site_url('one-campus/schools'), 'dotClass' => 'text-primary-600'],
                    ['key' => 'one-campus-banner', 'label' => 'One Campus Five Spanduk', 'url' => site_url('one-campus/banners'), 'dotClass' => 'text-success-600'],
                ],
            ],
            ['key' => 'analytics', 'label' => 'Analytics', 'url' => site_url('analytics'), 'icon' => 'solar:pie-chart-2-outline'],
            ['key' => 'special-analytics', 'label' => 'Analisis Khusus', 'url' => site_url('analisis-khusus'), 'icon' => 'solar:graph-new-up-outline'],
        ],
    ],
    [
        'title' => 'Administrasi',
        'items' => [
            ['key' => 'master-data', 'label' => 'Master Data', 'url' => site_url('master-data'), 'icon' => 'mingcute:storage-line'],
            ['key' => 'notifications', 'label' => 'Notifikasi', 'url' => site_url('notifications'), 'icon' => 'iconoir:bell'],
            ['key' => 'users', 'label' => 'Users', 'url' => site_url('users'), 'icon' => 'flowbite:users-group-outline'],
        ],
    ],
];
$userPermissions = $currentUser['permissions'] ?? [];
$isAdminSystem = ($currentUser['role'] ?? '') === 'Admin Sistem';
$canViewSpecialAnalytics = in_array(($currentUser['role'] ?? ''), ['Admin Sistem', 'DMER'], true);
$canAccessItem = static function (string $key) use ($isAdminSystem, $userPermissions, $canViewSpecialAnalytics): bool {
    return $isAdminSystem
        || in_array($key, $userPermissions, true)
        || ($key === 'task-manager' && in_array('dashboard', $userPermissions, true))
        || ($key === 'crm-report' && (in_array('reports', $userPermissions, true) || in_array('crm', $userPermissions, true) || in_array('dashboard', $userPermissions, true)))
        || ($key === 'document-templates' && (in_array('documents', $userPermissions, true) || $isAdminSystem))
        || ($key === 'generate-documents' && in_array('documents', $userPermissions, true))
        || ($key === 'special-analytics' && $canViewSpecialAnalytics);
};
$userName = $currentUser['name'] ?? 'Admin';
$userRole = $currentUser['role'] ?? 'Admin Sistem';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'EventHub') ?> | EventHub</title>
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . @filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . @filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/remixicon.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/lib/apexcharts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/lib/dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/lib/flatpickr.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/lib/full-calendar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/dmer-tailwind.css?v=' . @filemtime(FCPATH . 'assets/css/dmer-tailwind.css')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . @filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white wowdash-body" data-shell>
    <aside class="sidebar" data-sidebar>
        <button type="button" class="sidebar-close-btn !mt-4" aria-label="Tutup menu">
            <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
        </button>
        <div>
            <a href="<?= site_url('dashboard') ?>" class="sidebar-logo gap-3">
                <img src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub" class="light-logo !max-h-[38px] w-auto object-contain">
                <img src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub" class="dark-logo !max-h-[38px] w-auto object-contain">
                <img src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub" class="logo-icon !max-h-[36px] w-auto object-contain">
                <span class="sidebar-brand-text leading-tight">
                    <strong class="block text-base font-bold text-neutral-900 dark:text-white tracking-tight">EventHub</strong>
                    <small class="block text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Integrated Scheduler</small>
                </span>
            </a>
        </div>
        <div class="sidebar-menu-area">
            <ul class="sidebar-menu" id="sidebar-menu" aria-label="Navigasi utama">
                <?php foreach ($navGroups as $group): ?>
                    <?php
                    $visibleItems = array_values(array_filter(
                        $group['items'],
                        static fn (array $item): bool => $canAccessItem($item['key'])
                    ));
                    ?>
                    <?php if ($visibleItems !== []): ?>
                        <li class="sidebar-menu-group-title"><?= esc($group['title']) ?></li>
                        <?php foreach ($visibleItems as $item): ?>
                            <?php
                            $children = $item['children'] ?? [];
                            $childKeys = array_column($children, 'key');
                            $isActive = $active === $item['key'] || in_array($active, $childKeys, true);
                            ?>
                            <?php if ($children !== []): ?>
                                <li class="dropdown <?= $isActive ? 'dropdown-open open show' : '' ?>">
                                    <a href="javascript:void(0)" class="nav-item <?= $isActive ? 'is-active' : '' ?>">
                                        <iconify-icon icon="<?= esc($item['icon']) ?>" class="menu-icon"></iconify-icon>
                                        <span><?= esc($item['label']) ?></span>
                                    </a>
                                    <ul class="sidebar-submenu" style="<?= $isActive ? 'display: block;' : 'display: none;' ?>">
                                        <?php foreach ($children as $child): ?>
                                            <?php $isChildActive = $active === $child['key']; ?>
                                            <li class="<?= $isChildActive ? 'active-page' : '' ?>">
                                                <a href="<?= $child['url'] ?>" class="<?= $isChildActive ? 'active-page' : '' ?>">
                                                    <i class="ri-circle-fill circle-icon <?= esc($child['dotClass'] ?? 'text-primary-600') ?> w-auto"></i>
                                                    <?= esc($child['label']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="<?= $isActive ? 'active-page' : '' ?>">
                                    <a href="<?= $item['url'] ?>" class="nav-item <?= $isActive ? 'active-page is-active' : '' ?>">
                                        <iconify-icon icon="<?= esc($item['icon']) ?>" class="menu-icon"></iconify-icon>
                                        <span><?= esc($item['label']) ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <main class="dashboard-main">
        <?php if (session('impersonator')): ?>
            <div style="background:linear-gradient(90deg, #7c3aed 0%, #4f46e5 100%); color:#ffffff; padding:10px 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; font-size:13px; box-shadow:0 4px 14px rgba(124,58,237,0.35); position:sticky; top:0; z-index:9999;">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <span style="background:rgba(255,255,255,0.22); padding:3px 10px; border-radius:999px; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:5px;">
                        <iconify-icon icon="solar:user-hand-up-bold" style="font-size:14px;"></iconify-icon>
                        Mode Impersonate Super Admin
                    </span>
                    <span>Anda sedang login & memantau kegiatan sebagai: <strong><?= esc($currentUser['name'] ?? session('user.name')) ?></strong> <span style="background:rgba(255,255,255,0.18); padding:2px 8px; border-radius:4px; font-size:11.5px;"><?= esc($currentUser['role'] ?? session('user.role')) ?></span></span>
                    <span style="opacity:0.85; font-size:12px;">&bull; Akun Asli: <strong><?= esc(session('impersonator.name')) ?></strong> (Super Admin)</span>
                </div>
                <div>
                    <a href="<?= site_url('impersonate/leave') ?>" style="display:inline-flex; align-items:center; gap:6px; background:#ffffff; color:#4f46e5; font-weight:700; font-size:12.5px; padding:6px 14px; border-radius:6px; text-decoration:none; box-shadow:0 2px 6px rgba(0,0,0,0.12); transition:all .15s ease;">
                        <iconify-icon icon="solar:logout-2-outline" style="font-size:16px;"></iconify-icon>
                        <span>Kembali ke Akun Super Admin</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        <div class="navbar-header border-b border-neutral-200 dark:border-neutral-600">
            <div class="flex items-center justify-between gap-4">
                <div class="col-auto">
                    <div class="flex flex-wrap items-center gap-[16px]">
                        <button type="button" class="sidebar-toggle" aria-label="Toggle Sidebar">
                            <iconify-icon icon="heroicons:bars-3-solid" class="icon non-active"></iconify-icon>
                            <iconify-icon icon="iconoir:arrow-right" class="icon active"></iconify-icon>
                        </button>
                        <button type="button" class="sidebar-mobile-toggle d-flex !leading-[0]" data-menu-toggle aria-label="Buka menu">
                            <iconify-icon icon="heroicons:bars-3-solid" class="icon !text-[30px]"></iconify-icon>
                        </button>
                        <div class="navbar-search">
                            <input type="text" name="search" placeholder="Cari data di halaman..." data-table-search>
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="theme-toggle" class="w-10 h-10 bg-neutral-200 dark:bg-neutral-700 dark:text-white rounded-full flex justify-center items-center hover:bg-primary-50 transition" aria-label="Ganti Tema">
                            <span id="theme-toggle-dark-icon" class="hidden">
                                <i class="ri-sun-line text-lg"></i>
                            </span>
                            <span id="theme-toggle-light-icon" class="hidden">
                                <i class="ri-moon-line text-lg"></i>
                            </span>
                        </button>
                        <a href="<?= site_url('calendar') ?>" class="w-10 h-10 bg-neutral-200 dark:bg-neutral-700 rounded-full hidden sm:flex justify-center items-center hover:bg-primary-50 transition" title="Kalender Event" aria-label="Kalender Event">
                            <iconify-icon icon="solar:calendar-outline" class="text-neutral-900 dark:text-white text-xl"></iconify-icon>
                        </a>
                        <a href="<?= site_url('notifications') ?>" class="has-indicator w-10 h-10 bg-neutral-200 dark:bg-neutral-700 rounded-full flex justify-center items-center hover:bg-primary-50 transition" title="Notifikasi" aria-label="Notifikasi">
                            <iconify-icon icon="iconoir:bell" class="text-neutral-900 dark:text-white text-xl"></iconify-icon>
                        </a>
                        <button data-dropdown-toggle="dropdownProfile" class="flex items-center gap-2.5 py-1 ps-1.5 pe-3 rounded-full bg-neutral-100 dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 hover:border-primary-600 transition" type="button">
                            <span class="w-8 h-8 rounded-full bg-primary-600 text-white font-bold text-sm flex items-center justify-center shrink-0">
                                <?= esc($userInitial) ?>
                            </span>
                            <span class="hidden md:block text-start leading-tight">
                                <strong class="block text-xs font-semibold text-neutral-900 dark:text-white"><?= esc($userName) ?></strong>
                                <small class="block text-[11px] text-neutral-500 dark:text-neutral-300" data-role-label><?= esc($userRole) ?></small>
                            </span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="text-neutral-500 dark:text-neutral-300 text-xs hidden md:inline-block"></iconify-icon>
                        </button>
                        <div id="dropdownProfile" class="z-30 hidden bg-white dark:bg-neutral-700 rounded-xl shadow-lg dropdown-menu-sm p-3 border border-neutral-200 dark:border-neutral-600 min-w-[230px]">
                            <div class="py-3 px-4 rounded-lg bg-primary-50 dark:bg-primary-600/25 mb-3 flex items-center justify-between gap-2">
                                <div>
                                    <h6 class="text-sm text-neutral-900 dark:text-white font-semibold mb-0"><?= esc($userName) ?></h6>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-300"><?= esc($userRole) ?></span>
                                </div>
                            </div>
                            <ul class="flex flex-col gap-1">
                                <li>
                                    <a class="text-neutral-700 dark:text-neutral-200 px-3 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-600 hover:text-primary-600 flex items-center gap-3 text-sm font-medium" href="<?= site_url('dashboard') ?>">
                                        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="text-neutral-700 dark:text-neutral-200 px-3 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-600 hover:text-primary-600 flex items-center gap-3 text-sm font-medium" href="<?= site_url('task-manager') ?>">
                                        <iconify-icon icon="solar:checklist-minimalistic-outline" class="icon text-lg"></iconify-icon> Task Manager
                                    </a>
                                </li>
                                <li>
                                    <a class="text-neutral-700 dark:text-neutral-200 px-3 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-600 hover:text-primary-600 flex items-center gap-3 text-sm font-medium" href="<?= site_url('notifications') ?>">
                                        <iconify-icon icon="iconoir:bell" class="icon text-lg"></iconify-icon> Notifikasi
                                    </a>
                                </li>
                                <li class="border-t border-neutral-200 dark:border-neutral-600 mt-1 pt-1">
                                    <form action="<?= site_url('logout') ?>" method="post" class="w-full">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="w-full text-danger-600 px-3 py-2 rounded-lg hover:bg-danger-50 dark:hover:bg-danger-600/20 flex items-center gap-3 text-sm font-semibold">
                                            <iconify-icon icon="lucide:power" class="icon text-lg"></iconify-icon> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        <form action="<?= site_url('logout') ?>" method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <button class="ghost-button !py-2 !px-3.5 !min-h-[38px] text-xs font-semibold flex items-center gap-1.5" type="submit">
                                <iconify-icon icon="lucide:power" class="text-sm"></iconify-icon>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-main-body">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400 block mb-0.5">Aplikasi Penjadwalan & Manajemen Event</span>
                    <h1 class="text-xl font-bold mb-0 text-neutral-900 dark:text-white"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <ul class="flex items-center gap-[6px] text-sm">
                    <li class="font-medium">
                        <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-2 text-neutral-600 hover:text-primary-600 dark:text-neutral-300">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg text-primary-600"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li class="text-neutral-400 dark:text-neutral-500">/</li>
                    <li class="font-semibold text-neutral-900 dark:text-white"><?= esc($title ?? 'Dashboard') ?></li>
                </ul>
            </div>

            <section class="content">
                <?php if (session('success')): ?>
                    <div class="alert success flex items-center gap-3 mb-5">
                        <iconify-icon icon="solar:check-circle-bold" class="text-xl shrink-0"></iconify-icon>
                        <span><?= esc(session('success')) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (session('error')): ?>
                    <div class="alert error flex items-center gap-3 mb-5">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-xl shrink-0"></iconify-icon>
                        <span><?= esc(session('error')) ?></span>
                    </div>
                <?php endif; ?>
                <?= $content ?? '' ?>
            </section>
        </div>

        <footer class="d-footer border-t border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-neutral-500 dark:text-neutral-400">
                <p class="mb-0">© <?= date('Y') ?> <strong class="text-neutral-800 dark:text-white">EventHub</strong> — Integrated Event & Academic Scheduler.</p>
                <p class="mb-0">UI Template <span class="text-primary-600 font-semibold">WowDash Laravel Tailwind</span></p>
            </div>
        </footer>
    </main>

    <script src="<?= base_url('assets/js/lib/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/lib/apexcharts.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/lib/simple-datatables.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/lib/iconify-icon.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/flowbite.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/wowdash.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js?v=' . @filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
