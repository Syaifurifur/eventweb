@props(['title' => 'Dashboard', 'currentUser' => []])
<?php
$userName = $currentUser['name'] ?? 'Admin';
$userRole = $currentUser['role'] ?? 'Admin Sistem';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
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
                <!-- Dark / Light Theme Toggle -->
                <button type="button" id="theme-toggle" class="w-10 h-10 bg-neutral-200 dark:bg-neutral-700 dark:text-white rounded-full flex justify-center items-center hover:bg-primary-50 transition" aria-label="Ganti Tema">
                    <span id="theme-toggle-dark-icon" class="hidden">
                        <i class="ri-sun-line text-lg"></i>
                    </span>
                    <span id="theme-toggle-light-icon" class="hidden">
                        <i class="ri-moon-line text-lg"></i>
                    </span>
                </button>

                <!-- Quick Calendar Button -->
                <a href="<?= site_url('calendar') ?>" class="w-10 h-10 bg-neutral-200 dark:bg-neutral-700 rounded-full hidden sm:flex justify-center items-center hover:bg-primary-50 transition" title="Kalender Event" aria-label="Kalender Event">
                    <iconify-icon icon="solar:calendar-outline" class="text-neutral-900 dark:text-white text-xl"></iconify-icon>
                </a>

                <!-- Notifications Link -->
                <a href="<?= site_url('notifications') ?>" class="has-indicator w-10 h-10 bg-neutral-200 dark:bg-neutral-700 rounded-full flex justify-center items-center hover:bg-primary-50 transition" title="Notifikasi" aria-label="Notifikasi">
                    <iconify-icon icon="iconoir:bell" class="text-neutral-900 dark:text-white text-xl"></iconify-icon>
                </a>

                <!-- User Profile Dropdown -->
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

                <!-- Direct Logout Button for Quick Access & Automated Tests -->
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

