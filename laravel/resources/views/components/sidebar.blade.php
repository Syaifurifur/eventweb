@props(['active' => 'dashboard', 'currentUser' => []])
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
?>
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

