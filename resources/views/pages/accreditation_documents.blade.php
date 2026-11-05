<style>
.dx-chip { font-size:10px; font-weight:700; padding:2px 8px; border-radius:999px; white-space:nowrap; display:inline-block; }
.dx-chip-green { background:#dcfce7; color:#15803d; }
.dx-chip-amber { background:#ffedd5; color:#c2410c; }
.dx-chip-red { background:#ffe4e6; color:#be123c; }
.dx-chip-slate { background:#e2e8f0; color:#475569; }

html.dark .dx-chip-green { background:rgba(34,197,94,0.2); color:#4ade80; border:1px solid rgba(34,197,94,0.3); }
html.dark .dx-chip-amber { background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.3); }
html.dark .dx-chip-red { background:rgba(239,68,68,0.2); color:#f87171; border:1px solid rgba(239,68,68,0.3); }
html.dark .dx-chip-slate { background:rgba(148,163,184,0.2); color:#94a3b8; border:1px solid rgba(148,163,184,0.3); }
</style>

<div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-primary-600 transition-colors">Dashboard</a>
                <span>&rsaquo;</span>
                <span>Dokumen &amp; Laporan</span>
                <span>&rsaquo;</span>
                <span class="font-bold text-slate-700 dark:text-slate-200">Dokumen Akreditasi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-primary-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 text-sm">
                    🎓
                </span>
                Dokumen Akreditasi Kampus
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Fasilitas pengelolaan berkas &amp; bukti fisik pelaksanaan event untuk pemenuhan instrumen akreditasi (BAN-PT &amp; LAM-INFOKOM)
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <?php if ($isAdmin): ?>
                <button type="button" onclick="openDesignateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 transition-all shadow-md shadow-primary-600/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>🔍 Cari Dokumen Event &amp; Jadikan Akreditasi</span>
                </button>
            <?php endif; ?>
            <a href="<?= site_url('documents') ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors shadow-sm" title="Buka seluruh dokumen event untuk memilih">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Kelola Dokumen Event</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar Kendali</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-base">✅</span>
                <span><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
        </div>
    <?php endif; ?>

    <!-- 4 Stats Cards (Ringkasan Kesiapan Akreditasi) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Dokumen</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">📄</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-2"><?= (int) $stats['total'] ?></div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Ditetapkan untuk akreditasi</div>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-900/50 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Menunggu Kelengkapan</span>
                <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs font-bold">⚠️</span>
            </div>
            <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-2"><?= (int) $stats['waiting_complete'] ?></div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5">Perlu dilengkapi Kepala Kampus</div>
        </div>

        <div class="p-4 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/80 dark:border-blue-900/50 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">Menunggu Verifikasi</span>
                <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold">🕒</span>
            </div>
            <div class="text-2xl font-black text-blue-700 dark:text-blue-300 mt-2"><?= (int) $stats['waiting_verify'] ?></div>
            <div class="text-[11px] text-blue-600 dark:text-blue-400 mt-0.5">Sudah diisi, menunggu review Admin</div>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-900/50 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Lengkap &amp; Terverifikasi</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">✅</span>
            </div>
            <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-2"><?= (int) $stats['verified'] ?></div>
            <div class="flex items-center justify-between text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">
                <span>Siap instrumen akreditasi</span>
                <span class="font-extrabold"><?= (int) $stats['percentage'] ?>%</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar Interaktif: Kampus, Tahun Pelaksanaan, Status, Standar Akreditasi & Pencarian -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm p-4 sm:p-5">
        <form method="get" action="<?= site_url('documents/accreditation') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Filter 1: Kampus -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Kampus Pelaksana:
                </label>
                <?php if ($isAdmin): ?>
                    <select name="campus_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        <option value="">-- Semua Kampus --</option>
                        <?php foreach ($campuses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($selectedCampusId == $c['id']) ? 'selected' : '' ?>>
                                <?= esc($c['name']) ?> <?= !empty($c['campus_code']) ? '['.esc($c['campus_code']).']' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <?php
                        $userCampusName = 'Kampus Anda';
                        foreach ($campuses as $c) {
                            if ((int)$c['id'] === (int)$selectedCampusId) {
                                $userCampusName = $c['name'];
                                break;
                            }
                        }
                    ?>
                    <input type="text" readonly value="<?= esc($userCampusName) ?> (Terkunci)" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 cursor-not-allowed">
                    <input type="hidden" name="campus_id" value="<?= (int) $selectedCampusId ?>">
                <?php endif; ?>
            </div>

            <!-- Filter 2: Tahun Pelaksanaan -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Tahun Pelaksanaan:
                </label>
                <select name="year" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">-- Semua Tahun --</option>
                    <?php foreach ($availableYears as $yr): ?>
                        <option value="<?= $yr ?>" <?= ($selectedYear == $yr) ? 'selected' : '' ?>>
                            Tahun <?= $yr ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter 3: Status Kelengkapan -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Status Kelengkapan:
                </label>
                <select name="status" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu_kelengkapan" <?= ($selectedStatus === 'menunggu_kelengkapan') ? 'selected' : '' ?>>⚠️ Menunggu Kelengkapan</option>
                    <option value="menunggu_verifikasi" <?= ($selectedStatus === 'menunggu_verifikasi') ? 'selected' : '' ?>>🕒 Menunggu Verifikasi</option>
                    <option value="terverifikasi" <?= ($selectedStatus === 'terverifikasi') ? 'selected' : '' ?>>✅ Terverifikasi Lengkap</option>
                </select>
            </div>

            <!-- Filter 4: Standar Akreditasi -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Kriteria / Standar:
                </label>
                <select name="standard" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">-- Semua Kriteria --</option>
                    <?php foreach ($accreditationStandards as $std): ?>
                        <option value="<?= esc($std) ?>" <?= ($selectedStandard === $std) ? 'selected' : '' ?>>
                            <?= esc($std) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter 5: Pencarian & Tombol Aksi -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Pencarian Kata Kunci:
                </label>
                <div class="flex items-center gap-1.5">
                    <div class="relative flex-1">
                        <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Judul, kode event, catatan..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    </div>
                    <button type="submit" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition-colors shrink-0" title="Terapkan Filter">
                        Cari
                    </button>
                    <?php if (!empty($selectedCampusId) || !empty($selectedYear) || !empty($selectedStatus) || !empty($selectedStandard) || !empty($search)): ?>
                        <a href="<?= site_url('documents/accreditation') ?>" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors shrink-0" title="Reset Filter">
                            ✕
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Data Dokumen Akreditasi -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                    Daftar Dokumen Akreditasi
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Menampilkan <strong><?= count($items) ?></strong> dokumen yang ditetapkan untuk bukti akreditasi
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    <span>🏛 Cakupan:</span>
                    <strong><?= $isAdmin ? ($selectedCampusId ? 'Kampus Terpilih' : 'Seluruh Kampus') : 'Kampus Anda' ?></strong>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-3.5 text-center w-12">No</th>
                        <th class="py-3 px-4">Event &amp; Kode</th>
                        <th class="py-3 px-3.5">Kampus</th>
                        <th class="py-3 px-3.5">Tahun &amp; Standar</th>
                        <th class="py-3 px-3.5">Dokumen Utama Event</th>
                        <th class="py-3 px-3.5">Bukti Tambahan &amp; Catatan</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-slate-700 dark:text-slate-300">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-xs font-bold text-slate-600 dark:text-slate-400">Belum ada dokumen akreditasi yang sesuai dengan filter.</p>
                                <?php if ($isAdmin): ?>
                                    <p class="text-[11px] text-slate-400 mt-1">Klik tombol <strong>"+ Pilih Dokumen Event untuk Akreditasi"</strong> di atas untuk menetapkan dokumen baru.</p>
                                <?php else: ?>
                                    <p class="text-[11px] text-slate-400 mt-1">Admin belum menetapkan dokumen akreditasi untuk kampus Anda pada filter ini.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($items as $idx => $row): ?>
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3 px-3.5 text-center font-bold text-slate-400"><?= $idx + 1 ?></td>
                            
                            <!-- Event Title & Code -->
                            <td class="py-3 px-4">
                                <div class="font-extrabold text-slate-900 dark:text-slate-100 text-xs sm:text-sm">
                                    <?= esc($row['title']) ?>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span class="font-mono font-bold text-primary-600 dark:text-primary-400"><?= esc($row['event_code'] ?? 'EVT') ?></span>
                                    <span>&bull;</span>
                                    <span><?= !empty($row['event_date']) ? date('d M Y', strtotime($row['event_date'])) : '-' ?></span>
                                    <span>&bull;</span>
                                    <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-[10px]">
                                        <?= esc($row['event_category'] ?? 'Event') ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Kampus -->
                            <td class="py-3 px-3.5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">
                                    <?= esc($row['campus_name'] ?? '-') ?>
                                </div>
                                <?php if (!empty($row['campus_code'])): ?>
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[10px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        <?= esc($row['campus_code']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Tahun & Standar Akreditasi -->
                            <td class="py-3 px-3.5">
                                <div class="font-black text-slate-900 dark:text-slate-100">
                                    Tahun <?= esc((string)$row['event_year']) ?>
                                </div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800">
                                    <?= esc($row['accreditation_standard']) ?>
                                </span>
                            </td>

                            <!-- Dokumen Event Bawaan (Proposal, Report, Surat Tugas) -->
                            <td class="py-3 px-3.5">
                                <div class="space-y-2 text-xs min-w-[240px] max-w-[320px]">
                                    <!-- Proposal -->
                                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                        <div class="flex items-center justify-between gap-1.5 mb-1">
                                            <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 min-w-0">
                                                <span class="w-5 h-5 rounded flex items-center justify-center bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 text-[10px] shrink-0">📄</span>
                                                <span class="truncate">Proposal</span>
                                                <?php if (!empty($row['proposal_doc']['code']) && $row['proposal_doc']['code'] !== '-'): ?>
                                                    <span class="text-[10px] font-mono text-slate-400 shrink-0">(<?= esc($row['proposal_doc']['code']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="dx-chip <?= esc($row['proposal_doc']['status_class'] ?? 'dx-chip-slate') ?> text-[10px] py-0.5 px-2 font-bold shrink-0">
                                                <?= esc($row['proposal_doc']['status'] ?? ($row['has_proposal'] ? 'Tersedia' : 'Belum ada')) ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px]">
                                            <?php if (!empty($row['proposal_doc']['has_link'])): ?>
                                                <a href="<?= esc($row['proposal_doc']['href']) ?>" target="_blank" rel="noopener" class="font-bold text-primary-600 dark:text-primary-400 hover:underline inline-flex items-center gap-1 truncate max-w-full" title="<?= esc($row['proposal_doc']['display']) ?>">
                                                    <span class="truncate"><?= esc($row['proposal_doc']['display']) ?></span>
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                                <?php if (!empty($row['proposal_doc']['submitted_at'])): ?>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                                        <?= esc(date('d M Y H:i', strtotime((string)$row['proposal_doc']['submitted_at']))) ?><?= !empty($row['proposal_doc']['pic_name']) ? ' &middot; ' . esc($row['proposal_doc']['pic_name']) : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic">Belum ada dokumen</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- LPJ / Laporan -->
                                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                        <div class="flex items-center justify-between gap-1.5 mb-1">
                                            <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 min-w-0">
                                                <span class="w-5 h-5 rounded flex items-center justify-center bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 text-[10px] shrink-0">📑</span>
                                                <span class="truncate">LPJ / Laporan</span>
                                                <?php if (!empty($row['report_doc']['code']) && $row['report_doc']['code'] !== '-'): ?>
                                                    <span class="text-[10px] font-mono text-slate-400 shrink-0">(<?= esc($row['report_doc']['code']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="dx-chip <?= esc($row['report_doc']['status_class'] ?? 'dx-chip-slate') ?> text-[10px] py-0.5 px-2 font-bold shrink-0">
                                                <?= esc($row['report_doc']['status'] ?? ($row['has_report'] ? 'Tersedia' : 'Belum ada')) ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px]">
                                            <?php if (!empty($row['report_doc']['has_link'])): ?>
                                                <a href="<?= esc($row['report_doc']['href']) ?>" target="_blank" rel="noopener" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 truncate max-w-full" title="<?= esc($row['report_doc']['display']) ?>">
                                                    <span class="truncate"><?= esc($row['report_doc']['display']) ?></span>
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                                <?php if (!empty($row['report_doc']['submitted_at'])): ?>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                                        <?= esc(date('d M Y H:i', strtotime((string)$row['report_doc']['submitted_at']))) ?><?= !empty($row['report_doc']['pic_name']) ? ' &middot; ' . esc($row['report_doc']['pic_name']) : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic">Belum ada dokumen</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Surat Tugas / SK -->
                                    <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60 dark:border-slate-800 gap-2">
                                        <span class="text-slate-500 dark:text-slate-400 font-semibold shrink-0">SK / Surat Tugas:</span>
                                        <?php if (!empty($row['assignment_doc']['has_link'])): ?>
                                            <a href="<?= esc($row['assignment_doc']['href']) ?>" target="_blank" rel="noopener" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 truncate" title="<?= esc($row['assignment_doc']['display']) ?>">
                                                <span class="truncate"><?= esc($row['assignment_doc']['display']) ?></span> &nearr;
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Belum ada</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Bukti Tambahan & Catatan -->
                            <td class="py-3 px-3.5">
                                <div class="space-y-1 text-[11px]">
                                    <!-- Additional File -->
                                    <?php if (!empty($row['additional_evidence_path'])): ?>
                                        <div>
                                            <a href="<?= site_url('documents/accreditation/' . $row['id'] . '/download-evidence') ?>" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Unduh Bukti Fisik</span>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Google Drive Link -->
                                    <?php if (!empty($row['external_evidence_link'])): ?>
                                        <div>
                                            <a href="<?= esc($row['external_evidence_link']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                <span>Folder Drive Bukti</span>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Catatan Admin (Instruksi) -->
                                    <?php if (!empty($row['admin_notes'])): ?>
                                        <div class="text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 p-1.5 rounded border border-slate-100 dark:border-slate-800" title="<?= esc($row['admin_notes']) ?>">
                                            <span class="font-bold text-slate-700 dark:text-slate-300">Instruksi Admin:</span>
                                            <p class="truncate max-w-[200px]"><?= esc($row['admin_notes']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Catatan Kepala Kampus -->
                                    <?php if (!empty($row['campus_notes'])): ?>
                                        <div class="text-slate-500 dark:text-slate-400 bg-amber-50/50 dark:bg-amber-950/20 p-1.5 rounded border border-amber-100 dark:border-amber-900/40" title="<?= esc($row['campus_notes']) ?>">
                                            <span class="font-bold text-amber-700 dark:text-amber-300">Keterangan Kampus:</span>
                                            <p class="truncate max-w-[200px]"><?= esc($row['campus_notes']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-3.5 text-center">
                                <?php if ($row['status'] === 'terverifikasi' || $row['status'] === 'lengkap'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        &#10003; Terverifikasi Lengkap
                                    </span>
                                <?php elseif ($row['status'] === 'menunggu_verifikasi'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                        &#9203; Menunggu Review Admin
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                        &#9888; Belum Lengkap
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Lengkapi Data (Kepala Kampus & Admin) -->
                                    <button type="button" 
                                            onclick='openCompleteModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 transition-all shadow-sm" 
                                            title="Lengkapi data & bukti akreditasi">
                                        <span>✏️ Lengkapi</span>
                                    </button>

                                    <!-- Tombol Khusus Admin: Verifikasi & Hapus -->
                                    <?php if ($isAdmin): ?>
                                        <button type="button" 
                                                onclick='openVerifyModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 border border-emerald-300 dark:border-emerald-800 transition-colors" 
                                                title="Verifikasi dokumen">
                                            <span>🛡️ Review</span>
                                        </button>

                                        <form method="post" action="<?= site_url('documents/accreditation/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini dari daftar akreditasi?')" class="inline-block">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition-colors" title="Hapus dari Akreditasi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODAL 1: CARI DOKUMEN EVENT UNTUK AKREDITASI (ADMIN) ==================== -->
<?php if ($isAdmin): ?>
    <dialog id="dialog-designate-accreditation" class="panel" style="max-width: 820px; width: 95%; max-height: 90vh; border-radius: 16px; border: 1px solid var(--border-color, #cbd5e1); box-shadow: 0 20px 40px rgba(0,0,0,0.35); padding: 0; overflow: hidden; background: var(--surface, #ffffff); color: var(--ink, #1e293b);">
        <form method="post" action="<?= site_url('documents/accreditation/designate') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="event_id" id="candSelectedEventId" value="" required>

            <!-- Header -->
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-primary-100 dark:bg-primary-900/60 text-primary-600 dark:text-primary-300 flex items-center justify-center text-xs">🔍</span>
                        <span>Cari dari Dokumen Event untuk Akreditasi</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Cari dokumen event kampus, periksa status berkas, dan klik tombol untuk menjadikannya Dokumen Akreditasi
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('dialog-designate-accreditation').close()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 dark:hover:text-slate-200 transition-colors">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4 max-h-[calc(90vh-140px)] overflow-y-auto text-xs">
                <!-- Search & Filters Container -->
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Pencarian Dokumen Event:
                        </label>
                        <div class="relative">
                            <input type="text" id="candSearchInput" oninput="filterCandidateList()" placeholder="Ketik nama event, kode event (EVT-...), nama kampus, atau kategori..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none pl-9">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Kampus:</label>
                            <select id="candCampusFilter" onchange="filterCandidateList()" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                <option value="">-- Semua Kampus --</option>
                                <?php foreach ($campuses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Ketersediaan Berkas:</label>
                            <select id="candDocFilter" onchange="filterCandidateList()" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                <option value="all">Semua Event</option>
                                <option value="with_docs">Hanya Yang Memiliki Berkas Proposal/LPJ</option>
                                <option value="unaccredited" selected>Hanya Yang Belum Jadi Akreditasi</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-200/60 dark:border-slate-800">
                        <span>Hasil pencarian: <strong id="candCount" class="text-primary-600 dark:text-primary-400">0</strong> dokumen event</span>
                        <span class="italic text-slate-400">Klik tombol "⭐ Jadikan Data Akreditasi" pada baris event yang ingin ditetapkan</span>
                    </div>
                </div>

                <!-- Hasil Pencarian Dokumen Event (Scrollable List) -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Pilih dari Hasil Pencarian Dokumen Event:
                    </label>
                    <div id="candResultsContainer" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        <!-- Rendered by JS -->
                    </div>
                </div>

                <!-- Panel Event Terpilih & Form Konfigurasi Akreditasi -->
                <div id="selectedEventPanel" class="hidden p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border-2 border-indigo-300 dark:border-indigo-700 space-y-3.5 transition-all">
                    <div class="flex items-start justify-between gap-3 pb-2.5 border-b border-indigo-200/80 dark:border-indigo-800">
                        <div>
                            <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
                                <span>🎯</span>
                                <span>Event Terpilih untuk Dijadikan Akreditasi:</span>
                            </div>
                            <h4 id="selectedEventName" class="text-sm font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">
                                -
                            </h4>
                            <div id="selectedEventCampus" class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 font-medium">
                                -
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-indigo-600 text-white shrink-0">
                            Siap Ditetapkan
                        </span>
                    </div>

                    <!-- Standar Kriteria Akreditasi -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Standar / Kriteria Akreditasi (BAN-PT &amp; LAM-INFOKOM): <span class="text-rose-500">*</span>
                        </label>
                        <select name="accreditation_standard" required class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                            <?php foreach ($accreditationStandards as $std): ?>
                                <option value="<?= esc($std) ?>" <?= str_contains($std, 'Kriteria 9') ? 'selected' : '' ?>>
                                    <?= esc($std) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Judul Khusus Akreditasi -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Judul Berkas Akreditasi (Opsional):
                            </label>
                            <input type="text" name="title" id="selectedEventCustomTitle" placeholder="Kosongkan jika menggunakan judul event asli..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        </div>

                        <!-- Jenis Dokumen yang Diwajibkan -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Jenis Dokumen yang Diwajibkan:
                            </label>
                            <select name="doc_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                <option value="all">Semua Dokumen (Proposal, LPJ, Surat Tugas &amp; Bukti Kegiatan)</option>
                                <option value="report">Laporan Pertanggungjawaban (LPJ) &amp; Dokumentasi</option>
                                <option value="proposal">Proposal Kegiatan &amp; RAB</option>
                                <option value="assignment_letter">Surat Tugas &amp; SK Tim Pelaksana</option>
                            </select>
                        </div>
                    </div>

                    <!-- Instruksi Admin ke Kepala Kampus -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Instruksi / Catatan Admin untuk Kepala Kampus:
                        </label>
                        <textarea name="admin_notes" rows="2" placeholder="Contoh: Harap Kepala Kampus melengkapi file daftar hadir peserta, dokumentasi ber-geotag, dan berita acara penutupan..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('dialog-designate-accreditation').close()" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300 transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnSubmitDesignate" disabled class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md">
                    Tetapkan Dokumen Akreditasi &rarr;
                </button>
            </div>
        </form>
    </dialog>
<?php endif; ?>

<!-- ==================== MODAL 2: LENGKAPI DATA DOKUMEN AKREDITASI (KEPALA KAMPUS & ADMIN) ==================== -->
<dialog id="dialog-complete-accreditation" class="panel" style="max-width: 680px; width: 95%; max-height: 90vh; border-radius: 16px; border: 1px solid var(--border-color, #cbd5e1); box-shadow: 0 20px 40px rgba(0,0,0,0.35); padding: 0; overflow: hidden; background: var(--surface, #ffffff); color: var(--ink, #1e293b);">
    <form id="formCompleteAccreditation" method="post" action="#" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <!-- Header -->
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                    Lengkapi Dokumen Akreditasi
                </h3>
                <p id="modalCompleteSubtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Unggah bukti fisik dan lengkapi data pemenuhan kriteria akreditasi
                </p>
            </div>
            <button type="button" onclick="document.getElementById('dialog-complete-accreditation').close()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 dark:hover:text-slate-200 transition-colors">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4 max-h-[calc(90vh-140px)] overflow-y-auto text-xs">
            <!-- Box Informasi Instruksi Admin -->
            <div id="modalCompleteAdminNotesBox" class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800 text-xs">
                <div class="flex items-center gap-2 font-bold text-indigo-900 dark:text-indigo-200 mb-1">
                    <span>📌</span>
                    <span>Instruksi dari Tim Akreditasi (Admin):</span>
                </div>
                <p id="modalCompleteAdminNotes" class="text-slate-700 dark:text-slate-300 leading-relaxed">
                    -
                </p>
            </div>

            <!-- Upload File Bukti Tambahan Akreditasi -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Upload Berkas Bukti Tambahan (PDF / Word / ZIP / Gambar):
                </label>
                <input type="file" name="additional_evidence" accept=".pdf,.doc,.docx,.zip,.png,.jpg,.jpeg" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none">
                <p class="text-[11px] text-slate-500 mt-1">
                    Maksimal 25 MB. Disarankan format PDF atau arsip ZIP berisi gabungan bukti (daftar hadir, notulensi, dokumentasi foto).
                </p>
                <div id="modalCompleteCurrentEvidence" class="mt-1.5 hidden text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                    File saat ini: <span id="modalCompleteCurrentFileName">-</span>
                </div>
            </div>

            <!-- Link Folder Google Drive / Dokumen Pendukung Eksternal -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Tautan Link Google Drive / Cloud Bukti Kegiatan:
                </label>
                <input type="url" name="external_evidence_link" id="modalCompleteExternalLink" placeholder="https://drive.google.com/drive/folders/..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <p class="text-[11px] text-slate-500 mt-1">
                    Pastikan hak akses tautan disetel dapat dilihat (Anyone with link can view).
                </p>
            </div>

            <!-- Catatan Pemenuhan dari Kepala Kampus -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Catatan Keterangan Pemenuhan Bukti (Kepala Kampus):
                </label>
                <textarea name="campus_notes" id="modalCompleteCampusNotes" rows="3" placeholder="Tuliskan keterangan kelengkapan, nomor dokumen pendukung, atau penjelasan ketercapaian kegiatan..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
            </div>

            <!-- Status Pengajuan -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Status Dokumen:
                </label>
                <select name="status" id="modalCompleteStatus" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="menunggu_verifikasi">🕒 Selesai Dilengkapi &bull; Kirim untuk Verifikasi Admin</option>
                    <option value="menunggu_kelengkapan">⚠️ Masih Draft (Belum Lengkap Semua)</option>
                    <?php if ($isAdmin): ?>
                        <option value="terverifikasi">✅ Tandai Langsung Terverifikasi Lengkap (Admin)</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-end gap-2.5">
            <button type="button" onclick="document.getElementById('dialog-complete-accreditation').close()" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300 transition-colors">
                Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 transition-all shadow-md">
                Simpan &amp; Perbarui Kelengkapan
            </button>
        </div>
    </form>
</dialog>

<!-- ==================== MODAL 3: VERIFIKASI DOKUMEN AKREDITASI (ADMIN) ==================== -->
<?php if ($isAdmin): ?>
    <dialog id="dialog-verify-accreditation" class="panel" style="max-width: 580px; width: 95%; max-height: 90vh; border-radius: 16px; border: 1px solid var(--border-color, #cbd5e1); box-shadow: 0 20px 40px rgba(0,0,0,0.35); padding: 0; overflow: hidden; background: var(--surface, #ffffff); color: var(--ink, #1e293b);">
        <form id="formVerifyAccreditation" method="post" action="#">
            <?= csrf_field() ?>
            <!-- Header -->
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                        Review &amp; Verifikasi Dokumen Akreditasi
                    </h3>
                    <p id="modalVerifySubtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Tinjau kelengkapan berkas akreditasi yang telah diisi Kepala Kampus
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('dialog-verify-accreditation').close()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 dark:hover:text-slate-200 transition-colors">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4 max-h-[calc(90vh-140px)] overflow-y-auto text-xs">
                <!-- Info Respon Kampus -->
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 space-y-2">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Judul Dokumen:</span>
                        <strong id="modalVerifyTitle" class="text-slate-800 dark:text-slate-200">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Keterangan / Catatan Kepala Kampus:</span>
                        <p id="modalVerifyCampusNotes" class="text-slate-700 dark:text-slate-300 italic">-</p>
                    </div>
                </div>

                <!-- Pilihan Keputusan Verifikasi -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-2">
                        Keputusan Verifikasi:
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/20 cursor-pointer">
                            <input type="radio" name="action" value="approve" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <strong class="text-emerald-800 dark:text-emerald-200 block">✅ Setujui &amp; Tandai Terverifikasi Lengkap</strong>
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400">Berkas dan data fisik telah sesuai dengan kriteria instrumen akreditasi.</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/20 cursor-pointer">
                            <input type="radio" name="action" value="reject" class="text-amber-600 focus:ring-amber-500">
                            <div>
                                <strong class="text-amber-800 dark:text-amber-200 block">⚠️ Kembalikan ke Kepala Kampus (Perlu Revisi)</strong>
                                <span class="text-[11px] text-amber-600 dark:text-amber-400">Bukti belum mencukupi atau perlu perbaikan dokumen.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Catatan Evaluasi Feedback -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Feedback / Verifikasi (Ditampilkan ke Kepala Kampus):
                    </label>
                    <textarea name="admin_notes" id="modalVerifyAdminNotes" rows="3" placeholder="Tuliskan catatan verifikasi atau kekurangan dokumen yang perlu diperbaiki..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('dialog-verify-accreditation').close()" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 transition-all shadow-md">
                    Simpan Keputusan Verifikasi
                </button>
            </div>
        </form>
    </dialog>
<?php endif; ?>

<script>
const candidateEventsList = <?= !empty($candidateEvents) ? json_encode($candidateEvents, JSON_HEX_APOS | JSON_HEX_QUOT) : '[]' ?>;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch(e) {
        return dateStr;
    }
}

function renderCandidateList(list) {
    const container = document.getElementById('candResultsContainer');
    const countEl = document.getElementById('candCount');
    if (!container) return;

    if (countEl) countEl.textContent = list.length;

    if (list.length === 0) {
        container.innerHTML = `
            <div class="p-6 text-center text-slate-400 dark:text-slate-500 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-bold text-xs">Tidak ada dokumen event yang cocok dengan filter pencarian.</p>
                <p class="text-[11px] mt-0.5">Coba gunakan kata kunci lain atau ubah filter kampus.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = list.map(ev => {
        const isAcc = Boolean(ev.is_accredited);
        const propBadge = ev.has_proposal 
            ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">📄 Proposal ✓</span>` 
            : `<span class="px-1.5 py-0.5 rounded text-[10px] text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800">📄 Proposal -</span>`;
        const repBadge = ev.has_report 
            ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">📑 LPJ ✓</span>` 
            : `<span class="px-1.5 py-0.5 rounded text-[10px] text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800">📑 LPJ -</span>`;

        const evJson = JSON.stringify(ev).replace(/"/g, '&quot;');

        const actionBtn = isAcc
            ? `<span class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shrink-0">✅ Terdaftar Akreditasi</span>`
            : `<button type="button" onclick="chooseEventToAccredit(${evJson})" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 active:scale-95 transition-all shadow-sm flex items-center gap-1.5 shrink-0">
                    <span>⭐ Jadikan Data Akreditasi</span>
               </button>`;

        return `
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-400 dark:hover:border-primary-500 bg-white dark:bg-slate-900 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] mb-1">
                        <span class="font-mono font-bold text-primary-600 dark:text-primary-400">[${escapeHtml(ev.code || 'EVT')}]</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-500 dark:text-slate-400">${ev.event_date ? formatDate(ev.event_date) : '-'}</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold text-[10px]">${escapeHtml(ev.category || 'Event')}</span>
                    </div>
                    <h4 class="font-extrabold text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate" title="${escapeHtml(ev.title)}">
                        ${escapeHtml(ev.title)}
                    </h4>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">🏛 ${escapeHtml(ev.campus_name || 'Semua Kampus')}</span>
                        <span>&bull;</span>
                        ${propBadge}
                        ${repBadge}
                    </div>
                </div>
                <div>${actionBtn}</div>
            </div>
        `;
    }).join('');
}

function filterCandidateList() {
    const q = (document.getElementById('candSearchInput')?.value || '').toLowerCase().trim();
    const campusId = document.getElementById('candCampusFilter')?.value || '';
    const docFilter = document.getElementById('candDocFilter')?.value || 'all';

    const filtered = candidateEventsList.filter(ev => {
        if (campusId !== '' && String(ev.campus_id) !== String(campusId)) return false;
        if (docFilter === 'with_docs' && !ev.has_proposal && !ev.has_report) return false;
        if (docFilter === 'unaccredited' && ev.is_accredited) return false;

        if (q !== '') {
            const matchTitle = (ev.title || '').toLowerCase().includes(q);
            const matchCode = (ev.code || '').toLowerCase().includes(q);
            const matchCampus = (ev.campus_name || '').toLowerCase().includes(q);
            const matchCat = (ev.category || '').toLowerCase().includes(q);
            if (!matchTitle && !matchCode && !matchCampus && !matchCat) return false;
        }
        return true;
    });

    renderCandidateList(filtered);
}

function chooseEventToAccredit(ev) {
    const hiddenId = document.getElementById('candSelectedEventId');
    if (hiddenId) hiddenId.value = ev.id;

    const nameEl = document.getElementById('selectedEventName');
    if (nameEl) nameEl.textContent = `[${ev.code || 'EVT'}] ${ev.title}`;

    const campusEl = document.getElementById('selectedEventCampus');
    if (campusEl) campusEl.textContent = `🏛 ${ev.campus_name || 'Semua Kampus'} • Pelaksanaan: ${ev.event_date ? formatDate(ev.event_date) : '-'} (Tahun ${ev.event_year || ''})`;

    const customTitleInput = document.getElementById('selectedEventCustomTitle');
    if (customTitleInput) customTitleInput.placeholder = ev.title;

    const btnSubmit = document.getElementById('btnSubmitDesignate');
    if (btnSubmit) btnSubmit.disabled = false;

    const panel = document.getElementById('selectedEventPanel');
    if (panel) {
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function openDesignateModal() {
    const dialog = document.getElementById('dialog-designate-accreditation');
    if (dialog) {
        filterCandidateList();
        dialog.showModal();
        setTimeout(() => document.getElementById('candSearchInput')?.focus(), 100);
    }
}

function openCompleteModal(data) {
    const dialog = document.getElementById('dialog-complete-accreditation');
    const form = document.getElementById('formCompleteAccreditation');
    if (!dialog || !form) return;

    form.action = "<?= site_url('documents/accreditation') ?>/" + data.id + "/complete";
    document.getElementById('modalCompleteSubtitle').textContent = (data.title || 'Dokumen Akreditasi') + ' • ' + (data.campus_name || 'Kampus');
    
    // Notes
    const adminNotesEl = document.getElementById('modalCompleteAdminNotes');
    if (adminNotesEl) {
        adminNotesEl.textContent = data.admin_notes && data.admin_notes.trim() !== '' ? data.admin_notes : 'Tidak ada catatan instruksi khusus dari Admin.';
    }

    // External Link & Campus Notes
    const extLinkInput = document.getElementById('modalCompleteExternalLink');
    if (extLinkInput) extLinkInput.value = data.external_evidence_link || '';

    const campusNotesInput = document.getElementById('modalCompleteCampusNotes');
    if (campusNotesInput) campusNotesInput.value = data.campus_notes || '';

    // Evidence file indicator
    const currentEvBox = document.getElementById('modalCompleteCurrentEvidence');
    const currentEvName = document.getElementById('modalCompleteCurrentFileName');
    if (currentEvBox && currentEvName) {
        if (data.additional_evidence_name && data.additional_evidence_name.trim() !== '') {
            currentEvName.textContent = data.additional_evidence_name;
            currentEvBox.classList.remove('hidden');
        } else {
            currentEvBox.classList.add('hidden');
        }
    }

    // Status
    const statusSelect = document.getElementById('modalCompleteStatus');
    if (statusSelect) {
        statusSelect.value = data.status || 'menunggu_verifikasi';
    }

    dialog.showModal();
}

function openVerifyModal(data) {
    const dialog = document.getElementById('dialog-verify-accreditation');
    const form = document.getElementById('formVerifyAccreditation');
    if (!dialog || !form) return;

    form.action = "<?= site_url('documents/accreditation') ?>/" + data.id + "/verify";
    document.getElementById('modalVerifyTitle').textContent = (data.title || 'Dokumen Akreditasi') + ' (' + (data.campus_name || '-') + ')';
    document.getElementById('modalVerifyCampusNotes').textContent = data.campus_notes && data.campus_notes.trim() !== '' ? data.campus_notes : 'Kepala Kampus belum menambahkan catatan keterangan.';
    document.getElementById('modalVerifyAdminNotes').value = data.admin_notes || '';

    dialog.showModal();
}

// Close dialog on backdrop click
document.addEventListener('DOMContentLoaded', () => {
    ['dialog-designate-accreditation', 'dialog-complete-accreditation', 'dialog-verify-accreditation'].forEach(id => {
        const dialog = document.getElementById(id);
        if (dialog) {
            dialog.addEventListener('click', (e) => {
                const rect = dialog.getBoundingClientRect();
                const inDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height
                    && rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
                if (!inDialog) {
                    dialog.close();
                }
            });
        }
    });
});
</script>
