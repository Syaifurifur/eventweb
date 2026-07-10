<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-visual" aria-label="Ringkasan sistem">
            <a class="brand light brand-logo-link" href="<?= site_url('dashboard') ?>" aria-label="EventHub Dashboard">
                <img class="brand-logo brand-logo-login" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
            </a>
            <div class="login-hero">
                <span class="eyebrow">PRD Prototype</span>
                <h1>Kelola event, approval, kalender, dan laporan dalam satu dashboard.</h1>
                <div class="hero-metrics">
                    <?php foreach (($loginMetrics ?? []) as $metric): ?>
                        <span><strong><?= esc(number_format((int) ($metric['value'] ?? 0), 0, ',', '.')) ?></strong> <?= esc($metric['label'] ?? '') ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="login-card">
            <span class="eyebrow">Masuk sistem</span>
            <h2>Selamat datang kembali</h2>
            <?php if (session('success')): ?>
                <div class="alert success"><?= esc(session('success')) ?></div>
            <?php endif; ?>
            <?php if (session('error')): ?>
                <div class="alert error"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <form action="<?= site_url('login') ?>" method="post" class="stacked-form">
                <?= csrf_field() ?>
                <label>
                    Username atau Email
                    <input class="control" type="text" name="identity" value="<?= old('identity') ?>" required>
                </label>
                <label>
                    Password
                    <span class="password-field">
                        <input class="control" type="password" name="password" required>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                    </span>
                </label>
                <div class="form-link-row">
                    <a href="<?= site_url('forgot-password') ?>">Lupa password?</a>
                </div>
                <button class="primary-button" type="submit">Masuk ke Dashboard</button>
                <a class="ghost-button" href="<?= site_url('register') ?>">Daftar User</a>
            </form>
        </section>
    </main>
    <script src="<?= base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
