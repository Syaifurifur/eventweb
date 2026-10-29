<!DOCTYPE html>
<html lang="id" data-theme="light">

<x-head :title="$title ?? 'EventHub'" />

<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white wowdash-body" data-shell>

    <!-- ..:: WowDash Sidebar Start ::.. -->
    <x-sidebar :active="$active ?? 'dashboard'" :currentUser="$currentUser ?? []" />
    <!-- ..:: WowDash Sidebar End ::.. -->

    <main class="dashboard-main">

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
