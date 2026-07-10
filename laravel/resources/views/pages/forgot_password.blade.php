<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Lupa Password') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-visual" aria-label="Reset password">
            <a class="brand light brand-logo-link" href="<?= site_url('login') ?>" aria-label="EventHub Login">
                <img class="brand-logo brand-logo-login" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
            </a>
            <div class="login-hero">
                <span class="eyebrow">Reset akses</span>
                <h1>Buat password baru agar akun EventHub bisa digunakan kembali.</h1>
            </div>
        </section>

        <section class="login-card">
            <span class="eyebrow">Lupa password</span>
            <h2>Reset password akun</h2>
            <?php if (session('success')): ?>
                <div class="alert success"><?= esc(session('success')) ?></div>
            <?php endif; ?>
            <?php if (session('error')): ?>
                <div class="alert error"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <?php if (session('reset_link')): ?>
                <div class="alert info">
                    Mode development: <a href="<?= esc(session('reset_link')) ?>">buka link reset password</a>.
                </div>
            <?php endif; ?>
            <form action="<?= site_url('forgot-password') ?>" method="post" class="stacked-form">
                <?= csrf_field() ?>
                <label>
                    Username atau Email
                    <input class="control" type="text" name="identity" value="<?= old('identity') ?>" required>
                </label>
                <button class="primary-button" type="submit">Kirim Link Reset</button>
                <a class="ghost-button" href="<?= site_url('login') ?>">Kembali Login</a>
            </form>
        </section>
    </main>
    <script src="<?= base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
