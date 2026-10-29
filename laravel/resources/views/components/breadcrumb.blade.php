@props(['title' => 'Dashboard', 'subTitle' => ''])
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <span class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400 block mb-0.5">Aplikasi Penjadwalan & Manajemen Event</span>
        <h1 class="text-xl font-bold mb-0 text-neutral-900 dark:text-white"><?= esc($title ?: 'Dashboard') ?></h1>
    </div>
    <ul class="flex items-center gap-[6px] text-sm">
        <li class="font-medium">
            <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-2 text-neutral-600 hover:text-primary-600 dark:text-neutral-300">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg text-primary-600"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li class="text-neutral-400 dark:text-neutral-500">/</li>
        <li class="font-semibold text-neutral-900 dark:text-white"><?= esc($subTitle !== '' ? $subTitle : ($title ?: 'Dashboard')) ?></li>
    </ul>
</div>

