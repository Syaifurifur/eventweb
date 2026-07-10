<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Reset Password') ?> | EventHub</title>
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-visual" aria-label="Password baru">
            <a class="brand light brand-logo-link" href="<?= site_url('login') ?>" aria-label="EventHub Login">
                <img class="brand-logo brand-logo-login" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
            </a>
            <div class="login-hero">
                <span class="eyebrow">Password baru</span>
                <h1>Amankan akun dengan password baru yang mudah diingat dan sulit ditebak.</h1>
            </div>
        </section>

        <section class="login-card">
            <span class="eyebrow">Reset password</span>
            <h2>Buat password baru</h2>
            <?php if (session('error')): ?>
                <div class="alert error"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <form action="<?= site_url('reset-password/' . ($token ?? '')) ?>" method="post" class="stacked-form">
                <?= csrf_field() ?>
                <label>
                    Password Baru
                    <span class="password-field">
                        <input class="control" type="password" name="password" required minlength="6">
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                    </span>
                </label>
                <label>
                    Konfirmasi Password
                    <span class="password-field">
                        <input class="control" type="password" name="password_confirmation" required minlength="6">
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                    </span>
                </label>
                <button class="primary-button" type="submit">Simpan Password Baru</button>
                <a class="ghost-button" href="<?= site_url('login') ?>">Kembali Login</a>
            </form>
        </section>
    </main>
    <script src="<?= base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
