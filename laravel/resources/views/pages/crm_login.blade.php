<?php
$canRegisterCrmUser = $canRegisterCrmUser ?? false;
$registeringCampus = $registeringCampus ?? null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login CRM') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="crm-login-page">
    <main class="crm-login-shell">
        <section class="crm-login-card">
            <a class="crm-login-brand centered" href="<?= site_url('login') ?>" aria-label="EventHub CRM">
                <img class="brand-logo brand-logo-crm-login" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
            </a>
            <div class="crm-login-card-head">
                <span>CRM Agent Workspace</span>
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
                    <input class="control" type="text" name="username" value="<?= esc(old('username') ?? '') ?>" required>
                </label>
                <label>
                    Password
                    <span class="password-field">
                        <input class="control" type="password" name="password" required>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                    </span>
                </label>
                <button class="primary-button" type="submit">Masuk ke CRM</button>
                <a class="ghost-button" href="<?= site_url('login') ?>">Kembali ke EventHub</a>
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
                                <input class="control" type="text" name="register_full_name" value="<?= esc(old('register_full_name') ?? '') ?>" required>
                            </label>
                            <label>
                                Username
                                <input class="control" type="text" name="register_username" value="<?= esc(old('register_username') ?? '') ?>" required>
                            </label>
                            <label>
                                Password
                                <span class="password-field">
                                    <input class="control" type="password" name="register_password" required minlength="6">
                                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                                </span>
                            </label>
                            <label>
                                Konfirmasi Password
                                <span class="password-field">
                                    <input class="control" type="password" name="register_password_confirmation" required minlength="6">
                                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                                </span>
                            </label>
                            <button class="primary-button" type="submit">Kirim Pendaftaran CRM</button>
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
    <script src="<?= base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
