<?php
$dmerCampusesData = $dmerCampuses ?? [];
$canViewAllTabs = (bool) ($canViewAllDmerTabs ?? false);
$defaultCampusNo = (int) ($dmerDefaultCampusNo ?? ($dmerCampusesData[0]['no'] ?? 1));
$userCampusName = (string) ($dmerUserCampusName ?? ($dmerCampusesData[0]['kampus'] ?? 'Kampus'));
$selectedYear = (string) ($dmerSelectedYear ?? '2026');
$selectedMonth = (string) ($dmerSelectedMonth ?? 'all');
$yearOptions = $dmerYearOptions ?? ['2026', '2025'];
$monthOptions = $dmerMonthOptions ?? [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
];
$periodLabel = (string) ($dmerPeriodLabel ?? 'Tahun 2026 (Semua Bulan)');
$totalCampusesCount = count($dmerCampusesData);
?>

<style>
    .dmer-dashboard-wrap {
        width: 100%;
        max-width: 100%;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .dmer-dashboard-wrap .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .dmer-dashboard-wrap .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 9999px; }
    .dmer-dashboard-wrap .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .dmer-dashboard-wrap .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .dmer-dashboard-wrap .cell-achieved { background-color: #ecfdf5 !important; color: #047857 !important; font-weight: 700 !important; }
    .dmer-dashboard-wrap .cell-unachieved { background-color: #fff1f2 !important; color: #be123c !important; }
    .dmer-dashboard-wrap input[type=range] { -webkit-appearance: none; appearance: none; background: transparent; width: 100%; }
    .dmer-dashboard-wrap input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%;
        background: #2563eb; cursor: pointer; margin-top: -6px; box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .dmer-dashboard-wrap input[type=range]::-webkit-slider-runnable-track {
        width: 100%; height: 4px; cursor: pointer; background: #e2e8f0; border-radius: 2px;
    }

    /* Responsive Media Layout System (Immune to CSS layer conflicts) */
    .dmer-header-top {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
    }
    .dmer-header-brand {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        flex: 1 1 auto;
        min-width: 0;
    }
    .dmer-period-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
        padding: 0.5rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        width: 100%;
    }
    .dmer-tabs-bar {
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    .dmer-tabs-list {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: #f1f5f9;
        padding: 0.25rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
        max-width: 100%;
    }
    .dmer-benchmark-row {
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
        width: 100%;
    }
    .dmer-benchmark-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.625rem;
        width: 100%;
        flex: 1 1 auto;
    }
    .dmer-selector-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        width: 100%;
    }
    .dmer-selector-controls {
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
        flex: 1 1 auto;
        min-width: 0;
    }
    .dmer-summary-box {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: #f8fafc;
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
    }
    .dmer-kpi-grid-5 {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        width: 100%;
    }
    .dmer-charts-row {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
        width: 100%;
    }
    .dmer-bottom-row {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
        width: 100%;
    }
    .dmer-filter-toolbar {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    .dmer-cards-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        width: 100%;
    }
    .dmer-kpi-grid-4 {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        width: 100%;
    }
    .dmer-exec-top3 {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        width: 100%;
    }
    .dmer-compare-header {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
        margin-bottom: 1.25rem;
    }
    .dmer-compare-selects {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.625rem;
        width: 100%;
    }
    .dmer-compare-body {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.5rem;
        align-items: center;
        width: 100%;
    }

    /* Small Tablet >= 640px */
    @media (min-width: 640px) {
        .dmer-header-brand { align-items: center; }
        .dmer-period-form { width: auto; }
        .dmer-benchmark-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dmer-selector-controls { flex-direction: row; align-items: flex-end; }
        .dmer-kpi-grid-5 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dmer-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dmer-kpi-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dmer-compare-selects { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    /* Tablet >= 768px */
    @media (min-width: 768px) {
        .dmer-benchmark-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dmer-kpi-grid-5 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dmer-filter-toolbar { flex-direction: row; align-items: center; justify-content: space-between; }
        .dmer-exec-top3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* Desktop >= 1024px */
    @media (min-width: 1024px) {
        .dmer-header-top {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        .dmer-tabs-bar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        .dmer-benchmark-row {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        .dmer-benchmark-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .dmer-selector-row {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        .dmer-kpi-grid-5 {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .dmer-charts-row {
            grid-template-columns: 5fr 7fr;
        }
        .dmer-bottom-row {
            grid-template-columns: 1fr 1fr;
        }
        .dmer-cards-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .dmer-kpi-grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .dmer-exec-top3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .dmer-compare-header {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        .dmer-compare-selects {
            width: auto;
            min-width: 540px;
        }
        .dmer-compare-body {
            grid-template-columns: 5fr 7fr;
        }
    }

    /* Wide Desktop >= 1280px */
    @media (min-width: 1280px) {
        .dmer-cards-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
</style>

<!-- ==================== DASHBOARD 5 STRATEGI DMER (MARKOM) PER KAMPUS ==================== -->
<div class="dmer-dashboard-wrap">

    <!-- Top Header & Period / Navigation Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-6">
        <div class="dmer-header-top">
            <div class="dmer-header-brand">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900">
                            <?= $canViewAllTabs ? 'DASHBOARD 5 STRATEGI DMER (MARKOM) PER KAMPUS' : 'PROFIL CAPAIAN 5 STRATEGI DMER — KAMPUS ' . esc(strtoupper($userCampusName)) ?>
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            Periode: <?= esc($periodLabel) ?>
                        </span>
                        <?php if ($canViewAllTabs): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <?= esc((string) $totalCampusesCount) ?> Kampus Real Data
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Mode Kampus (Data Real)
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        <?= $canViewAllTabs
                            ? 'Evaluasi Penuh Seluruh Kampus, Komparasi Head-to-Head & Target Benchmark (Data Real Database)'
                            : 'Monitoring Capaian 5 Strategi DMER, Evaluasi Prioritas & Simulasi Target Kampus Anda' ?>
                    </p>
                </div>
            </div>

            <!-- Real Data Period Filter Form -->
            <form method="get" action="<?= site_url('dashboard') ?>" class="dmer-period-form shrink-0">
                <div class="flex items-center gap-1.5">
                    <label for="dmer_year" class="text-[11px] font-bold uppercase tracking-wider text-slate-500 pl-1">Tahun:</label>
                    <select id="dmer_year" name="dmer_year" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-800 text-xs font-semibold rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $selectedYear === 'all' ? 'selected' : '' ?>>Semua Tahun</option>
                        <?php foreach ($yearOptions as $yr): ?>
                            <option value="<?= esc((string) $yr) ?>" <?= $selectedYear === (string) $yr ? 'selected' : '' ?>><?= esc((string) $yr) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <label for="dmer_month" class="text-[11px] font-bold uppercase tracking-wider text-slate-500 pl-1">Bulan:</label>
                    <select id="dmer_month" name="dmer_month" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-800 text-xs font-semibold rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $selectedMonth === 'all' ? 'selected' : '' ?>>Semua Bulan</option>
                        <?php foreach ($monthOptions as $mKey => $mName): ?>
                            <option value="<?= esc((string) $mKey) ?>" <?= $selectedMonth === (string) $mKey ? 'selected' : '' ?>><?= esc((string) $mName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selectedYear !== '2026' || $selectedMonth !== 'all'): ?>
                    <a href="<?= site_url('dashboard') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($canViewAllTabs): ?>
            <!-- 5-Tab Navigation (ONLY FOR SUPER ADMIN & ADMIN) -->
            <div class="dmer-tabs-bar">
                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    Akses Penuh Super Admin / Admin: Pilih Mode Tampilan Analitik
                </span>
                <div class="dmer-tabs-list custom-scrollbar">
                    <button type="button" onclick="switchTab('campus')" id="tab-campus" class="tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-white text-blue-600 shadow-sm">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Profil Per Kampus
                    </button>
                    <button type="button" onclick="switchTab('cards')" id="tab-cards" class="tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-600 hover:text-slate-900">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Grid Kampus
                    </button>
                    <button type="button" onclick="switchTab('matrix')" id="tab-matrix" class="tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-600 hover:text-slate-900">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Tabel & Heatmap
                    </button>
                    <button type="button" onclick="switchTab('executive')" id="tab-executive" class="tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-600 hover:text-slate-900">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        Ringkasan Eksekutif
                    </button>
                    <button type="button" onclick="switchTab('compare')" id="tab-compare" class="tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-600 hover:text-slate-900">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Komparasi Kampus
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Legend & Target Reference Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <div class="dmer-benchmark-row">
            <div class="flex items-center gap-2 shrink-0">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Benchmark Target 5 Strategi DMER:</span>
            </div>
            <div class="dmer-benchmark-grid">
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70">
                    <div class="min-w-0 pr-2">
                        <div class="text-[11px] font-bold text-blue-700">1. OCTH</div>
                        <div class="text-[10px] text-slate-500 truncate">One Campus Two Openhouse</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-blue-100 text-blue-800 shrink-0">2 Keg</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70">
                    <div class="min-w-0 pr-2">
                        <div class="text-[11px] font-bold text-indigo-700">2. OCTE</div>
                        <div class="text-[10px] text-slate-500 truncate">One Campus Two Event</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-indigo-100 text-indigo-800 shrink-0">2 Event</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70">
                    <div class="min-w-0 pr-2">
                        <div class="text-[11px] font-bold text-violet-700">3. OCTS</div>
                        <div class="text-[10px] text-slate-500 truncate">One Campus Sixty School</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-violet-100 text-violet-800 shrink-0">60 Sek</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70">
                    <div class="min-w-0 pr-2">
                        <div class="text-[11px] font-bold text-amber-700">4. OCFO</div>
                        <div class="text-[10px] text-slate-500 truncate">One Campus Five OJT</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-amber-100 text-amber-800 shrink-0">5 OJT</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70">
                    <div class="min-w-0 pr-2">
                        <div class="text-[11px] font-bold text-emerald-700">5. OCFS</div>
                        <div class="text-[10px] text-slate-500 truncate">One Campus Five Spanduk</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-emerald-100 text-emerald-800 shrink-0">5 Spd</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== VIEW 1: PROFIL PER KAMPUS (DEFAULT FOR ALL USERS) ==================== -->
    <div id="view-campus" class="space-y-5">

        <!-- Campus Selector & Profile Header Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-6">
            <div class="dmer-selector-row">

                <?php if ($canViewAllTabs): ?>
                    <!-- Left: Selector Controls (Admin / Super Admin) -->
                    <div class="dmer-selector-controls">
                        <div class="flex-1 min-w-0">
                            <label for="campusSelect" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                Pilih Kampus untuk Dianalisis (<?= esc((string) $totalCampusesCount) ?> Kampus)
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="navigateCampus(-1)" title="Kampus Sebelumnya" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <select id="campusSelect" onchange="selectCampus(this.value)" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm font-semibold rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                </select>
                                <button type="button" onclick="navigateCampus(1)" title="Kampus Berikutnya" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Quick Filter Pills -->
                        <div class="sm:border-l sm:border-slate-200 sm:pl-4 shrink-0">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Filter Cepat Dropdown</span>
                            <select id="quickCampusFilter" onchange="filterCampusDropdown(this.value)" class="w-full sm:w-auto bg-white border border-slate-200 text-slate-700 text-xs font-medium rounded-xl p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">Semua Kampus (<?= esc((string) $totalCampusesCount) ?>)</option>
                                <option value="has_achieved">Minimal 1 Strategi Tercapai</option>
                                <option value="zero_achieved">Belum Ada Strategi Tercapai (0/5)</option>
                                <option value="octh_pass">Lulus OCTH (&ge; 2)</option>
                                <option value="octe_pass">Lulus OCTE (&ge; 2)</option>
                                <option value="octs_pass">Lulus OCTS (&ge; 60)</option>
                                <option value="ocfo_pass">Lulus OCFO (&ge; 5)</option>
                                <option value="ocfs_pass">Lulus OCFS (&ge; 5)</option>
                            </select>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Left: Locked Campus Profile Info (For User Per Kampus) -->
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 font-black text-lg shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-blue-600">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                Profil Resmi Kampus Anda
                            </span>
                            <h2 class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 truncate">Kampus <?= esc($userCampusName) ?></h2>
                            <p class="text-xs text-slate-500">Data real-time dari laporan Event, Sekolah (OCTS), OJT (OCFO), dan Spanduk (OCFS) kampus Anda.</p>
                        </div>
                        <select id="campusSelect" class="hidden">
                            <option value="<?= esc((string) $defaultCampusNo) ?>" selected><?= esc($userCampusName) ?></option>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Right: Selected Campus Summary Badge -->
                <div class="dmer-summary-box shrink-0">
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Kampus Terpilih</div>
                        <div id="profileCampusName" class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">-</div>
                        <div id="profileCampusRank" class="text-xs text-slate-500 mt-0.5">Peringkat - dari <?= esc((string) $totalCampusesCount) ?> Kampus</div>
                    </div>
                    <div class="h-10 w-px bg-slate-200 hidden sm:block"></div>
                    <div class="flex items-center gap-4">
                        <div class="text-center px-1">
                            <div class="text-[11px] font-bold uppercase text-slate-400">Target Lulus</div>
                            <div id="profileStrategiesMet" class="text-lg font-extrabold text-blue-600">0 / 5</div>
                        </div>
                        <div class="h-10 w-px bg-slate-200"></div>
                        <div class="text-center px-1">
                            <div class="text-[11px] font-bold uppercase text-slate-400">Skor Capaian</div>
                            <div id="profileOverallScore" class="text-lg font-extrabold text-emerald-600">0%</div>
                        </div>
                        <div id="profileStatusBadge" class="px-3 py-1.5 rounded-xl text-xs font-bold">
                            -
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 5 Strategy KPI Cards for Selected Campus -->
        <div id="campusStrategyCards" class="dmer-kpi-grid-5">
            <!-- Populated by JS -->
        </div>

        <!-- Charts Row -->
        <div class="dmer-charts-row">

            <!-- Radar Chart: 5 Strategy Balance -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold text-slate-800">Peta Keseimbangan 5 Strategi</h3>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600">Skala 0% - 100%+ (Cap 150%)</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Perbandingan persentase ketercapaian kampus terhadap target (100%) dan rata-rata nasional.</p>
                </div>
                <div class="relative h-64 sm:h-72 w-full flex items-center justify-center">
                    <canvas id="campusRadarChart"></canvas>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <span>Garis Hijau: Target Ideal (100%)</span>
                    <span>Garis Abu: Rata-rata <?= esc((string) $totalCampusesCount) ?> Kampus</span>
                </div>
            </div>

            <!-- Bar Chart: Actual vs Target & Gap -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold text-slate-800">Realisasi Aktual vs Target per Strategi</h3>
                        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-lg">
                            <button type="button" onclick="setCampusBarMode('actual')" id="btnBarActual" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-white text-slate-800 shadow-sm">Angka Aktual</button>
                            <button type="button" onclick="setCampusBarMode('percent')" id="btnBarPercent" class="px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600">Persentase (%)</button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Visualisasi volume kegiatan aktual dibandingkan dengan standar target masing-masing strategi.</p>
                </div>
                <div class="relative h-64 sm:h-72 w-full">
                    <canvas id="campusBarChart"></canvas>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Tercapai (&ge; Target)</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Belum Tercapai (&lt; Target)</span>
                    </div>
                    <span id="barChartSubnote">Menampilkan angka aktual kegiatan</span>
                </div>
            </div>

        </div>

        <!-- Automated Recommendations & Interactive Target Simulator -->
        <div class="dmer-bottom-row">

            <!-- Left: Diagnostic & Action Plan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5 min-w-0">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="p-2 rounded-lg bg-amber-50 text-amber-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Evaluasi & Rekomendasi Prioritas Kampus</h3>
                        <p class="text-xs text-slate-500">Analisis otomatis berdasarkan gap (kekurangan) terhadap target 5 strategi.</p>
                    </div>
                </div>
                <div id="campusEvaluationList" class="space-y-2.5 mt-4">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Right: Interactive What-If Simulator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5 min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-lg bg-blue-50 text-blue-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Simulasi Penambahan Capaian (What-If Planner)</h3>
                            <p class="text-xs text-slate-500">Geser slider untuk mensimulasikan dampak penambahan kegiatan terhadap skor kampus.</p>
                        </div>
                    </div>
                    <button type="button" onclick="resetSimulator()" class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-2.5 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                        Reset Ke Aktual
                    </button>
                </div>

                <div id="simulatorControls" class="space-y-3 mt-4">
                    <!-- Populated by JS -->
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 bg-slate-50 px-4 py-2.5 rounded-xl">
                    <div>
                        <span class="text-xs font-medium text-slate-500">Proyeksi Skor Baru:</span>
                        <span id="simProjectedScore" class="ml-1.5 text-sm font-extrabold text-blue-600">0%</span>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500">Strategi Lulus:</span>
                        <span id="simProjectedPassed" class="ml-1.5 text-sm font-extrabold text-emerald-600">0 / 5</span>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500">Proyeksi Peringkat:</span>
                        <span id="simProjectedRank" class="ml-1.5 text-sm font-extrabold text-indigo-600">#1</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <?php if ($canViewAllTabs): ?>
        <!-- ==================== VIEW 2: GRID SEMUA KAMPUS (MINI CARDS) ==================== -->
        <div id="view-cards" class="hidden space-y-5">

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 dmer-filter-toolbar">
                <div class="relative flex-1 min-w-0">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="cardSearchInput" oninput="renderCampusGrid()" placeholder="Cari nama kampus (contoh: Cyber, Bekasi, Bogor)..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select id="cardFilterGroup" onchange="renderCampusGrid()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2">
                        <option value="all">Semua Kelompok</option>
                        <option value="Mandiri">Kampus Mandiri (Top 15)</option>
                        <option value="Non-Mandiri">Kampus Non-Mandiri (Bottom 18)</option>
                    </select>
                    <select id="cardFilterStatus" onchange="renderCampusGrid()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2">
                        <option value="all">Semua Status Capaian</option>
                        <option value="achieved_any">Minimal 1 Target Tercapai</option>
                        <option value="achieved_2plus">Minimal 2 Target Tercapai</option>
                        <option value="achieved_none">Belum Ada Target Tercapai (0/5)</option>
                    </select>
                    <select id="cardSortBy" onchange="renderCampusGrid()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2">
                        <option value="score_desc">Urutkan: Skor Tertinggi</option>
                        <option value="score_asc">Urutkan: Skor Terendah</option>
                        <option value="passed_desc">Urutkan: Jumlah Target Lulus</option>
                        <option value="volume_desc">Urutkan: Total Volume Aktivitas</option>
                        <option value="name_asc">Urutkan: Nama Kampus (A-Z)</option>
                        <option value="no_asc">Urutkan: Nomor Urut Asli (1-<?= esc((string) $totalCampusesCount) ?>)</option>
                    </select>
                </div>
            </div>

            <!-- Grid Container -->
            <div id="allCampusesGrid" class="dmer-cards-grid">
                <!-- Populated by JS -->
            </div>

        </div>

        <!-- ==================== VIEW 3: TABEL & HEATMAP MATRIX ==================== -->
        <div id="view-matrix" class="hidden space-y-5">

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5">
                <div class="dmer-filter-toolbar mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tabel Matriks & Heatmap 5 Strategi DMER (MARKOM)</h3>
                        <p class="text-xs text-slate-500">Sel berwarna <span class="text-emerald-700 font-semibold">Hijau</span> menandakan target tercapai (&ge; Target), sedangkan <span class="text-rose-700 font-semibold">Merah Muda</span> menandakan belum mencapai target.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" id="tableSearchInput" oninput="renderMatrixTable()" placeholder="Cari kampus di tabel..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select id="tableStrategyFilter" onchange="renderMatrixTable()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2">
                            <option value="all">Semua Kampus (<?= esc((string) $totalCampusesCount) ?>)</option>
                            <option value="octh_ok">Lulus OCTH (&ge;2)</option>
                            <option value="octh_gap">Belum Lulus OCTH (&lt;2)</option>
                            <option value="octe_ok">Lulus OCTE (&ge;2)</option>
                            <option value="octe_gap">Belum Lulus OCTE (&lt;2)</option>
                            <option value="octs_ok">Lulus OCTS (&ge;60)</option>
                            <option value="octs_gap">Belum Lulus OCTS (&lt;60)</option>
                            <option value="ocfo_ok">Lulus OCFO (&ge;5)</option>
                            <option value="ocfo_gap">Belum Lulus OCFO (&lt;5)</option>
                            <option value="ocfs_ok">Lulus OCFS (&ge;5)</option>
                            <option value="ocfs_gap">Belum Lulus OCFS (&lt;5)</option>
                        </select>
                        <button type="button" onclick="downloadCSV()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh CSV
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse min-w-[780px]">
                        <thead>
                            <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider">
                                <th class="py-3 px-3 font-bold cursor-pointer hover:bg-slate-700" onclick="sortMatrix('no')">No &#8597;</th>
                                <th class="py-3 px-4 font-bold cursor-pointer hover:bg-slate-700" onclick="sortMatrix('kampus')">Kampus &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center">Kelompok</th>
                                <th class="py-3 px-3 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('octh')">OCTH<br><span class="text-[9px] font-normal text-slate-300">(Target: 2)</span> &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('octe')">OCTE<br><span class="text-[9px] font-normal text-slate-300">(Target: 2)</span> &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('octs')">OCTS<br><span class="text-[9px] font-normal text-slate-300">(Target: 60)</span> &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('ocfo')">OCFO<br><span class="text-[9px] font-normal text-slate-300">(Target: 5)</span> &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('ocfs')">OCFS<br><span class="text-[9px] font-normal text-slate-300">(Target: 5)</span> &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('passed')">Strategi Lulus &#8597;</th>
                                <th class="py-3 px-4 font-bold text-center cursor-pointer hover:bg-slate-700" onclick="sortMatrix('score')">Skor Capaian &#8597;</th>
                                <th class="py-3 px-3 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="matrixTableBody" class="divide-y divide-slate-200 text-xs">
                            <!-- Populated by JS -->
                        </tbody>
                        <tfoot id="matrixTableFoot" class="bg-slate-100 font-bold text-xs text-slate-800 border-t-2 border-slate-300">
                            <!-- Populated by JS -->
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        <!-- ==================== VIEW 4: RINGKASAN EKSEKUTIF ==================== -->
        <div id="view-executive" class="hidden space-y-5">

            <!-- Macro KPIs -->
            <div class="dmer-kpi-grid-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold uppercase text-slate-400">Rata-rata Skor Nasional</div>
                    <div id="execAvgScore" class="text-2xl font-black text-slate-900 mt-1">0%</div>
                    <div class="text-xs text-slate-500 mt-1">Rata-rata pencapaian 5 strategi di <?= esc((string) $totalCampusesCount) ?> kampus</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold uppercase text-slate-400">Strategi Paling Banyak Lulus</div>
                    <div id="execMostPassedStrat" class="text-2xl font-black text-indigo-600 mt-1">-</div>
                    <div id="execMostPassedDesc" class="text-xs text-slate-500 mt-1">-</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold uppercase text-slate-400">Strategi Paling Kritis</div>
                    <div id="execLeastPassedStrat" class="text-2xl font-black text-rose-600 mt-1">-</div>
                    <div id="execLeastPassedDesc" class="text-xs text-slate-500 mt-1">-</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold uppercase text-slate-400">Kampus 0 Target Tercapai</div>
                    <div id="execZeroTargetCount" class="text-2xl font-black text-amber-600 mt-1">0 Kampus</div>
                    <div class="text-xs text-slate-500 mt-1">Membutuhkan pendampingan dan supervisi prioritas</div>
                </div>
            </div>

            <!-- Executive Charts Row -->
            <div class="dmer-bottom-row">
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm min-w-0">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Tingkat Kelulusan Kampus per Strategi (Pass Rate)</h3>
                    <p class="text-xs text-slate-500 mb-4">Jumlah kampus yang berhasil mencapai target vs belum mencapai target dari total <?= esc((string) $totalCampusesCount) ?> kampus.</p>
                    <div class="relative h-64 sm:h-72 w-full">
                        <canvas id="execPassRateChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm min-w-0">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Distribusi Jumlah Strategi yang Dicapai Kampus</h3>
                    <p class="text-xs text-slate-500 mb-4">Pengelompokan kampus berdasarkan berapa banyak strategi yang berhasil dilampaui.</p>
                    <div class="relative h-64 sm:h-72 w-full">
                        <canvas id="execDistChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Performers per Strategy -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-1">Peringkat Teratas (Top Performers) & Kampus Perhatian Khusus</h3>
                <p class="text-xs text-slate-500 mb-4">Ringkasan kampus dengan capaian terbaik secara keseluruhan maupun juara di masing-masing strategi (Data Real).</p>
                <div class="dmer-exec-top3">
                    <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200">
                        <div class="text-xs font-extrabold uppercase text-emerald-800 mb-2">Kampus Paling Seimbang (Top Overall)</div>
                        <ul id="topBalancedList" class="space-y-1.5 text-xs text-slate-700"></ul>
                    </div>
                    <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200">
                        <div class="text-xs font-extrabold uppercase text-blue-800 mb-2">Rekor Volume Tertinggi per Strategi</div>
                        <ul id="topVolumeRecordsList" class="space-y-1.5 text-xs text-slate-700"></ul>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200">
                        <div class="text-xs font-extrabold uppercase text-rose-800 mb-2">Rekomendasi Kebijakan Manajemen</div>
                        <ul class="space-y-1.5 text-xs text-slate-700 list-disc pl-4">
                            <li><strong>Fokus Nasional OCTH & OCFO:</strong> Perkuat kolaborasi kunjungan sekolah dengan paket Openhouse & rekrutmen siswa OJT.</li>
                            <li><strong>Replikasi Best Practice:</strong> Jadikan kampus berperingkat tertinggi sebagai mentor silang bagi kampus yang belum mencapai target.</li>
                            <li><strong>Monitoring Real-Time:</strong> Evaluasi capaian bulanan melalui filter Tahun & Bulan di atas untuk mengawal progres setiap kampus.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- ==================== VIEW 5: KOMPARASI KAMPUS (HEAD TO HEAD) ==================== -->
        <div id="view-compare" class="hidden space-y-5">

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5">
                <div class="dmer-compare-header">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Komparasi Head-to-Head Antar Kampus</h3>
                        <p class="text-xs text-slate-500">Bandingkan hingga 3 kampus sekaligus untuk melihat kekuatan dan kelemahan relatif tiap kampus.</p>
                    </div>
                    <div class="dmer-compare-selects">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-blue-600 mb-1">Kampus A (Biru)</label>
                            <select id="compSelect1" onchange="renderComparison()" class="w-full bg-blue-50/50 border border-blue-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2"></select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-emerald-600 mb-1">Kampus B (Hijau)</label>
                            <select id="compSelect2" onchange="renderComparison()" class="w-full bg-emerald-50/50 border border-emerald-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2"></select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-amber-600 mb-1">Kampus C (Oranye)</label>
                            <select id="compSelect3" onchange="renderComparison()" class="w-full bg-amber-50/50 border border-amber-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2"></select>
                        </div>
                    </div>
                </div>

                <div class="dmer-compare-body">
                    <div class="relative h-72 sm:h-80 w-full flex items-center justify-center min-w-0">
                        <canvas id="compareRadarChart"></canvas>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar min-w-0">
                        <table class="w-full text-left border-collapse rounded-xl overflow-hidden border border-slate-200 min-w-[520px]">
                            <thead>
                                <tr class="bg-slate-800 text-white text-xs">
                                    <th class="py-3 px-4">Indikator / Strategi</th>
                                    <th class="py-3 px-3 text-center">Target</th>
                                    <th id="compHead1" class="py-3 px-3 text-center bg-blue-900/40">Kampus A</th>
                                    <th id="compHead2" class="py-3 px-3 text-center bg-emerald-900/40">Kampus B</th>
                                    <th id="compHead3" class="py-3 px-3 text-center bg-amber-900/40">Kampus C</th>
                                </tr>
                            </thead>
                            <tbody id="compareTableBody" class="divide-y divide-slate-200 text-xs">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<!-- Chart.js Local Asset -->
<script src="<?= base_url('assets/js/lib/chart.umd.min.js') ?>"></script>
<script>
// ==================== 1. REAL DATASET & TARGET CONFIG ====================
const TARGETS = {
    octh: { key: 'octh', code: 'OCTH', name: 'One Campus Two Openhouse', target: 2, unit: 'Kegiatan', color: '#2563eb', lightBg: 'bg-blue-50', textCol: 'text-blue-700', borderCol: 'border-blue-200' },
    octe: { key: 'octe', code: 'OCTE', name: 'One Campus Two Event', target: 2, unit: 'Event', color: '#4f46e5', lightBg: 'bg-indigo-50', textCol: 'text-indigo-700', borderCol: 'border-indigo-200' },
    octs: { key: 'octs', code: 'OCTS', name: 'One Campus Sixty School', target: 60, unit: 'Sekolah', color: '#7c3aed', lightBg: 'bg-violet-50', textCol: 'text-violet-700', borderCol: 'border-violet-200' },
    ocfo: { key: 'ocfo', code: 'OCFO', name: 'One Campus Five OJT', target: 5, unit: 'Siswa OJT', color: '#d97706', lightBg: 'bg-amber-50', textCol: 'text-amber-700', borderCol: 'border-amber-200' },
    ocfs: { key: 'ocfs', code: 'OCFS', name: 'One Campus Five Spanduk', target: 5, unit: 'Spanduk', color: '#059669', lightBg: 'bg-emerald-50', textCol: 'text-emerald-700', borderCol: 'border-emerald-200' }
};

const STRATEGY_KEYS = ['octh', 'octe', 'octs', 'ocfo', 'ocfs'];
const CAN_VIEW_ALL_TABS = <?= json_encode($canViewAllTabs) ?>;
const DEFAULT_CAMPUS_NO = <?= json_encode($defaultCampusNo) ?>;
const RAW_CAMPUS_DATA = <?= json_encode($dmerCampusesData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

// ==================== 2. DATA ENRICHMENT ====================
const CAMPUS_DATA = RAW_CAMPUS_DATA.map(row => {
    const group = row.no <= 15 ? 'Mandiri' : 'Non-Mandiri';
    const pct = {};
    const cappedPct = {};
    const met = {};
    let passedCount = 0;
    let sumCappedPct = 0;

    STRATEGY_KEYS.forEach(k => {
        const t = TARGETS[k].target;
        const val = Number(row[k] || 0);
        const rawP = (val / t) * 100;
        const capP = Math.min(rawP, 100);
        pct[k] = Math.round(rawP * 10) / 10;
        cappedPct[k] = Math.round(capP * 10) / 10;
        met[k] = val >= t;
        if (val >= t) passedCount++;
        sumCappedPct += capP;
    });

    const overallScore = Math.round((sumCappedPct / 5) * 10) / 10;
    const totalVolume = Number(row.octh || 0) + Number(row.octe || 0) + Number(row.octs || 0) + Number(row.ocfo || 0) + Number(row.ocfs || 0);

    return {
        ...row,
        group,
        pct,
        cappedPct,
        met,
        passedCount,
        overallScore,
        totalVolume
    };
});

// Compute National Rank based on overallScore, then passedCount, then totalVolume
const rankedOrder = [...CAMPUS_DATA].sort((a, b) => {
    if (b.overallScore !== a.overallScore) return b.overallScore - a.overallScore;
    if (b.passedCount !== a.passedCount) return b.passedCount - a.passedCount;
    return b.totalVolume - a.totalVolume;
});
rankedOrder.forEach((item, idx) => {
    const found = CAMPUS_DATA.find(c => c.no === item.no);
    if (found) found.rank = idx + 1;
});

// National average percentage per strategy (for radar reference)
const NATIONAL_AVG_PCT = {};
STRATEGY_KEYS.forEach(k => {
    const sum = CAMPUS_DATA.reduce((acc, c) => acc + Math.min(c.pct[k], 150), 0);
    NATIONAL_AVG_PCT[k] = CAMPUS_DATA.length > 0 ? Math.round((sum / CAMPUS_DATA.length) * 10) / 10 : 0;
});

// ==================== 3. STATE MANAGEMENT ====================
let currentTab = 'campus';
let selectedCampusNo = DEFAULT_CAMPUS_NO;
let campusBarMode = 'actual';
let simState = { octh: 0, octe: 0, octs: 0, ocfo: 0, ocfs: 0 };
let matrixSort = { key: 'no', asc: true };

// Chart instances
let radarChartInstance = null;
let barChartInstance = null;
let execPassChartInstance = null;
let execDistChartInstance = null;
let compareRadarInstance = null;

// ==================== 4. NAVIGATION & INITIALIZATION ====================
function switchTab(tab) {
    if (!CAN_VIEW_ALL_TABS && tab !== 'campus') {
        return;
    }
    currentTab = tab;
    ['campus', 'cards', 'matrix', 'executive', 'compare'].forEach(t => {
        const viewEl = document.getElementById(`view-${t}`);
        const btnEl = document.getElementById(`tab-${t}`);
        if (!viewEl || !btnEl) return;
        if (t === tab) {
            viewEl.classList.remove('hidden');
            btnEl.className = 'tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-white text-blue-600 shadow-sm';
        } else {
            viewEl.classList.add('hidden');
            btnEl.className = 'tab-btn whitespace-nowrap flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-600 hover:text-slate-900';
        }
    });

    if (tab === 'campus') updateCampusProfile();
    if (tab === 'cards') renderCampusGrid();
    if (tab === 'matrix') renderMatrixTable();
    if (tab === 'executive') renderExecutiveSummary();
    if (tab === 'compare') renderComparison();
}

function populateCampusDropdown(filterMode = 'all') {
    const select = document.getElementById('campusSelect');
    if (!select || !CAN_VIEW_ALL_TABS) return;

    let filtered = [...CAMPUS_DATA];
    if (filterMode === 'has_achieved') filtered = filtered.filter(c => c.passedCount >= 1);
    if (filterMode === 'zero_achieved') filtered = filtered.filter(c => c.passedCount === 0);
    if (filterMode === 'octh_pass') filtered = filtered.filter(c => c.met.octh);
    if (filterMode === 'octe_pass') filtered = filtered.filter(c => c.met.octe);
    if (filterMode === 'octs_pass') filtered = filtered.filter(c => c.met.octs);
    if (filterMode === 'ocfo_pass') filtered = filtered.filter(c => c.met.ocfo);
    if (filterMode === 'ocfs_pass') filtered = filtered.filter(c => c.met.ocfs);

    select.innerHTML = '';
    if (filtered.length === 0) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Tidak ada kampus yang memenuhi kriteria filter ini';
        select.appendChild(opt);
        return;
    }

    filtered.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.no;
        opt.textContent = `${c.no}. ${c.kampus} — (Lulus: ${c.passedCount}/5 | Skor: ${c.overallScore}%)`;
        select.appendChild(opt);
    });

    if (filtered.some(c => c.no === Number(selectedCampusNo))) {
        select.value = selectedCampusNo;
    } else {
        selectedCampusNo = filtered[0].no;
        select.value = selectedCampusNo;
        updateCampusProfile();
    }
}

function filterCampusDropdown(mode) {
    populateCampusDropdown(mode);
}

function selectCampus(no) {
    if (!no) return;
    selectedCampusNo = Number(no);
    const select = document.getElementById('campusSelect');
    if (select && select.value != selectedCampusNo) {
        const filterEl = document.getElementById('quickCampusFilter');
        if (filterEl) filterEl.value = 'all';
        populateCampusDropdown('all');
        select.value = selectedCampusNo;
    }
    updateCampusProfile();
}

function navigateCampus(direction) {
    if (!CAN_VIEW_ALL_TABS) return;
    const select = document.getElementById('campusSelect');
    const options = Array.from(select.options).map(o => Number(o.value)).filter(Boolean);
    if (options.length === 0) return;
    const currentIndex = options.indexOf(Number(selectedCampusNo));
    let nextIndex = currentIndex + direction;
    if (nextIndex < 0) nextIndex = options.length - 1;
    if (nextIndex >= options.length) nextIndex = 0;
    selectedCampusNo = options[nextIndex];
    select.value = selectedCampusNo;
    updateCampusProfile();
}

function jumpToCampusProfile(no) {
    selectedCampusNo = Number(no);
    const filterEl = document.getElementById('quickCampusFilter');
    if (filterEl) filterEl.value = 'all';
    populateCampusDropdown('all');
    const select = document.getElementById('campusSelect');
    if (select) select.value = selectedCampusNo;
    switchTab('campus');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ==================== 5. VIEW 1: PROFIL PER KAMPUS ====================
function updateCampusProfile() {
    const campus = CAMPUS_DATA.find(c => c.no === Number(selectedCampusNo)) || CAMPUS_DATA[0];
    if (!campus) return;

    // Header summary
    document.getElementById('profileCampusName').textContent = `Kampus ${campus.kampus}`;
    document.getElementById('profileCampusRank').textContent = `Peringkat #${campus.rank} dari ${CAMPUS_DATA.length} Kampus • Kelompok ${campus.group}`;
    document.getElementById('profileStrategiesMet').textContent = `${campus.passedCount} / 5`;
    document.getElementById('profileOverallScore').textContent = `${campus.overallScore}%`;

    const badge = document.getElementById('profileStatusBadge');
    if (campus.passedCount >= 3) {
        badge.className = 'px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
        badge.textContent = 'Performa Baik';
    } else if (campus.passedCount >= 1) {
        badge.className = 'px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300';
        badge.textContent = 'Perlu Peningkatan';
    } else {
        badge.className = 'px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300';
        badge.textContent = 'Prioritas Pembinaan';
    }

    // 5 Strategy Cards
    const cardsContainer = document.getElementById('campusStrategyCards');
    cardsContainer.innerHTML = STRATEGY_KEYS.map(k => {
        const cfg = TARGETS[k];
        const actual = campus[k];
        const target = cfg.target;
        const pct = campus.pct[k];
        const barWidth = Math.min(pct, 100);
        const isMet = campus.met[k];
        const diff = actual - target;
        const diffText = diff >= 0 ? `Surplus +${diff} ${cfg.unit}` : `Kurang ${Math.abs(diff)} ${cfg.unit}`;

        return `
        <div class="bg-white rounded-2xl border ${isMet ? 'border-emerald-300 ring-1 ring-emerald-500/20' : 'border-slate-200/80'} shadow-sm p-4 flex flex-col justify-between transition-all hover:shadow-md min-w-0">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold ${cfg.lightBg} ${cfg.textCol} border ${cfg.borderCol}">${cfg.code}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold ${isMet ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700'}">
                        ${isMet ? '✓ TERCAPAI' : '✗ BELUM'}
                    </span>
                </div>
                <div class="text-xs font-semibold text-slate-600 truncate" title="${cfg.name}">${cfg.name}</div>
                <div class="mt-3 flex items-baseline justify-between gap-2">
                    <div>
                        <span class="text-2xl sm:text-3xl font-black ${isMet ? 'text-emerald-600' : 'text-slate-900'}">${actual}</span>
                        <span class="text-xs font-semibold text-slate-400"> / ${target} ${cfg.unit}</span>
                    </div>
                    <span class="text-sm font-extrabold ${isMet ? 'text-emerald-600' : 'text-slate-600'}">${pct}%</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ${isMet ? 'bg-emerald-500' : (pct > 0 ? 'bg-amber-500' : 'bg-rose-400')}" style="width: ${barWidth}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px]">
                    <span class="${isMet ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-medium'}">${diffText}</span>
                    <span class="text-slate-400">Target: ${target}</span>
                </div>
            </div>
        </div>
        `;
    }).join('');

    // Render Radar & Bar Charts
    renderCampusRadarChart(campus);
    renderCampusBarChart(campus);

    // Render Automated Diagnostic & Recommendations
    renderCampusEvaluation(campus);

    // Initialize Simulator with current campus values
    simState = {
        octh: campus.octh,
        octe: campus.octe,
        octs: campus.octs,
        ocfo: campus.ocfo,
        ocfs: campus.ocfs
    };
    renderSimulatorControls(campus);
}

function renderCampusRadarChart(campus) {
    const ctx = document.getElementById('campusRadarChart');
    if (!ctx || typeof Chart === 'undefined') return;
    const campusValues = STRATEGY_KEYS.map(k => Math.min(campus.pct[k], 150));
    const targetLine = STRATEGY_KEYS.map(() => 100);
    const natAvgLine = STRATEGY_KEYS.map(k => NATIONAL_AVG_PCT[k]);

    if (radarChartInstance) radarChartInstance.destroy();
    radarChartInstance = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['OCTH (Target 2)', 'OCTE (Target 2)', 'OCTS (Target 60)', 'OCFO (Target 5)', 'OCFS (Target 5)'],
            datasets: [
                {
                    label: `Kampus ${campus.kampus} (%)`,
                    data: campusValues,
                    backgroundColor: 'rgba(37, 99, 235, 0.22)',
                    borderColor: '#2563eb',
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    borderWidth: 2.5
                },
                {
                    label: 'Standar Target (100%)',
                    data: targetLine,
                    backgroundColor: 'rgba(16, 185, 129, 0.04)',
                    borderColor: '#10b981',
                    borderDash: [5, 5],
                    pointRadius: 2,
                    borderWidth: 1.5
                },
                {
                    label: 'Rata-rata Nasional (%)',
                    data: natAvgLine,
                    backgroundColor: 'rgba(100, 116, 139, 0.08)',
                    borderColor: '#94a3b8',
                    pointRadius: 2,
                    borderWidth: 1.5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 150,
                    ticks: {
                        stepSize: 50,
                        callback: v => v + '%',
                        font: { size: 10 }
                    },
                    pointLabels: {
                        font: { size: 11, weight: '600' },
                        color: '#334155'
                    }
                }
            },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
}

function setCampusBarMode(mode) {
    campusBarMode = mode;
    document.getElementById('btnBarActual').className = mode === 'actual'
        ? 'px-2.5 py-1 rounded-md text-xs font-semibold bg-white text-slate-800 shadow-sm'
        : 'px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600';
    document.getElementById('btnBarPercent').className = mode === 'percent'
        ? 'px-2.5 py-1 rounded-md text-xs font-semibold bg-white text-slate-800 shadow-sm'
        : 'px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600';
    document.getElementById('barChartSubnote').textContent = mode === 'actual'
        ? 'Menampilkan angka aktual kegiatan vs target'
        : 'Menampilkan persentase ketercapaian terhadap target (100%)';

    const campus = CAMPUS_DATA.find(c => c.no === Number(selectedCampusNo)) || CAMPUS_DATA[0];
    if (campus) renderCampusBarChart(campus);
}

function renderCampusBarChart(campus) {
    const ctx = document.getElementById('campusBarChart');
    if (!ctx || typeof Chart === 'undefined') return;
    if (barChartInstance) barChartInstance.destroy();

    const labels = STRATEGY_KEYS.map(k => `${TARGETS[k].code} (${TARGETS[k].unit})`);
    const barColors = STRATEGY_KEYS.map(k => campus.met[k] ? '#10b981' : '#f43f5e');

    if (campusBarMode === 'actual') {
        const actualData = STRATEGY_KEYS.map(k => campus[k]);
        const targetData = STRATEGY_KEYS.map(k => TARGETS[k].target);

        barChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: `Realisasi Aktual (${campus.kampus})`,
                        data: actualData,
                        backgroundColor: barColors,
                        borderRadius: 6,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Target Minimal',
                        data: targetData,
                        backgroundColor: '#cbd5e1',
                        borderRadius: 6,
                        barPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    } else {
        const pctData = STRATEGY_KEYS.map(k => campus.pct[k]);
        const targetPct = STRATEGY_KEYS.map(() => 100);

        barChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: `Capaian % (${campus.kampus})`,
                        data: pctData,
                        backgroundColor: barColors,
                        borderRadius: 6,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Target Standar (100%)',
                        data: targetPct,
                        backgroundColor: '#cbd5e1',
                        borderRadius: 6,
                        barPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { callback: v => v + '%' }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.raw}%`
                        }
                    }
                }
            }
        });
    }
}

function renderCampusEvaluation(campus) {
    const container = document.getElementById('campusEvaluationList');
    const items = [];

    STRATEGY_KEYS.forEach(k => {
        const cfg = TARGETS[k];
        const actual = campus[k];
        const gap = cfg.target - actual;
        if (gap <= 0) {
            items.push({
                priority: 3,
                type: 'success',
                title: `${cfg.code} (${cfg.name}) — Target Tercapai!`,
                desc: `Realisasi mencapai ${actual} dari target ${cfg.target} ${cfg.unit} (${campus.pct[k]}%). Pertahankan konsistensi pelaksanaan di periode berikutnya.`
            });
        } else if (actual > 0) {
            items.push({
                priority: 2,
                type: 'warning',
                title: `${cfg.code} (${cfg.name}) — Hampir Tercapai (Kurang ${gap} ${cfg.unit})`,
                desc: `Sudah ada progres ${actual} ${cfg.unit} (${campus.pct[k]}%). Dorong penyelesaian tambahan ${gap} ${cfg.unit} lagi agar mencapai 100%.`
            });
        } else {
            items.push({
                priority: 1,
                type: 'danger',
                title: `${cfg.code} (${cfg.name}) — Belum Ada Aktivitas (0/${cfg.target})`,
                desc: `Prioritas utama! Segera jadwalkan minimal ${cfg.target} ${cfg.unit} untuk mengisi kekosongan capaian pada strategi ${cfg.code}.`
            });
        }
    });

    items.sort((a, b) => a.priority - b.priority);

    container.innerHTML = items.map(item => {
        if (item.type === 'danger') {
            return `
            <div class="p-3 rounded-xl bg-rose-50/80 border border-rose-200 flex items-start gap-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-200 text-rose-800 shrink-0 mt-0.5">PRIORITAS 1</span>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-rose-900">${item.title}</div>
                    <div class="text-[11px] text-rose-700 mt-0.5">${item.desc}</div>
                </div>
            </div>`;
        } else if (item.type === 'warning') {
            return `
            <div class="p-3 rounded-xl bg-amber-50/80 border border-amber-200 flex items-start gap-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-200 text-amber-800 shrink-0 mt-0.5">OPTIMASI</span>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-amber-900">${item.title}</div>
                    <div class="text-[11px] text-amber-800 mt-0.5">${item.desc}</div>
                </div>
            </div>`;
        } else {
            return `
            <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-200 flex items-start gap-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-200 text-emerald-800 shrink-0 mt-0.5">LULUS</span>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-emerald-900">${item.title}</div>
                    <div class="text-[11px] text-emerald-700 mt-0.5">${item.desc}</div>
                </div>
            </div>`;
        }
    }).join('');
}

// ==================== 6. INTERACTIVE SIMULATOR ====================
function renderSimulatorControls(campus) {
    const container = document.getElementById('simulatorControls');
    const maxMap = { octh: 6, octe: 15, octs: 80, ocfo: 12, ocfs: 15 };

    container.innerHTML = STRATEGY_KEYS.map(k => {
        const cfg = TARGETS[k];
        const val = simState[k];
        const maxVal = Math.max(maxMap[k], campus[k] + 5);
        const isMet = val >= cfg.target;

        return `
        <div class="bg-slate-50/80 px-3.5 py-2.5 rounded-xl border border-slate-200/70">
            <div class="flex items-center justify-between text-xs mb-1 gap-2">
                <span class="font-bold text-slate-700">${cfg.code} <span class="font-normal text-slate-400">(Target: ${cfg.target} ${cfg.unit})</span></span>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-[11px] text-slate-400">Aktual: ${campus[k]}</span>
                    <span id="simVal-${k}" class="font-extrabold px-2 py-0.5 rounded ${isMet ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800'}">${val} ${cfg.unit}</span>
                </div>
            </div>
            <input type="range" min="0" max="${maxVal}" value="${val}" oninput="updateSimulation('${k}', this.value)" class="w-full">
        </div>
        `;
    }).join('');

    calculateSimulatedMetrics();
}

function updateSimulation(key, value) {
    simState[key] = Number(value);
    const cfg = TARGETS[key];
    const badge = document.getElementById(`simVal-${key}`);
    if (badge) {
        badge.textContent = `${simState[key]} ${cfg.unit}`;
        badge.className = `font-extrabold px-2 py-0.5 rounded ${simState[key] >= cfg.target ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800'}`;
    }
    calculateSimulatedMetrics();
}

function calculateSimulatedMetrics() {
    let passed = 0;
    let sumCap = 0;
    let totalVol = 0;

    STRATEGY_KEYS.forEach(k => {
        const val = simState[k];
        const t = TARGETS[k].target;
        if (val >= t) passed++;
        sumCap += Math.min((val / t) * 100, 100);
        totalVol += val;
    });

    const newScore = Math.round((sumCap / 5) * 10) / 10;

    // Calculate projected rank among all campuses
    const tempScores = CAMPUS_DATA.map(c => {
        if (c.no === Number(selectedCampusNo)) {
            return { no: c.no, overallScore: newScore, passedCount: passed, totalVolume: totalVol };
        }
        return { no: c.no, overallScore: c.overallScore, passedCount: c.passedCount, totalVolume: c.totalVolume };
    });

    tempScores.sort((a, b) => {
        if (b.overallScore !== a.overallScore) return b.overallScore - a.overallScore;
        if (b.passedCount !== a.passedCount) return b.passedCount - a.passedCount;
        return b.totalVolume - a.totalVolume;
    });

    const newRank = tempScores.findIndex(x => x.no === Number(selectedCampusNo)) + 1;

    document.getElementById('simProjectedScore').textContent = `${newScore}%`;
    document.getElementById('simProjectedPassed').textContent = `${passed} / 5`;
    document.getElementById('simProjectedRank').textContent = `#${newRank} dari ${CAMPUS_DATA.length}`;
}

function resetSimulator() {
    const campus = CAMPUS_DATA.find(c => c.no === Number(selectedCampusNo)) || CAMPUS_DATA[0];
    if (!campus) return;
    simState = {
        octh: campus.octh,
        octe: campus.octe,
        octs: campus.octs,
        ocfo: campus.ocfo,
        ocfs: campus.ocfs
    };
    renderSimulatorControls(campus);
}

// ==================== 7. VIEW 2: GRID SEMUA KAMPUS ====================
function renderCampusGrid() {
    const container = document.getElementById('allCampusesGrid');
    if (!container) return;
    const q = (document.getElementById('cardSearchInput')?.value || '').toLowerCase().trim();
    const grp = document.getElementById('cardFilterGroup')?.value || 'all';
    const status = document.getElementById('cardFilterStatus')?.value || 'all';
    const sortBy = document.getElementById('cardSortBy')?.value || 'score_desc';

    let list = CAMPUS_DATA.filter(c => {
        if (q && !c.kampus.toLowerCase().includes(q)) return false;
        if (grp !== 'all' && c.group !== grp) return false;
        if (status === 'achieved_any' && c.passedCount < 1) return false;
        if (status === 'achieved_2plus' && c.passedCount < 2) return false;
        if (status === 'achieved_none' && c.passedCount !== 0) return false;
        return true;
    });

    list.sort((a, b) => {
        if (sortBy === 'score_desc') return b.overallScore - a.overallScore || b.passedCount - a.passedCount;
        if (sortBy === 'score_asc') return a.overallScore - b.overallScore || a.passedCount - b.passedCount;
        if (sortBy === 'passed_desc') return b.passedCount - a.passedCount || b.overallScore - a.overallScore;
        if (sortBy === 'volume_desc') return b.totalVolume - a.totalVolume;
        if (sortBy === 'name_asc') return a.kampus.localeCompare(b.kampus);
        return a.no - b.no;
    });

    if (list.length === 0) {
        container.innerHTML = `<div class="col-span-full bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-500 text-sm">Tidak ada kampus yang cocok dengan pencarian/filter.</div>`;
        return;
    }

    container.innerHTML = list.map(c => {
        const pillHtml = STRATEGY_KEYS.map(k => {
            const cfg = TARGETS[k];
            const ok = c.met[k];
            return `
            <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs ${ok ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold' : 'bg-slate-50 text-slate-600 border border-slate-200/60'}">
                <span class="text-[11px]">${cfg.code}</span>
                <span>${c[k]} <span class="text-[10px] opacity-65">/${cfg.target}</span></span>
            </div>`;
        }).join('');

        return `
        <div onclick="jumpToCampusProfile(${c.no})" class="cursor-pointer bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-300 transition-all p-4 flex flex-col justify-between min-w-0">
            <div>
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded ${c.group === 'Mandiri' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600'}">${c.group} • #${c.rank}</span>
                        <h4 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1 truncate" title="${c.kampus}">${c.kampus}</h4>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-lg font-black ${c.overallScore >= 50 ? 'text-emerald-600' : (c.overallScore >= 25 ? 'text-blue-600' : 'text-slate-700')}">${c.overallScore}%</div>
                        <div class="text-[10px] font-semibold text-slate-400">Lulus: ${c.passedCount}/5</div>
                    </div>
                </div>
                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                    <div class="h-full rounded-full ${c.overallScore >= 50 ? 'bg-emerald-500' : (c.overallScore >= 25 ? 'bg-blue-500' : 'bg-rose-400')}" style="width: ${c.overallScore}%"></div>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    ${pillHtml}
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-blue-600">
                <span>Total Aktivitas: ${c.totalVolume}</span>
                <span>Lihat Detail &rarr;</span>
            </div>
        </div>
        `;
    }).join('');
}

// ==================== 8. VIEW 3: MATRIX TABLE & HEATMAP ====================
function sortMatrix(key) {
    if (matrixSort.key === key) {
        matrixSort.asc = !matrixSort.asc;
    } else {
        matrixSort.key = key;
        matrixSort.asc = key === 'no' || key === 'kampus';
    }
    renderMatrixTable();
}

function renderMatrixTable() {
    const tbody = document.getElementById('matrixTableBody');
    const tfoot = document.getElementById('matrixTableFoot');
    if (!tbody || !tfoot) return;

    const q = (document.getElementById('tableSearchInput')?.value || '').toLowerCase().trim();
    const stratFilter = document.getElementById('tableStrategyFilter')?.value || 'all';

    let rows = CAMPUS_DATA.filter(c => {
        if (q && !c.kampus.toLowerCase().includes(q)) return false;
        if (stratFilter === 'octh_ok' && !c.met.octh) return false;
        if (stratFilter === 'octh_gap' && c.met.octh) return false;
        if (stratFilter === 'octe_ok' && !c.met.octe) return false;
        if (stratFilter === 'octe_gap' && c.met.octe) return false;
        if (stratFilter === 'octs_ok' && !c.met.octs) return false;
        if (stratFilter === 'octs_gap' && c.met.octs) return false;
        if (stratFilter === 'ocfo_ok' && !c.met.ocfo) return false;
        if (stratFilter === 'ocfo_gap' && c.met.ocfo) return false;
        if (stratFilter === 'ocfs_ok' && !c.met.ocfs) return false;
        if (stratFilter === 'ocfs_gap' && c.met.ocfs) return false;
        return true;
    });

    rows.sort((a, b) => {
        const k = matrixSort.key;
        let va = k === 'passed' ? a.passedCount : (k === 'score' ? a.overallScore : a[k]);
        let vb = k === 'passed' ? b.passedCount : (k === 'score' ? b.overallScore : b[k]);
        if (typeof va === 'string') {
            return matrixSort.asc ? va.localeCompare(vb) : vb.localeCompare(va);
        }
        return matrixSort.asc ? va - vb : vb - va;
    });

    tbody.innerHTML = rows.map(c => `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="py-2.5 px-3 font-semibold text-slate-500">${c.no}</td>
            <td class="py-2.5 px-4 font-bold text-slate-900">${c.kampus}</td>
            <td class="py-2.5 px-3 text-center">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold ${c.group === 'Mandiri' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600'}">${c.group}</span>
            </td>
            <td class="py-2.5 px-3 text-center ${c.met.octh ? 'cell-achieved' : 'cell-unachieved'}">${c.octh}</td>
            <td class="py-2.5 px-3 text-center ${c.met.octe ? 'cell-achieved' : 'cell-unachieved'}">${c.octe}</td>
            <td class="py-2.5 px-3 text-center ${c.met.octs ? 'cell-achieved' : 'cell-unachieved'}">${c.octs}</td>
            <td class="py-2.5 px-3 text-center ${c.met.ocfo ? 'cell-achieved' : 'cell-unachieved'}">${c.ocfo}</td>
            <td class="py-2.5 px-3 text-center ${c.met.ocfs ? 'cell-achieved' : 'cell-unachieved'}">${c.ocfs}</td>
            <td class="py-2.5 px-3 text-center font-bold text-slate-700">${c.passedCount} / 5</td>
            <td class="py-2.5 px-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <div class="w-16 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: ${c.overallScore}%"></div>
                    </div>
                    <span class="font-bold text-slate-800">${c.overallScore}%</span>
                </div>
            </td>
            <td class="py-2.5 px-3 text-center">
                <button type="button" onclick="jumpToCampusProfile(${c.no})" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px]">Profil</button>
            </td>
        </tr>
    `).join('');

    // Totals Footer
    const sum = { octh: 0, octe: 0, octs: 0, ocfo: 0, ocfs: 0, score: 0 };
    const passCounts = { octh: 0, octe: 0, octs: 0, ocfo: 0, ocfs: 0 };
    rows.forEach(r => {
        STRATEGY_KEYS.forEach(k => {
            sum[k] += r[k];
            if (r.met[k]) passCounts[k]++;
        });
        sum.score += r.overallScore;
    });
    const avgScore = rows.length ? Math.round((sum.score / rows.length) * 10) / 10 : 0;

    tfoot.innerHTML = `
        <tr>
            <td colspan="3" class="py-3 px-4">TOTAL VOLUME (${rows.length} Kampus Ditampilkan)</td>
            <td class="py-3 px-3 text-center">${sum.octh}</td>
            <td class="py-3 px-3 text-center">${sum.octe}</td>
            <td class="py-3 px-3 text-center">${sum.octs}</td>
            <td class="py-3 px-3 text-center">${sum.ocfo}</td>
            <td class="py-3 px-3 text-center">${sum.ocfs}</td>
            <td class="py-3 px-3 text-center">-</td>
            <td class="py-3 px-4 text-center">Rata-rata: ${avgScore}%</td>
            <td></td>
        </tr>
        <tr class="bg-emerald-50 text-emerald-900">
            <td colspan="3" class="py-2.5 px-4">JUMLAH KAMPUS LULUS TARGET (&ge; Standar)</td>
            <td class="py-2.5 px-3 text-center">${passCounts.octh} Kampus</td>
            <td class="py-2.5 px-3 text-center">${passCounts.octe} Kampus</td>
            <td class="py-2.5 px-3 text-center">${passCounts.octs} Kampus</td>
            <td class="py-2.5 px-3 text-center">${passCounts.ocfo} Kampus</td>
            <td class="py-2.5 px-3 text-center">${passCounts.ocfs} Kampus</td>
            <td colspan="3"></td>
        </tr>
    `;
}

function downloadCSV() {
    let csv = 'No,Kampus,Kelompok,OCTH (Target 2),OCTE (Target 2),OCTS (Target 60),OCFO (Target 5),OCFS (Target 5),Strategi Lulus,Skor Capaian (%)\n';
    CAMPUS_DATA.forEach(c => {
        csv += `${c.no},"${c.kampus}",${c.group},${c.octh},${c.octe},${c.octs},${c.ocfo},${c.ocfs},${c.passedCount},${c.overallScore}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'Dashboard_5_Strategi_DMER_Markom_Real.csv';
    a.click();
    URL.revokeObjectURL(url);
}

// ==================== 9. VIEW 4: EXECUTIVE SUMMARY ====================
function renderExecutiveSummary() {
    const avgNatEl = document.getElementById('execAvgScore');
    if (!avgNatEl) return;

    const totalCampuses = CAMPUS_DATA.length;
    const avgNat = totalCampuses > 0
        ? Math.round((CAMPUS_DATA.reduce((a, c) => a + c.overallScore, 0) / totalCampuses) * 10) / 10
        : 0;
    const zeroCount = CAMPUS_DATA.filter(c => c.passedCount === 0).length;
    avgNatEl.textContent = `${avgNat}%`;
    document.getElementById('execZeroTargetCount').textContent = `${zeroCount} Kampus (${totalCampuses > 0 ? Math.round((zeroCount / totalCampuses) * 100) : 0}%)`;

    // Compute Pass counts per strategy dynamically
    const stratPassStats = STRATEGY_KEYS.map(k => {
        const passed = CAMPUS_DATA.filter(c => c.met[k]).length;
        const pct = totalCampuses > 0 ? Math.round((passed / totalCampuses) * 1000) / 10 : 0;
        return { key: k, code: TARGETS[k].code, name: TARGETS[k].name, passed, pct };
    });

    const sortedByPass = [...stratPassStats].sort((a, b) => b.passed - a.passed);
    const mostPassed = sortedByPass[0];
    const leastPassed = sortedByPass[sortedByPass.length - 1];

    if (mostPassed) {
        document.getElementById('execMostPassedStrat').textContent = `${mostPassed.code} (${mostPassed.passed}/${totalCampuses})`;
        document.getElementById('execMostPassedDesc').textContent = `${mostPassed.pct}% kampus mencapai target ${mostPassed.name}`;
    }
    if (leastPassed) {
        document.getElementById('execLeastPassedStrat').textContent = `${leastPassed.code} (${leastPassed.passed}/${totalCampuses})`;
        document.getElementById('execLeastPassedDesc').textContent = `${leastPassed.pct}% kampus mencapai target ${leastPassed.name}`;
    }

    // Top Balanced List
    const top5 = [...CAMPUS_DATA].sort((a, b) => b.overallScore - a.overallScore || b.passedCount - a.passedCount).slice(0, 6);
    document.getElementById('topBalancedList').innerHTML = top5.map((c, i) => `
        <li class="flex items-center justify-between gap-2">
            <span class="truncate"><strong>#${i + 1} ${c.kampus}</strong> (${c.passedCount}/5 Lulus)</span>
            <span class="font-bold text-emerald-700 shrink-0">${c.overallScore}%</span>
        </li>
    `).join('');

    // Top Volume Records per Strategy (100% dynamic from real data)
    const topVolumeHtml = STRATEGY_KEYS.map(k => {
        const cfg = TARGETS[k];
        const sorted = [...CAMPUS_DATA].sort((a, b) => b[k] - a[k]);
        const bestVal = sorted[0] ? sorted[0][k] : 0;
        const leaders = sorted.filter(c => c[k] === bestVal && bestVal > 0).slice(0, 2).map(c => c.kampus).join(', ');
        const text = bestVal > 0 ? `${leaders} (${bestVal} ${cfg.unit})` : 'Belum ada realisasi (0)';
        return `<li><strong>${cfg.code} Tertinggi:</strong> ${text}</li>`;
    }).join('');
    const topVolEl = document.getElementById('topVolumeRecordsList');
    if (topVolEl) topVolEl.innerHTML = topVolumeHtml;

    // Pass Rate Chart
    const passCounts = STRATEGY_KEYS.map(k => CAMPUS_DATA.filter(c => c.met[k]).length);
    const failCounts = STRATEGY_KEYS.map((k, i) => totalCampuses - passCounts[i]);

    const ctx1 = document.getElementById('execPassRateChart');
    if (ctx1 && typeof Chart !== 'undefined') {
        if (execPassChartInstance) execPassChartInstance.destroy();
        execPassChartInstance = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['OCTH (Target 2)', 'OCTE (Target 2)', 'OCTS (Target 60)', 'OCFO (Target 5)', 'OCFS (Target 5)'],
                datasets: [
                    { label: 'Lulus Target', data: passCounts, backgroundColor: '#10b981', borderRadius: 6 },
                    { label: 'Belum Lulus Target', data: failCounts, backgroundColor: '#f43f5e', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, max: Math.max(totalCampuses, 1) }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Distribution Chart
    const dist = [0, 1, 2, 3, 4, 5].map(n => CAMPUS_DATA.filter(c => c.passedCount === n).length);
    const ctx2 = document.getElementById('execDistChart');
    if (ctx2 && typeof Chart !== 'undefined') {
        if (execDistChartInstance) execDistChartInstance.destroy();
        execDistChartInstance = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['0 Strategi', '1 Strategi', '2 Strategi', '3 Strategi', '4 Strategi', '5 Strategi (Sempurna)'],
                datasets: [{
                    label: 'Jumlah Kampus',
                    data: dist,
                    backgroundColor: ['#f43f5e', '#f59e0b', '#3b82f6', '#10b981', '#059669', '#047857'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
}

// ==================== 10. VIEW 5: KOMPARASI KAMPUS ====================
function initCompareSelectors() {
    const s1 = document.getElementById('compSelect1');
    const s2 = document.getElementById('compSelect2');
    const s3 = document.getElementById('compSelect3');
    if (!s1 || !s2 || !s3) return;

    [s1, s2, s3].forEach(sel => {
        sel.innerHTML = CAMPUS_DATA.map(c => `<option value="${c.no}">${c.no}. ${c.kampus} (${c.overallScore}%)</option>`).join('');
    });

    const topThree = [...CAMPUS_DATA].sort((a, b) => b.overallScore - a.overallScore);
    s1.value = topThree[0]?.no || (CAMPUS_DATA[0]?.no ?? 1);
    s2.value = topThree[1]?.no || (CAMPUS_DATA[1]?.no ?? 1);
    s3.value = topThree[2]?.no || (CAMPUS_DATA[2]?.no ?? 1);
}

function renderComparison() {
    const s1 = document.getElementById('compSelect1');
    const s2 = document.getElementById('compSelect2');
    const s3 = document.getElementById('compSelect3');
    if (!s1 || !s2 || !s3) return;

    const c1 = CAMPUS_DATA.find(c => c.no === Number(s1.value)) || CAMPUS_DATA[0];
    const c2 = CAMPUS_DATA.find(c => c.no === Number(s2.value)) || CAMPUS_DATA[1] || CAMPUS_DATA[0];
    const c3 = CAMPUS_DATA.find(c => c.no === Number(s3.value)) || CAMPUS_DATA[2] || CAMPUS_DATA[0];
    if (!c1 || !c2 || !c3) return;

    document.getElementById('compHead1').textContent = c1.kampus;
    document.getElementById('compHead2').textContent = c2.kampus;
    document.getElementById('compHead3').textContent = c3.kampus;

    const tbody = document.getElementById('compareTableBody');
    const stratRows = STRATEGY_KEYS.map(k => {
        const cfg = TARGETS[k];
        return `
        <tr>
            <td class="py-2.5 px-4 font-bold text-slate-800">${cfg.code} — <span class="font-normal text-slate-500">${cfg.name}</span></td>
            <td class="py-2.5 px-3 text-center font-semibold text-slate-500">${cfg.target} ${cfg.unit}</td>
            <td class="py-2.5 px-3 text-center ${c1.met[k] ? 'cell-achieved' : 'cell-unachieved'}">${c1[k]} (${c1.pct[k]}%)</td>
            <td class="py-2.5 px-3 text-center ${c2.met[k] ? 'cell-achieved' : 'cell-unachieved'}">${c2[k]} (${c2.pct[k]}%)</td>
            <td class="py-2.5 px-3 text-center ${c3.met[k] ? 'cell-achieved' : 'cell-unachieved'}">${c3[k]} (${c3.pct[k]}%)</td>
        </tr>`;
    }).join('');

    const summaryRows = `
        <tr class="bg-slate-50 font-bold">
            <td class="py-2.5 px-4 text-slate-800">Jumlah Strategi Lulus</td>
            <td class="py-2.5 px-3 text-center text-slate-500">5 Strategi</td>
            <td class="py-2.5 px-3 text-center text-blue-700">${c1.passedCount} / 5</td>
            <td class="py-2.5 px-3 text-center text-emerald-700">${c2.passedCount} / 5</td>
            <td class="py-2.5 px-3 text-center text-amber-700">${c3.passedCount} / 5</td>
        </tr>
        <tr class="bg-slate-100 font-extrabold">
            <td class="py-3 px-4 text-slate-900">Skor Ketercapaian Rata-rata</td>
            <td class="py-3 px-3 text-center text-slate-600">100%</td>
            <td class="py-3 px-3 text-center text-blue-700">${c1.overallScore}% (Rank #${c1.rank})</td>
            <td class="py-3 px-3 text-center text-emerald-700">${c2.overallScore}% (Rank #${c2.rank})</td>
            <td class="py-3 px-3 text-center text-amber-700">${c3.overallScore}% (Rank #${c3.rank})</td>
        </tr>
    `;

    tbody.innerHTML = stratRows + summaryRows;

    const ctx = document.getElementById('compareRadarChart');
    if (ctx && typeof Chart !== 'undefined') {
        if (compareRadarInstance) compareRadarInstance.destroy();
        compareRadarInstance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['OCTH', 'OCTE', 'OCTS', 'OCFO', 'OCFS'],
                datasets: [
                    {
                        label: c1.kampus,
                        data: STRATEGY_KEYS.map(k => Math.min(c1.pct[k], 150)),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        borderWidth: 2
                    },
                    {
                        label: c2.kampus,
                        data: STRATEGY_KEYS.map(k => Math.min(c2.pct[k], 150)),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 2
                    },
                    {
                        label: c3.kampus,
                        data: STRATEGY_KEYS.map(k => Math.min(c3.pct[k], 150)),
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.15)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 150,
                        ticks: { stepSize: 50, callback: v => v + '%', font: { size: 10 } }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}

// ==================== 11. BOOTSTRAP ON LOAD ====================
window.addEventListener('DOMContentLoaded', () => {
    if (CAMPUS_DATA.length === 0) return;
    if (CAN_VIEW_ALL_TABS) {
        populateCampusDropdown('all');
        initCompareSelectors();
    }
    const select = document.getElementById('campusSelect');
    if (select) select.value = selectedCampusNo;
    updateCampusProfile();
});
</script>
