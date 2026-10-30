<!DOCTYPE html>
<html lang="id" data-theme="light">

<x-head :title="$title ?? 'EventHub'" />

<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white wowdash-body" data-shell>

    <!-- ..:: WowDash Sidebar Start ::.. -->
    <x-sidebar :active="$active ?? 'dashboard'" :currentUser="$currentUser ?? []" />
    <!-- ..:: WowDash Sidebar End ::.. -->

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

        <!-- ..:: WowDash Navbar Start ::.. -->
        <x-navbar :title="$title ?? 'Dashboard'" :currentUser="$currentUser ?? []" />
        <!-- ..:: WowDash Navbar End ::.. -->

        <div class="dashboard-main-body">

            <!-- ..:: WowDash Breadcrumb Start ::.. -->
            <x-breadcrumb :title="$title ?? 'Dashboard'" :subTitle="$subTitle ?? ($title ?? 'Dashboard')" />
            <!-- ..:: WowDash Breadcrumb End ::.. -->

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
                @yield('content')
            </section>

        </div>

        <!-- ..:: WowDash Footer Start ::.. -->
        <x-footer />
        <!-- ..:: WowDash Footer End ::.. -->

    </main>

    <!-- ..:: WowDash Scripts Start ::.. -->
    <x-script :script="$script ?? ''" />
    <!-- ..:: WowDash Scripts End ::.. -->

</body>

</html>
