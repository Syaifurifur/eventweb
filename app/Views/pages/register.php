<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Daftar User') ?> | EventHub</title>
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . @filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . @filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/remixicon.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . @filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>
<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white wowdash-auth-body">
    <section class="bg-white dark:bg-dark-2 flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:flex hidden bg-gradient-to-br from-slate-900 via-primary-900 to-slate-900 relative overflow-hidden p-12 flex-col justify-between text-white">
            <div class="relative z-10">
                <a class="inline-flex items-center gap-3" href="<?= site_url('login') ?>" aria-label="EventHub Login">
                    <img class="h-12 w-auto object-contain" src="<?= base_url('assets/img/eventhub-logo.png') ?>" alt="EventHub">
                </a>
            </div>
            <div class="relative z-10 my-auto py-8">
                <div class="max-w-[500px] mx-auto text-center mb-8">
                    <img src="<?= base_url('assets/images/auth/auth-img.png') ?>" alt="Registrasi User" class="max-h-[260px] w-auto mx-auto drop-shadow-2xl">
                </div>
                <span class="inline-block px-3 py-1 rounded-full bg-primary-600/30 border border-primary-400/30 text-primary-200 text-xs font-semibold uppercase tracking-wider mb-3">Registrasi User</span>
                <h1 class="text-3xl font-bold text-white leading-tight mb-6">Akun baru akan direview Admin Sistem sebelum dapat digunakan.</h1>
                <div class="grid grid-cols-3 gap-3">
                    <div class="p-4 rounded-xl bg-white/10 border border-white/15"><strong class="block text-xl font-bold text-white">1</strong><span class="text-xs text-neutral-200">Daftar</span></div>
                    <div class="p-4 rounded-xl bg-white/10 border border-white/15"><strong class="block text-xl font-bold text-white">2</strong><span class="text-xs text-neutral-200">Review Admin</span></div>
                    <div class="p-4 rounded-xl bg-white/10 border border-white/15"><strong class="block text-xl font-bold text-white">3</strong><span class="text-xs text-neutral-200">Login</span></div>
                </div>
            </div>
            <div class="relative z-10 text-xs text-neutral-300">© <?= date('Y') ?> EventHub — WowDash UI</div>
        </div>

        <div class="lg:w-1/2 w-full py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div class="mb-6">
                    <a href="<?= site_url('login') ?>" class="mb-4 inline-flex items-center gap-3">
                        <img src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub" class="h-11 w-auto object-contain">
                        <div>
                            <strong class="block text-lg font-bold text-neutral-900 dark:text-white leading-none">EventHub</strong>
                            <small class="text-xs text-neutral-500 dark:text-neutral-400">Integrated Scheduler</small>
                        </div>
                    </a>
                    <span class="text-xs font-bold uppercase tracking-wider text-primary-600 block mb-1">Daftar user</span>
                    <h4 class="mb-2 font-bold text-2xl text-neutral-900 dark:text-white">Buat akun baru</h4>
                    <p class="mb-6 text-secondary-light text-sm">Lengkapi data di bawah ini untuk mengajukan pembuatan akun.</p>
                </div>

                <?php if (session('error')): ?>
                    <div class="alert error mb-5"><?= esc(session('error')) ?></div>
                <?php endif; ?>

                <form action="<?= site_url('register') ?>" method="post" class="stacked-form">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label class="form-label block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Nama</label>
                        <div class="icon-field relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl text-neutral-500">
                                <iconify-icon icon="f7:person"></iconify-icon>
                            </span>
                            <input class="form-control control h-[52px] !ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl w-full" type="text" name="name" placeholder="Nama user" value="<?= esc(old('name') ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Username</label>
                        <div class="icon-field relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl text-neutral-500">
                                <iconify-icon icon="solar:user-id-outline"></iconify-icon>
                            </span>
                            <input class="form-control control h-[52px] !ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl w-full" type="text" name="username" placeholder="username" value="<?= esc(old('username') ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Email</label>
                        <div class="icon-field relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl text-neutral-500">
                                <iconify-icon icon="mage:email"></iconify-icon>
                            </span>
                            <input class="form-control control h-[52px] !ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl w-full" type="email" name="email" placeholder="nama@eventhub.test" value="<?= esc(old('email') ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Password</label>
                        <div class="relative password-field">
                            <div class="icon-field">
                                <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl text-neutral-500">
                                    <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                </span>
                                <input class="form-control control h-[52px] !ps-11 !pe-12 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl w-full" type="password" name="password" id="register-password" minlength="6" placeholder="Minimal 6 karakter" required>
                            </div>
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light text-lg" data-toggle="#register-password" data-password-toggle aria-label="Lihat password" aria-pressed="false"></span>
                        </div>
                    </div>
                    <button class="btn btn-primary primary-button justify-center text-sm font-semibold px-4 py-3.5 w-full rounded-xl" type="submit">Kirim Pendaftaran</button>
                    <a class="ghost-button justify-center text-sm font-semibold py-3 px-4 w-full rounded-xl mt-3 text-center" href="<?= site_url('login') ?>">Kembali Login</a>
                </form>
            </div>
        </div>
    </section>
    <script src="<?= base_url('assets/js/lib/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/lib/iconify-icon.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/flowbite.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/wowdash.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js?v=' . @filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
</body>
</html>
