<?php
$canRegisterCrmUser = $canRegisterCrmUser ?? false;
$registeringCampus = $registeringCampus ?? null;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login CRM') ?> | EventHub</title>
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . @filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . @filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/remixicon.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . @filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white crm-login-page">
    <main class="crm-login-shell">
        <section class="crm-login-card card border border-neutral-200 dark:border-neutral-600 shadow-xl">
            <a class="crm-login-brand centered" href="<?= site_url('login') ?>" aria-label="EventHub CRM">
                <img class="brand-logo brand-logo-crm-login" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
            </a>
            <div class="crm-login-card-head">
                <span class="text-primary-600 font-bold text-xs uppercase tracking-wider">CRM Agent Workspace • WowDash</span>
                <h2>Login CRM</h2>
                <p>Masuk sebagai Admin CRM atau Agent untuk mengelola data calon pendaftar.</p>
            </div>
            <?php if (session('success')): ?>
                <div class="alert success"><?= esc(session('success')) ?></div>
            <?php endif; ?>
            <?php if (session('error')): ?>
                <div class="alert error"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <form action="<?= site_url('crm/login') ?>" method="post" class="stacked-form">
                <?= csrf_field() ?>
                <label>
                    Username
                    <input class="control form-control" type="text" name="username" value="<?= esc(old('username') ?? '') ?>" required>
                </label>
                <label>
                    Password
                    <span class="password-field">
                        <input class="control form-control" type="password" name="password" required>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                    </span>
                </label>
                <button class="primary-button btn btn-primary justify-center" type="submit">Masuk ke CRM</button>
                <a class="ghost-button justify-center" href="<?= site_url('login') ?>">Kembali ke EventHub</a>
            </form>

            <?php if ($canRegisterCrmUser): ?>
                <details class="crm-register-drawer" <?= old('register_username') ? 'open' : '' ?>>
                    <summary class="ghost-button">Daftar User CRM</summary>
                    <div class="crm-register-panel">
                        <div class="crm-login-card-head">
                            <span>Daftar User CRM</span>
                            <h2>Buat akun agent</h2>
                            <p>Akun baru masuk ke daftar review Admin Sistem untuk kampus <?= esc($registeringCampus['name'] ?? 'Anda') ?>.</p>
                        </div>
                        <form action="<?= site_url('crm/register') ?>" method="post" class="stacked-form">
                            <?= csrf_field() ?>
                            <label>
                                Nama Lengkap
                                <input class="control form-control" type="text" name="register_full_name" value="<?= esc(old('register_full_name') ?? '') ?>" required>
                            </label>
                            <label>
                                Username
                                <input class="control form-control" type="text" name="register_username" value="<?= esc(old('register_username') ?? '') ?>" required>
                            </label>
                            <label>
                                Password
                                <span class="password-field">
                                    <input class="control form-control" type="password" name="register_password" required minlength="6">
                                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                                </span>
                            </label>
                            <label>
                                Konfirmasi Password
                                <span class="password-field">
                                    <input class="control form-control" type="password" name="register_password_confirmation" required minlength="6">
                                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                                </span>
                            </label>
                            <button class="primary-button btn btn-primary justify-center" type="submit">Kirim Pendaftaran CRM</button>
                        </form>
                    </div>
                </details>
            <?php else: ?>
                <div class="crm-register-note">
                    Pendaftaran user CRM dilakukan oleh Kepala Kampus melalui session EventHub.
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script src="<?= base_url('assets/js/lib/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/lib/iconify-icon.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/flowbite.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/wowdash.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js?v=' . @filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
