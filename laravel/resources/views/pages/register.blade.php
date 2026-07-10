<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Daftar User') ?> | EventHub</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-visual" aria-label="Pendaftaran user">
            <a class="brand light brand-logo-link" href="<?= site_url('login') ?>" aria-label="EventHub Login">
                <img class="brand-logo brand-logo-login" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
            </a>
            <div class="login-hero">
                <span class="eyebrow">Registrasi User</span>
                <h1>Akun baru akan direview Admin Sistem sebelum dapat digunakan.</h1>
                <div class="hero-metrics">
                    <span><strong>1</strong> Daftar</span>
                    <span><strong>2</strong> Review Admin</span>
                    <span><strong>3</strong> Login</span>
                </div>
            </div>
        </section>

        <section class="login-card register-card">
            <span class="eyebrow">Daftar user</span>
            <h2>Buat akun baru</h2>
            <?php if (session('error')): ?>
                <div class="alert error"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <form action="<?= site_url('register') ?>" method="post" class="stacked-form">
                <?= csrf_field() ?>
                <label>
                    Nama
                    <input class="control" type="text" name="name" placeholder="Nama user" value="<?= esc(old('name') ?? '') ?>" required>
                </label>
                <label>
                    Username
                    <input class="control" type="text" name="username" placeholder="username" value="<?= esc(old('username') ?? '') ?>" required>
                </label>
                <label>
                    Email
                    <input class="control" type="email" name="email" placeholder="nama@eventhub.test" value="<?= esc(old('email') ?? '') ?>" required>
                </label>
                <label>
                    Password
                    <span class="password-field">
                        <input class="control" type="password" name="password" minlength="6" required>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                    </span>
                </label>
                <button class="primary-button" type="submit">Kirim Pendaftaran</button>
                <a class="ghost-button" href="<?= site_url('login') ?>">Kembali Login</a>
            </form>
        </section>
    </main>
    <script src="<?= base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
