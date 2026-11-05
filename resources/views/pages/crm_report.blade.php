<?php
$canViewAllCrmCampuses = (bool) ($canViewAllCrmCampuses ?? false);
$userCampusFullName = strtoupper(trim((string) ($userCampusFullName ?? 'UBSI KAMPUS YOGYAKARTA')));
$requestedCampusParam = strtoupper(trim((string) ($requestedCampusParam ?? '')));

$crmTimestamp = (string) ($timestamp ?? (date('d M Y, H:i:s') . ' WIB'));
$macroSummary = (array) ($macro ?? []);
$visibleCrmCampuses = (array) ($campuses ?? []);
$allCrmCampuses = (array) ($allCampuses ?? $visibleCrmCampuses);
$visibleCrmAgents = (array) ($agents ?? []);
$crmRecentLogs = (array) ($recentLogs ?? []);

$matchedCampus = $matchedCampus ?? null;
if (!$matchedCampus && !empty($visibleCrmCampuses)) {
    $matchedCampus = $visibleCrmCampuses[0];
}

$initialActiveTab = $canViewAllCrmCampuses
    ? ($requestedCampusParam !== '' ? 'campus-scoped' : 'admin-overview')
    : 'campus-scoped';
?>
<script src="<?= base_url('assets/js/lib/svg-chart-engine.js?v=' . @filemtime(FCPATH . 'assets/js/lib/svg-chart-engine.js')) ?>"></script>
<style>
  .crm-report-wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #0f172a;
    width: 100%;
    max-width: 100%;
  }
  .crm-report-wrapper * {
    box-sizing: border-box;
  }
  .crm-report-wrapper canvas {
    display: none !important;
  }
  .crm-custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  .crm-custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 9999px;
  }
  .crm-custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
  }
  .crm-custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }
  .crm-header-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .crm-role-banner-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .crm-kpi-grid-6 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 0.875rem;
  }
  .crm-rec-grid-2 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
  }
  .crm-charts-12 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1.5rem;
  }
  .crm-col-7,
  .crm-col-5,
  .crm-col-12 {
    grid-column: span 1 / span 1;
    min-width: 0;
  }
  .crm-stage-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
  }
  .crm-response-grid-6 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
  }
  .crm-selector-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .crm-toolbar-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  @media (min-width: 640px) {
    .crm-kpi-grid-6 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .crm-response-grid-6 {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .crm-selector-row {
      flex-direction: row;
      align-items: flex-end;
    }
  }
  @media (min-width: 768px) {
    .crm-kpi-grid-6 {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .crm-rec-grid-2 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (min-width: 1024px) {
    .crm-header-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
    .crm-role-banner-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
    .crm-toolbar-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
    .crm-kpi-grid-6 {
      grid-template-columns: repeat(6, minmax(0, 1fr));
    }
    .crm-response-grid-6 {
      grid-template-columns: repeat(6, minmax(0, 1fr));
    }
    .crm-charts-12 {
      grid-template-columns: repeat(12, minmax(0, 1fr));
    }
    .crm-col-7 {
      grid-column: span 7 / span 7;
    }
    .crm-col-5 {
      grid-column: span 5 / span 5;
    }
    .crm-col-12 {
      grid-column: span 12 / span 12;
    }
  }
</style>

<div class="crm-report-wrapper space-y-6 pb-8">

  <!-- Top Header Card with Real-Time Synchronizer -->
  <div class="bg-slate-900 text-white rounded-2xl border border-slate-800 shadow-md p-4 sm:p-5">
    <div class="crm-header-row">
      <div class="flex items-start sm:items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0 shadow-inner">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white">Dashboard &amp; Rekomendasi Hasil CRM Kampus</h1>
            <span id="crmRealtimeBadge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              Real-Time Sync Aktif
            </span>
            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-slate-800 text-slate-300 border border-slate-700">
              Diperbarui: <strong id="crmRealtimeTimeText" class="text-white font-mono"><?= esc($crmTimestamp) ?></strong>
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-1">
            <?php if ($canViewAllCrmCampuses): ?>
              Monitoring Real-Time Lintas Kampus: <strong class="text-indigo-300"><?= number_format($macroSummary['totalLeads'] ?? 8794, 0, ',', '.') ?> Total Prospek</strong> • <strong class="text-emerald-300"><?= count($allCrmCampuses) ?> Kampus</strong> • <strong class="text-sky-300"><?= $macroSummary['totalEventsWithLeads'] ?? 46 ?> Event Ber-Leads</strong>
            <?php else: ?>
              Laporan Operasional &amp; Hasil CRM Khusus <strong class="text-emerald-300"><?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?></strong> (Data kampus lain disembunyikan sesuai hak akses)
            <?php endif; ?>
          </p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button id="crmRealtimeSyncBtn" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow transition" title="Sinkronkan data dan grafik dengan database secara real-time">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          <span id="crmRealtimeSyncBtnText">Refresh Real-Time</span>
        </button>

        <?php if ($canViewAllCrmCampuses): ?>
          <!-- Navigation Tabs (Admin Only) -->
          <nav class="flex flex-wrap items-center gap-1 bg-slate-800/90 p-1 rounded-xl border border-slate-700">
            <button id="tabBtn-admin-overview" type="button" data-tab="admin-overview" class="nav-tab-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all bg-indigo-600 text-white shadow">
              🔐 View Admin: Rekomendasi
            </button>
            <button id="tabBtn-admin-matrix" type="button" data-tab="admin-matrix" class="nav-tab-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60">
              📊 View Admin: Matriks Kampus
            </button>
            <button id="tabBtn-campus-scoped" type="button" data-tab="campus-scoped" class="nav-tab-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60">
              🏢 View Kampus Pelaksana
            </button>
            <button id="tabBtn-access-guide" type="button" data-tab="access-guide" class="nav-tab-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60">
              🛡️ Skema Hak Akses
            </button>
          </nav>
        <?php endif; ?>

        <a href="<?= site_url('crm') ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-500 hover:bg-emerald-600 text-slate-950 shadow">
          💬 Buka CRM Workspace &rarr;
        </a>
      </div>
    </div>
  </div>

  <?php if ($canViewAllCrmCampuses): ?>
  <!-- =================================================================== -->
  <!-- TAB 1: VIEW ADMIN PUSAT — REKOMENDASI & ANALITIK NASIONAL          -->
  <!-- =================================================================== -->
  <section id="view-admin-overview" class="space-y-6">
    <!-- Role Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 shadow-md border border-indigo-500/30">
      <div class="crm-role-banner-row">
        <div class="space-y-1.5">
          <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 text-xs font-extrabold uppercase tracking-wider rounded-full bg-indigo-500 text-white">Mode Akses: Admin Pusat (Semua Kampus)</span>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
              <span id="bannerLeadsCount"><?= number_format($macroSummary['totalLeads'] ?? 8794, 0, ',', '.') ?></span> Siswa • <?= count($allCrmCampuses) ?> Kampus • <?= $macroSummary['totalEventsWithLeads'] ?? 46 ?> Event Ber-Leads
            </span>
          </div>
          <h2 class="text-base sm:text-lg font-extrabold text-white">Rekomendasi Strategis &amp; Analitik Lintas Kampus (Khusus Admin Pusat)</h2>
          <p class="text-xs text-slate-300 max-w-4xl leading-relaxed">
            Tampilan ini <strong>hanya dapat dilihat oleh Admin Pusat</strong> untuk memonitor progres konversi seluruh kampus di database CRM secara real-time, mengintervensi tumpukan data <em>Belum diassign</em> dan <em>Fresh tertahan di Agent</em>, serta mengevaluasi efektivitas event dan respon calon mahasiswa se-Indonesia.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
          <button id="jumpToCampusViewBtn" type="button" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-slate-950 transition shadow-sm flex items-center gap-1.5">
            <span>🏢 Buka View Khusus Kampus Pelaksana</span>
            <span>&rarr;</span>
          </button>
          <button id="exportAdminCsvBtn1" type="button" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            <span>Unduh Laporan Admin (CSV)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 6 National Macro KPI Cards -->
    <div class="crm-kpi-grid-6">
      <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Prospek CRM</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700"><?= count($allCrmCampuses) ?> Kampus</span>
        </div>
        <div id="kpiMacroTotal" class="text-2xl font-extrabold text-slate-900 mt-1.5"><?= number_format($macroSummary['totalLeads'] ?? 8794, 0, ',', '.') ?></div>
        <div class="text-[11px] text-slate-500 mt-1"><?= $macroSummary['totalEventsWithLeads'] ?? 46 ?> Event Ber-Leads</div>
      </div>

      <div class="rounded-2xl p-4 border border-rose-200 bg-rose-50/70 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Belum Di-Assign</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">Darurat SLA</span>
        </div>
        <div id="kpiMacroUnassigned" class="text-2xl font-extrabold text-rose-700 mt-1.5"><?= number_format($macroSummary['totalUnassigned'] ?? 3770, 0, ',', '.') ?></div>
        <div class="text-[11px] text-rose-900/80 mt-1">Belum memiliki Agent CRM</div>
      </div>

      <div class="rounded-2xl p-4 border border-amber-200 bg-amber-50/70 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Fresh Total</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">Tahap 1 Fresh</span>
        </div>
        <div id="kpiMacroFresh" class="text-2xl font-extrabold text-amber-700 mt-1.5"><?= number_format($macroSummary['totalFresh'] ?? 5061, 0, ',', '.') ?></div>
        <div class="text-[11px] text-amber-900/80 mt-1">Belum pernah dihubungi</div>
      </div>

      <div class="rounded-2xl p-4 border border-sky-200 bg-sky-50/70 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-sky-900">Minat &amp; Ragu-ragu</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-sky-100 text-sky-800">Skor 55</span>
        </div>
        <div id="kpiMacroWarm" class="text-2xl font-extrabold text-sky-700 mt-1.5"><?= number_format(($macroSummary['totalMinat'] ?? 165) + ($macroSummary['totalRagu'] ?? 58), 0, ',', '.') ?></div>
        <div class="text-[11px] text-sky-900/80 mt-1"><?= $macroSummary['totalMinat'] ?? 165 ?> Minat • <?= $macroSummary['totalRagu'] ?? 58 ?> Ragu-ragu</div>
      </div>

      <div class="rounded-2xl p-4 border border-emerald-200 bg-emerald-50/70 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900">Closing (PMB)</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Tahap 3</span>
        </div>
        <div id="kpiMacroClosing" class="text-2xl font-extrabold text-emerald-700 mt-1.5"><?= number_format($macroSummary['totalClosing'] ?? 521, 0, ',', '.') ?></div>
        <div class="text-[11px] text-emerald-900/80 mt-1">Rasio Closing Nasional: <strong><?= $macroSummary['overallClosingPct'] ?? 5.9 ?>%</strong></div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tidak Merespon</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700">Re-Engagement</span>
        </div>
        <div id="kpiMacroNoResp" class="text-2xl font-extrabold text-slate-800 mt-1.5"><?= number_format($macroSummary['totalNoResp'] ?? 3819, 0, ',', '.') ?></div>
        <div class="text-[11px] text-slate-500 mt-1"><?= number_format($macroSummary['totalMenolak'] ?? 218, 0, ',', '.') ?> siswa menolak</div>
      </div>
    </div>

    <!-- Strategic Macro Charts -->
    <div class="crm-charts-12">
      <!-- Funnel & Status Comparison Chart -->
      <div class="crm-col-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Komparasi Funnel &amp; Status Hasil CRM Lintas Seluruh Kampus</h3>
            <p class="text-xs text-slate-500">Perbandingan distribusi respon prospek antar kampus di Indonesia</p>
          </div>
          <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
            <button id="adminChartModeVolBtn" type="button" class="px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-slate-900 shadow-sm">Volume (Siswa)</button>
            <button id="adminChartModePctBtn" type="button" class="px-2.5 py-1 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900">Persentase (%)</button>
          </div>
        </div>
        <div class="relative h-80 w-full">
          <canvas id="crmAdminCampusChart"></canvas>
        </div>
      </div>

      <!-- Category Conversion Chart -->
      <div class="crm-col-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-900">Efektivitas Kategori Event terhadap Konversi Closing</h3>
        <p class="text-xs text-slate-500 mb-3">Analisis kategori event yang paling produktif menghasilkan closing</p>
        <div class="relative h-80 w-full">
          <canvas id="crmAdminCategoryChart"></canvas>
        </div>
      </div>
    </div>
  </section>

  <!-- =================================================================== -->
  <!-- TAB 2: VIEW ADMIN PUSAT — MATRIKS PENGAWASAN KAMPUS & AGENT       -->
  <!-- =================================================================== -->
  <section id="view-admin-matrix" class="hidden space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="crm-toolbar-row pb-4 border-b border-slate-100">
        <div>
          <h2 class="text-base font-extrabold text-slate-900">Tabel Pengawasan Seluruh Kampus Pelaksana (Khusus Admin Pusat)</h2>
          <p class="text-xs text-slate-500 mt-0.5">Membandingkan status SLA, konversi, dan tindak lanjut CRM per kampus pelaksana</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <input id="adminCrmSearchInput" type="text" placeholder="Cari kampus..." class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none w-44">
          <select id="adminCrmClusterFilter" class="rounded-xl border border-slate-200 px-2.5 py-1.5 text-xs text-slate-700 bg-slate-50">
            <option value="ALL">Semua Klaster Status</option>
            <option value="URGENT_UNASSIGNED">Darurat Belum Assign</option>
            <option value="STALLED_ASSIGNED">Fresh Tertahan di Agent</option>
            <option value="HIGH_NORESPONSE">Tidak Merespon Tinggi</option>
            <option value="BENCHMARK_CLOSING">Benchmark Closing Tinggi</option>
          </select>
          <select id="adminCrmSortSelect" class="rounded-xl border border-slate-200 px-2.5 py-1.5 text-xs text-slate-700 bg-slate-50">
            <option value="TOTAL_DESC">Urutkan: Total Leads Terbanyak</option>
            <option value="CLOSING_DESC">Urutkan: Closing Terbanyak</option>
            <option value="UNASSIGNED_DESC">Urutkan: Unassigned Terbanyak</option>
            <option value="FRESH_DESC">Urutkan: Saldo Fresh Terbanyak</option>
            <option value="SCORE_DESC">Urutkan: Skor Tertinggi</option>
          </select>
          <button id="exportAdminCsvBtn2" type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white transition flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            <span>Unduh CSV</span>
          </button>
        </div>
      </div>

      <div class="overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200 mt-4 max-h-[560px]">
        <table class="w-full text-left border-collapse text-xs">
          <thead class="sticky top-0 bg-slate-900 text-white font-semibold uppercase tracking-wider">
            <tr>
              <th class="py-3 px-2.5 text-center">No</th>
              <th class="py-3 px-3">Kampus Pelaksana</th>
              <th class="py-3 px-2.5 text-center">Total Prospek</th>
              <th class="py-3 px-2.5 text-center bg-rose-950/70">Belum Di-Assign</th>
              <th class="py-3 px-2.5 text-center bg-amber-950/70">Fresh Total</th>
              <th class="py-3 px-2.5 text-center">Tdk Respon</th>
              <th class="py-3 px-2.5 text-center bg-sky-950/70">Minat / Ragu</th>
              <th class="py-3 px-2.5 text-center">Menolak</th>
              <th class="py-3 px-2.5 text-center bg-emerald-950/70">Closing (PMB)</th>
              <th class="py-3 px-2 text-center">Rata-rata Skor</th>
              <th class="py-3 px-3">Status Klaster &amp; Catatan Admin</th>
              <th class="py-3 px-2.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="adminCrmTableBody" class="divide-y divide-slate-200">
            <!-- Populated by JS -->
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- =================================================================== -->
  <!-- TAB 3: VIEW MASING-MASING KAMPUS PELAKSANA (DETAIL OPERASIONAL)     -->
  <!-- =================================================================== -->
  <section id="view-campus-scoped" class="<?= $canViewAllCrmCampuses ? 'hidden ' : '' ?>space-y-6">
    <!-- Top Role Isolation & Campus Selector Bar -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-emerald-500/40 p-4 sm:p-5">
      <div class="crm-charts-12 items-center">
        <div class="crm-col-7 space-y-3">
          <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 text-xs font-extrabold uppercase tracking-wider rounded-full bg-emerald-600 text-white">
              🏢 Mode Akses: Kampus Pelaksana (Detail Progres &amp; Respon CRM)
            </span>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-300">
              🔒 Terisolasi Per Kampus
            </span>
          </div>

          <?php if ($canViewAllCrmCampuses): ?>
            <div class="crm-selector-row">
              <div class="flex-1">
                <label for="crmCampusSelect" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  Pilih Kampus Pelaksana (Tampilan Detail Progres CRM):
                </label>
                <div class="flex items-center gap-1.5">
                  <button id="crmPrevCampusBtn" type="button" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition" title="Kampus Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                  </button>
                  <select id="crmCampusSelect" class="w-full rounded-xl border border-emerald-500 bg-emerald-50/30 px-3.5 py-2.5 text-sm font-extrabold text-slate-900 shadow-sm focus:border-emerald-600 focus:outline-none">
                  </select>
                  <button id="crmNextCampusBtn" type="button" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition" title="Kampus Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </button>
                </div>
              </div>

              <div class="sm:w-56">
                <label for="crmCampusQuickFilter" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  Filter Klaster Kampus:
                </label>
                <select id="crmCampusQuickFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-700">
                  <option value="ALL">Semua Kampus CRM</option>
                  <option value="URGENT_UNASSIGNED">Darurat Belum Assign</option>
                  <option value="STALLED_ASSIGNED">Fresh Tertahan di Agent</option>
                  <option value="HIGH_NORESPONSE">Tidak Merespon Tinggi</option>
                  <option value="BENCHMARK_CLOSING">Konversi Closing Tinggi</option>
                </select>
              </div>
            </div>
          <?php else: ?>
            <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-300 flex flex-wrap items-center justify-between gap-2">
              <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Kampus Pelaksana Aktif</div>
                <div class="text-base sm:text-lg font-extrabold text-slate-900"><?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?></div>
              </div>
              <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-bold">
                ✓ Terautentikasi Sesuai Kampus Anda
              </span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right: Campus Status & Role Boundary Box -->
        <div class="crm-col-5 bg-slate-900 text-white rounded-xl p-4 border border-slate-800 space-y-2">
          <div class="flex items-center justify-between">
            <span id="crmCampusClusterBadge" class="px-2.5 py-0.5 text-xs font-extrabold rounded-full bg-amber-400 text-slate-950">Status Kampus</span>
            <?php if ($canViewAllCrmCampuses): ?>
              <button id="backToAdminViewBtn" type="button" class="text-xs font-semibold text-indigo-300 hover:text-white underline">
                &larr; Kembali ke View Admin
              </button>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-300 leading-relaxed">
            Menampilkan data internal <strong id="crmCampusHeaderNameSpan" class="text-emerald-300"><?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?></strong>: progres funnel, detail event ber-leads, respon prospek, beban kerja agent internal, dan live activity logs.
          </p>
          <div class="pt-1.5 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
            <span id="crmCampusMetaSubtext">Memuat metadata...</span>
            <span class="text-emerald-400 font-semibold">🟢 Sinkron Real-Time</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 6 Operational KPI Cards for Selected Campus -->
    <div id="crmCampusKpiRow" class="crm-kpi-grid-6">
      <!-- Populated by JS -->
    </div>

    <!-- Recommendations & Action Plan -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="crm-toolbar-row pb-4 border-b border-slate-100">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-base font-extrabold text-slate-900">
              Rekomendasi Operasional &amp; Action Plan: <span id="crmCampusRecTitle" class="text-emerald-700"><?= esc($matchedCampus['kampus'] ?? 'UBSI KAMPUS YOGYAKARTA') ?></span>
            </h3>
            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">Tindak Lanjut Otomatis</span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">
            Rekomendasi taktis ini disusun otomatis dari kondisi riil event, agent, dan tahapan respon prospek di kampus ini.
          </p>
        </div>
        <button id="exportSingleCampusCrmBtn" type="button" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition flex items-center gap-1.5 shrink-0">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
          <span>Unduh Lembar Kerja Kampus (CSV)</span>
        </button>
      </div>

      <div id="crmCampusRecList" class="crm-rec-grid-2 pt-4">
        <!-- Populated by JS -->
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- REAL-TIME CHARTS: GRAFIK TANGGAPAN HASIL RESPON PER KAMPUS          -->
    <!-- =================================================================== -->
    <div class="crm-charts-12">
      <!-- Chart 1: Donut Tanggapan Hasil Respon Kampus -->
      <div class="crm-col-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between gap-2 mb-1">
            <h3 class="text-sm font-bold text-slate-900">1. Grafik Tanggapan Hasil Respon Kampus Ini</h3>
            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-emerald-100 text-emerald-800">Real-Time</span>
          </div>
          <p class="text-xs text-slate-500 mb-3">Distribusi lengkap hasil tanggapan prospek dari seluruh kegiatan di kampus ini</p>
          <div class="relative h-64 w-full">
            <canvas id="crmCampusFunnelChart"></canvas>
          </div>
        </div>

        <!-- 6 Status Breakdown Pills -->
        <div id="crmCampusResponseSummaryPills" class="crm-response-grid-6 mt-4 pt-3 border-t border-slate-100 text-center text-xs">
          <!-- Populated by JS -->
        </div>
      </div>

      <!-- Chart 2: Komparasi Respon Per Event di Kampus Ini -->
      <div class="crm-col-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between gap-2 mb-1">
            <h3 class="text-sm font-bold text-slate-900">2. Grafik Tanggapan Respon per Event di Kampus Ini</h3>
            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-indigo-100 text-indigo-800">Komparasi Event</span>
          </div>
          <p class="text-xs text-slate-500 mb-3">Membandingkan respon Closing, Minat, Ragu-ragu, Menolak, dan Fresh pada masing-masing event</p>
          <div class="relative h-64 w-full">
            <canvas id="crmCampusEventResponseChart"></canvas>
          </div>
        </div>
        <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
          <span>* Klik atau hover pada batang grafik untuk melihat detail angka dan persentase respon.</span>
          <span class="font-semibold text-indigo-600">Cross-Browser SVG Vector</span>
        </div>
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- TABEL 1: EVENT APA SAJA YANG TELAH DITAMBAHKAN LEADS               -->
    <!-- =================================================================== -->
    <div class="crm-charts-12">
      <div class="crm-col-12 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
          <div>
            <h3 class="text-sm font-bold text-slate-900">1. Daftar Event yang Telah Ditambahkan Leads di Kampus Ini</h3>
            <p class="text-xs text-slate-500">Rincian sumber kegiatan/event beserta perolehan leads dan hasil follow-up respon calon mahasiswa</p>
          </div>
          <div id="crmCampusEventCountBadge" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200">
            0 Event
          </div>
        </div>

        <div class="overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200 mt-2">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-800 text-white font-semibold uppercase">
                <th class="py-2.5 px-3">Kode &amp; Nama Event</th>
                <th class="py-2.5 px-2.5">Kategori</th>
                <th class="py-2.5 px-2.5">Tanggal &amp; Lokasi</th>
                <th class="py-2.5 px-2 text-center">Total Leads</th>
                <th class="py-2.5 px-2 text-center bg-rose-950/60">Unassigned</th>
                <th class="py-2.5 px-2 text-center bg-amber-950/60">Fresh</th>
                <th class="py-2.5 px-2 text-center">Tdk Respon</th>
                <th class="py-2.5 px-2 text-center bg-sky-950/60">Minat</th>
                <th class="py-2.5 px-2 text-center bg-amber-950/60">Ragu</th>
                <th class="py-2.5 px-2 text-center">Menolak</th>
                <th class="py-2.5 px-2 text-center bg-emerald-950/60">Closing</th>
                <th class="py-2.5 px-2 text-center">Konversi</th>
                <th class="py-2.5 px-3">Rekomendasi Tindak Lanjut Event</th>
              </tr>
            </thead>
            <tbody id="crmCampusEventTableBody" class="divide-y divide-slate-200">
              <!-- Populated by JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- TABEL 2 & 3: AGENT EVALUATION & ASAL SEKOLAH                       -->
    <!-- =================================================================== -->
    <div class="crm-charts-12">
      <!-- Agent Workload & SLA Table -->
      <div class="crm-col-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-900 mb-1">2. Evaluasi Beban Kerja &amp; Kinerja Agent CRM Internal Kampus</h3>
        <p class="text-xs text-slate-500 mb-3">Daftar agent CRM di kampus ini beserta status penugasan &amp; supervisi koordinator</p>
        <div class="overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200 max-h-96">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-slate-800 text-white font-semibold uppercase">
              <tr>
                <th class="py-2.5 px-3">Nama Agent CRM</th>
                <th class="py-2.5 px-2 text-center">Total</th>
                <th class="py-2.5 px-2 text-center">Fresh</th>
                <th class="py-2.5 px-2 text-center">Tdk Respon</th>
                <th class="py-2.5 px-2 text-center">Minat/Ragu</th>
                <th class="py-2.5 px-2 text-center">Closing</th>
                <th class="py-2.5 px-3">Evaluasi &amp; Instruksi Koordinator</th>
              </tr>
            </thead>
            <tbody id="crmCampusAgentTableBody" class="divide-y divide-slate-200">
              <!-- Populated by JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Origin Schools & Local Rejection Reasons -->
      <div class="crm-col-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between space-y-4">
        <div>
          <h3 class="text-sm font-bold text-slate-900 mb-1">3. Asal Sekolah Prospek Terbanyak di Kampus Ini</h3>
          <p class="text-xs text-slate-500 mb-2">Sekolah penyumbang prospek &amp; closing terbesar untuk prioritas kunjungan ulang</p>
          <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-52">
            <table class="w-full text-left border-collapse text-xs">
              <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                <tr>
                  <th class="py-2 px-3">Asal Sekolah</th>
                  <th class="py-2 px-2 text-center">Prospek</th>
                  <th class="py-2 px-2 text-center">Minat/Ragu</th>
                  <th class="py-2 px-2 text-center">Closing</th>
                </tr>
              </thead>
              <tbody id="crmCampusSchoolTableBody" class="divide-y divide-slate-200">
                <!-- Populated by JS -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100">
          <h4 class="text-xs font-bold text-slate-900 mb-1.5">Catatan Alasan Menolak / Kendala di Kampus Ini</h4>
          <div id="crmCampusReasonList" class="space-y-1.5 max-h-40 overflow-y-auto crm-custom-scrollbar text-xs">
            <!-- Populated by JS -->
          </div>
        </div>
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- PANEL 4: FEED AKTIVITAS FOLLOW-UP CRM REAL-TIME TERBARU             -->
    <!-- =================================================================== -->
    <div class="crm-charts-12">
      <div class="crm-col-12 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h3 class="text-sm font-bold text-slate-900">4. Log Aktivitas Follow-Up CRM Real-Time Terbaru (Live Log Feed)</h3>
          </div>
          <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
            Terhubung ke Database MySQL Real-Time
          </span>
        </div>
        <p class="text-xs text-slate-500 mb-3">Rekaman interaksi terbaru oleh tim CRM terhadap calon mahasiswa secara real-time</p>

        <div class="overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200 max-h-72">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-slate-800 text-white font-semibold uppercase">
              <tr>
                <th class="py-2.5 px-3">Waktu</th>
                <th class="py-2.5 px-3">Calon Mahasiswa</th>
                <th class="py-2.5 px-3">Kampus &amp; Event</th>
                <th class="py-2.5 px-2.5">Agent / Petugas</th>
                <th class="py-2.5 px-2 text-center">Tahap / Label Respon</th>
                <th class="py-2.5 px-3">Ringkasan Interaksi</th>
              </tr>
            </thead>
            <tbody id="crmRecentLogsTableBody" class="divide-y divide-slate-200">
              <!-- Populated by JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </section>

  <?php if ($canViewAllCrmCampuses): ?>
  <!-- =================================================================== -->
  <!-- TAB 4: SKEMA HAK AKSES CRM                                          -->
  <!-- =================================================================== -->
  <section id="view-access-guide" class="hidden space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <h2 class="text-base font-extrabold text-slate-900 mb-1">Matriks Pemisahan Hak Akses &amp; Visibilitas Rekomendasi CRM</h2>
      <p class="text-xs text-slate-500 mb-4">Pengaturan tata kelola informasi antara Admin Pusat (Pengawas Nasional) dan Koordinator/Tim CRM Kampus Pelaksana.</p>

      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="bg-slate-900 text-white font-bold">
              <th class="py-3 px-4">Modul / Informasi &amp; Rekomendasi CRM</th>
              <th class="py-3 px-4 text-center bg-indigo-950">🔐 View Admin Pusat (Semua Kampus)</th>
              <th class="py-3 px-4 text-center bg-emerald-950">🏢 View Kampus Pelaksana (Per Kampus)</th>
              <th class="py-3 px-4">Tujuan Pengaturan Hak Akses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Agregasi Funnel Nasional &amp; Peringkat Kampus</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Terbuka Penuh (Semua Kampus)</td>
              <td class="py-3 px-4 text-center font-bold text-rose-700 bg-rose-50/50">🔒 Terkunci (Hanya Lihat Kampus Sendiri)</td>
              <td class="py-3 px-4 text-xs text-slate-600">Mencegah kebocoran strategi &amp; menjaga fokus operasional masing-masing kampus.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Database Siswa, Nomor WhatsApp, &amp; Asal Sekolah</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Akses Audit &amp; Realokasi Lintas Kampus</td>
              <td class="py-3 px-4 text-center font-bold text-amber-800 bg-amber-50/50">⚠️ Terbatas Hanya Siswa di Kampusnya</td>
              <td class="py-3 px-4 text-xs text-slate-600">Melindungi privasi data calon mahasiswa (UU PDP) agar tidak dihubungi ganda oleh kampus cabang lain.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Distribusi Lead "Belum diassign" &amp; Penunjukan Agent CRM</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Monitoring SLA &amp; Intervensi Auto-Assign</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Eksekusi Pembagian Kuota ke Agent Lokal</td>
              <td class="py-3 px-4 text-xs text-slate-600">Koordinator kampus membagi tugas harian; Admin Pusat memantau kampus yang menumpuk data &gt;24 jam.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Leaderboard &amp; Evaluasi Agent CRM</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Semua Agent Lintas Kampus</td>
              <td class="py-3 px-4 text-center font-bold text-amber-800 bg-amber-50/50">⚠️ Hanya Agent Internal Kampusnya</td>
              <td class="py-3 px-4 text-xs text-slate-600">Memudahkan koordinator kampus menegur agent yang masih menumpuk data Fresh di kampusnya.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Evaluasi ROI Kategori Event &amp; Respon Siswa</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Analitik Makro Nasional</td>
              <td class="py-3 px-4 text-center font-bold text-amber-800 bg-amber-50/50">⚠️ Hanya Event yang Dilaksanakan Kampusnya</td>
              <td class="py-3 px-4 text-xs text-slate-600">Admin menentukan alokasi anggaran event nasional; kampus mengevaluasi tindak lanjut per event lokal.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Toast Notification -->
  <div id="toastNotice" class="fixed bottom-5 right-5 z-50 hidden bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-lg text-xs font-medium border border-slate-700">
    Sinkronisasi Berhasil!
  </div>

</div>

<script>
  const CAN_VIEW_ALL_CRM_CAMPUSES = <?= $canViewAllCrmCampuses ? 'true' : 'false' ?>;
  let CRM_CAMPUS_LIST = <?= json_encode($visibleCrmCampuses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  let CRM_AGENTS_GLOBAL = <?= json_encode($visibleCrmAgents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  let CRM_RECENT_LOGS = <?= json_encode($crmRecentLogs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  let CRM_MACRO_SUMMARY = <?= json_encode($macroSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  const CRM_CATEGORY_SUMMARY = [
    { category: 'Edufair', total: 6334, fresh: 3959, noResp: 2181, minat: 13, ragu: 18, t2Unlabeled: 4, menolak: 148, closing: 11, convPct: 0.17 },
    { category: 'Aplikasi PMB', total: 1190, fresh: 369, noResp: 216, minat: 121, ragu: 11, t2Unlabeled: 0, menolak: 22, closing: 451, convPct: 37.90 },
    { category: 'Non Akademik', total: 298, fresh: 134, noResp: 69, minat: 13, ragu: 24, t2Unlabeled: 0, menolak: 8, closing: 50, convPct: 16.78 },
    { category: 'Open Booth (Mall/CFD/Public Area)', total: 273, fresh: 152, noResp: 118, minat: 1, ragu: 1, t2Unlabeled: 0, menolak: 1, closing: 0, convPct: 0.00 },
    { category: 'Akademik', total: 206, fresh: 0, noResp: 179, minat: 0, ragu: 0, t2Unlabeled: 1, menolak: 26, closing: 0, convPct: 0.00 },
    { category: 'Sosialisasi', total: 186, fresh: 186, noResp: 0, minat: 0, ragu: 0, t2Unlabeled: 0, menolak: 0, closing: 0, convPct: 0.00 },
    { category: 'BKOT/NKBO/TWOT', total: 87, fresh: 87, noResp: 0, minat: 0, ragu: 0, t2Unlabeled: 0, menolak: 0, closing: 0, convPct: 0.00 },
    { category: 'Open House (Hangout/NICE/Cybertalk)', total: 44, fresh: 17, noResp: 12, minat: 10, ragu: 2, t2Unlabeled: 0, menolak: 1, closing: 2, convPct: 4.55 },
    { category: 'Workshop', total: 24, fresh: 24, noResp: 0, minat: 0, ragu: 0, t2Unlabeled: 0, menolak: 0, closing: 0, convPct: 0.00 }
  ];

  const ADMIN_STRATEGIC_RECS = [
    {
      id: 'REC-ADM-01',
      cat: 'SLA',
      priority: 'KRITIS (SLA < 24 JAM)',
      badgeColor: 'bg-rose-100 text-rose-800 border-rose-300',
      title: '1. Intervensi Darurat Data Belum Di-assign',
      finding: 'Rasio unassigned tinggi pada beberapa kampus perlu intervensi cepat.',
      action: 'Terapkan kebijakan Auto-Assignment / Round-Robin di sistem CRM.',
      targetCluster: 'URGENT_UNASSIGNED',
      btnLabel: 'Lihat Kampus Darurat Unassigned'
    },
    {
      id: 'REC-ADM-02',
      cat: 'SLA',
      priority: 'TINGGI (SUPERVISI AGENT)',
      badgeColor: 'bg-amber-100 text-amber-800 border-amber-300',
      title: '2. Penertiban Data Fresh Tertahan di Agent',
      finding: 'Data sudah memiliki nama Agent CRM namun belum dihubungi.',
      action: 'Aktifkan pengingat SLA harian dan fitur tarik-ulang otomatis (Re-assign).',
      targetCluster: 'STALLED_ASSIGNED',
      btnLabel: 'Lihat Kampus Stagnan di Agent'
    },
    {
      id: 'REC-ADM-03',
      cat: 'CONV',
      priority: 'STRATEGIS (BENCHMARK)',
      badgeColor: 'bg-emerald-100 text-emerald-800 border-emerald-300',
      title: '3. Replikasi SOP Kampus Benchmark Konversi Tinggi',
      finding: 'Pengawalan intensif pada kanal Aplikasi PMB & event menghasilkan konversi tinggi.',
      action: 'Jadikan SOP pengawalan pendaftar PMB sebagai standar operasional nasional.',
      targetCluster: 'BENCHMARK_CLOSING',
      btnLabel: 'Lihat Kampus Benchmark Closing'
    }
  ];

  const state = {
    activeTab: '<?= $initialActiveTab ?>',
    adminRecCat: 'ALL',
    adminChartMode: 'VOL',
    crmSelectedCampusNo: <?= (int) ($matchedCampus['no'] ?? 1) ?>,
    crmCampusFilterGroup: 'ALL'
  };

  let crmAdminCampusChartInstance = null;
  let crmAdminCategoryChartInstance = null;
  let crmCampusFunnelChartInstance = null;
  let crmCampusEventResponseChartInstance = null;

  function classifyCampusCluster(crm) {
    const tot = Math.max(1, crm.total);
    const unassPct = (crm.unassigned / tot) * 100;
    const closePct = (crm.closing / tot) * 100;

    if (unassPct >= 70 && crm.unassigned >= 50) {
      return {
        code: 'URGENT_UNASSIGNED',
        badge: '🚨 Darurat Belum Assign',
        badgeClass: 'bg-rose-100 text-rose-800 border-rose-300',
        adminNote: `Terdapat ${crm.unassigned} siswa (${Math.round(unassPct)}%) belum ditugaskan ke agent.`
      };
    }
    if (crm.freshAssigned >= 50) {
      return {
        code: 'STALLED_ASSIGNED',
        badge: '⚠️ Fresh Tertahan di Agent',
        badgeClass: 'bg-amber-100 text-amber-800 border-amber-300',
        adminNote: `Sebanyak ${crm.freshAssigned} data sudah diassign namun belum difollow-up.`
      };
    }
    if (closePct >= 15 || crm.closing >= 20) {
      return {
        code: 'BENCHMARK_CLOSING',
        badge: '★ Benchmark Closing Tinggi',
        badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-300',
        adminNote: `Performa sangat baik dengan ${crm.closing} Closing (${closePct.toFixed(1)}%).`
      };
    }
    if (crm.noResp >= 100) {
      return {
        code: 'HIGH_NORESPONSE',
        badge: '🔄 Tidak Merespon Tinggi',
        badgeClass: 'bg-indigo-100 text-indigo-800 border-indigo-300',
        adminNote: `${crm.noResp} siswa belum membalas pesan. Butuh variasi pesan & follow up berkala.`
      };
    }
    return {
      code: 'BALANCED',
      badge: '✓ Operasional Normal',
      badgeClass: 'bg-slate-100 text-slate-700 border-slate-300',
      adminNote: 'Distribusi data dan alur follow-up berjalan dalam batas wajar.'
    };
  }

  function buildCampusSpecificCrmRecs(crm) {
    const recs = [];
    const safeTotal = Math.max(1, crm.total);
    const unassPct = Math.round((crm.unassigned / safeTotal) * 100);
    const warmTotal = (crm.minat || 0) + (crm.ragu || 0);

    if (crm.unassigned > 0) {
      recs.push({
        priority: 'PRIORITAS UTAMA • DISTRIBUSI KUOTA',
        color: 'border-rose-300 bg-rose-50/80 text-rose-950',
        badge: `${crm.unassigned} Belum Diassign (${unassPct}%)`,
        title: `Segera Bagikan ${crm.unassigned} Siswa ke Tim Agent CRM`,
        detail: `Terdapat ${crm.unassigned} prospek yang belum memiliki agent penanggung jawab. Distribusikan ke agent aktif kampus agar dapat segera dihubungi.`
      });
    }

    if (crm.freshAssigned > 0) {
      recs.push({
        priority: 'PRIORITAS EKSEKUSI • FOLLOW-UP DATA FRESH',
        color: 'border-amber-300 bg-amber-50/80 text-amber-950',
        badge: `${crm.freshAssigned} Siswa Fresh di Agent`,
        title: `Selesaikan Kontak Pertama untuk ${crm.freshAssigned} Data Fresh`,
        detail: `Sebanyak ${crm.freshAssigned} siswa sudah dipegang agent namun belum dihubungi. Prioritaskan kontak pertama via WhatsApp personal hari ini.`
      });
    }

    if (warmTotal > 0) {
      recs.push({
        priority: 'QUICK WINS • KONVERSI PROSPEK HANGAT',
        color: 'border-emerald-300 bg-emerald-50/80 text-emerald-950',
        badge: `${warmTotal} Prospek Hangat`,
        title: `Kawal Khusus ${crm.minat} Siswa "Minat" & ${crm.ragu} Siswa "Ragu-ragu"`,
        detail: `Peluang closing terbesar ada pada ${warmTotal} prospek hangat ini. Berikan panduan pendaftaran, info beasiswa, atau opsi cicilan biaya kuliah.`
      });
    }

    if (crm.noResp > 0) {
      recs.push({
        priority: 'RE-ENGAGEMENT • TIDAK MERESPON',
        color: 'border-indigo-300 bg-indigo-50/80 text-indigo-950',
        badge: `${crm.noResp} Tidak Merespon`,
        title: `Lakukan Pendekatan Ulang untuk ${crm.noResp} Siswa Tidak Merespon`,
        detail: `Kirimkan pesan sapaan ramah atau info event/tryout gratis pada jam santai (16.00-19.00 WIB) untuk membuka kembali percakapan.`
      });
    }

    if (recs.length === 0) {
      recs.push({
        priority: 'OPERASIONAL KAMPUS',
        color: 'border-slate-300 bg-slate-50 text-slate-900',
        badge: 'Status Baik',
        title: 'Pertahankan Ritme Follow-Up CRM Kampus',
        detail: 'Seluruh data terpantau berjalan sesuai tahapan. Lanjutkan koordinasi tim agent dan update status prospek secara berkala.'
      });
    }

    return recs;
  }

  function renderAdminOverview() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    updateAdminOverviewCharts();
  }

  function updateAdminOverviewCharts() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const isPct = state.adminChartMode === 'PCT';
    const topCampuses = CRM_CAMPUS_LIST.slice(0, 15);
    const labels = topCampuses.map((x) => x.kampus.replace('UBSI KAMPUS ', 'UBSI ').replace('UNM KAMPUS ', 'UNM '));
    const toVal = (val, tot) => (isPct ? Math.round((val / Math.max(1, tot)) * 1000) / 10 : val);

    const c1 = document.getElementById('crmAdminCampusChart');
    if (c1) {
      const ctx1 = c1.getContext('2d');
      if (crmAdminCampusChartInstance) crmAdminCampusChartInstance.destroy();
      crmAdminCampusChartInstance = new Chart(ctx1, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Fresh Unassigned',
              data: topCampuses.map((x) => toVal(x.freshUnassigned || 0, x.total)),
              backgroundColor: 'rgba(225, 29, 72, 0.85)'
            },
            {
              label: 'Fresh di Agent',
              data: topCampuses.map((x) => toVal(x.freshAssigned || 0, x.total)),
              backgroundColor: 'rgba(245, 158, 11, 0.85)'
            },
            {
              label: 'Tidak Merespon',
              data: topCampuses.map((x) => toVal(x.noResp || 0, x.total)),
              backgroundColor: 'rgba(100, 116, 139, 0.85)'
            },
            {
              label: 'Minat & Ragu-ragu',
              data: topCampuses.map((x) => toVal((x.minat || 0) + (x.ragu || 0), x.total)),
              backgroundColor: 'rgba(14, 165, 233, 0.9)'
            },
            {
              label: 'Menolak',
              data: topCampuses.map((x) => toVal(x.menolak || 0, x.total)),
              backgroundColor: 'rgba(168, 85, 247, 0.85)'
            },
            {
              label: 'Closing (PMB)',
              data: topCampuses.map((x) => toVal(x.closing || 0, x.total)),
              backgroundColor: 'rgba(16, 185, 129, 0.95)'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { stacked: true, ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 30 } },
            y: {
              stacked: true,
              beginAtZero: true,
              max: isPct ? 100 : undefined,
              ticks: { callback: (v) => (isPct ? v + '%' : v) }
            }
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 11, font: { size: 10 } } }
          }
        }
      });
    }

    const c2 = document.getElementById('crmAdminCategoryChart');
    if (c2) {
      const ctx2 = c2.getContext('2d');
      if (crmAdminCategoryChartInstance) crmAdminCategoryChartInstance.destroy();
      const topCats = CRM_CATEGORY_SUMMARY.slice(0, 6);
      crmAdminCategoryChartInstance = new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: topCats.map((c) => c.category.replace(' (Mall/CFD/Public Area)', '')),
          datasets: [
            {
              label: 'Closing (Siswa)',
              data: topCats.map((c) => c.closing),
              backgroundColor: 'rgba(16, 185, 129, 0.9)',
              borderRadius: 4
            },
            {
              label: 'Minat & Ragu',
              data: topCats.map((c) => c.minat + c.ragu),
              backgroundColor: 'rgba(14, 165, 233, 0.85)',
              borderRadius: 4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true }
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
          }
        }
      });
    }
  }

  function renderAdminMatrixAndAgents() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const tbody = document.getElementById('adminCrmTableBody');
    if (!tbody) return;

    const q = (document.getElementById('adminCrmSearchInput')?.value || '').trim().toLowerCase();
    const clusterFilter = document.getElementById('adminCrmClusterFilter')?.value || 'ALL';
    const sortVal = document.getElementById('adminCrmSortSelect')?.value || 'TOTAL_DESC';

    let rows = CRM_CAMPUS_LIST.map((crm) => ({ crm, cluster: classifyCampusCluster(crm) }));
    if (q) rows = rows.filter((r) => r.crm.kampus.toLowerCase().includes(q));
    if (clusterFilter !== 'ALL') rows = rows.filter((r) => r.cluster.code === clusterFilter);

    rows.sort((a, b) => {
      if (sortVal === 'TOTAL_DESC') return b.crm.total - a.crm.total;
      if (sortVal === 'CLOSING_DESC') return b.crm.closing - a.crm.closing;
      if (sortVal === 'UNASSIGNED_DESC') return b.crm.unassigned - a.crm.unassigned;
      if (sortVal === 'FRESH_DESC') return b.crm.fresh - a.crm.fresh;
      if (sortVal === 'SCORE_DESC') return b.crm.avgScore - a.crm.avgScore;
      return a.crm.no - b.crm.no;
    });

    tbody.innerHTML = '';
    rows.forEach(({ crm, cluster }, idx) => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-indigo-50/40 transition cursor-pointer';
      const safeTotal = Math.max(1, crm.total);
      const unassPct = Math.round((crm.unassigned / safeTotal) * 100);
      const closePct = ((crm.closing / safeTotal) * 100).toFixed(1);
      const warm = (crm.minat || 0) + (crm.ragu || 0);

      tr.innerHTML = `
        <td class="py-2.5 px-2.5 text-center font-medium text-slate-500">${idx + 1}</td>
        <td class="py-2.5 px-3 font-bold text-slate-900">
          <div>${crm.kampus}</div>
          <div class="text-[10px] font-semibold text-slate-400">${crm.events?.length || 0} Event Ber-Leads</div>
        </td>
        <td class="py-2.5 px-2.5 text-center font-extrabold text-slate-900">${crm.total.toLocaleString('id-ID')}</td>
        <td class="py-2.5 px-2.5 text-center font-bold ${crm.unassigned > 0 ? 'bg-rose-50/80 text-rose-800' : 'text-slate-400'}">
          ${crm.unassigned > 0 ? `${crm.unassigned.toLocaleString('id-ID')} <span class="text-[10px] block">(${unassPct}%)</span>` : '0'}
        </td>
        <td class="py-2.5 px-2.5 text-center font-bold ${crm.fresh > 0 ? 'bg-amber-50/80 text-amber-800' : 'text-slate-400'}">
          ${crm.fresh > 0 ? `${crm.fresh.toLocaleString('id-ID')}` : '0'}
        </td>
        <td class="py-2.5 px-2.5 text-center font-semibold ${crm.noResp > 0 ? 'text-slate-800' : 'text-slate-400'}">${crm.noResp || 0}</td>
        <td class="py-2.5 px-2.5 text-center font-bold ${warm > 0 ? 'bg-sky-50/80 text-sky-800' : 'text-slate-400'}">
          ${warm > 0 ? `${warm} <span class="text-[10px] block">(${crm.minat || 0}M/${crm.ragu || 0}R)</span>` : '0'}
        </td>
        <td class="py-2.5 px-2.5 text-center font-semibold ${crm.menolak > 0 ? 'text-rose-700' : 'text-slate-400'}">${crm.menolak || 0}</td>
        <td class="py-2.5 px-2.5 text-center font-extrabold ${crm.closing > 0 ? 'bg-emerald-50/90 text-emerald-800' : 'text-slate-400'}">
          ${crm.closing > 0 ? `${crm.closing} <span class="text-[10px] block">(${closePct}%)</span>` : '0'}
        </td>
        <td class="py-2.5 px-2 text-center font-bold text-indigo-700">${crm.avgScore || 0}</td>
        <td class="py-2.5 px-3">
          <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full border mb-1 ${cluster.badgeClass}">${cluster.badge}</span>
          <p class="text-[11px] text-slate-600 leading-snug">${cluster.adminNote}</p>
        </td>
        <td class="py-2.5 px-2.5 text-center">
          <button type="button" class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm whitespace-nowrap">
            🏢 Lihat Detail
          </button>
        </td>
      `;
      tr.addEventListener('click', () => openCrmCampusViewFor(crm.no));
      tbody.appendChild(tr);
    });
  }

  function getFilteredCrmCampusList() {
    const f = state.crmCampusFilterGroup;
    return CRM_CAMPUS_LIST.filter((crm) => {
      if (f === 'ALL') return true;
      return classifyCampusCluster(crm).code === f;
    });
  }

  function populateCrmCampusDropdown() {
    const sel = document.getElementById('crmCampusSelect');
    if (!sel) return;
    const list = getFilteredCrmCampusList();
    sel.innerHTML = '';

    if (!list.some((c) => c.no === state.crmSelectedCampusNo)) {
      state.crmSelectedCampusNo = list[0] ? list[0].no : 1;
    }

    list.forEach((crm) => {
      const opt = document.createElement('option');
      opt.value = crm.no;
      opt.textContent = `${crm.no}. ${crm.kampus} — (${crm.total.toLocaleString('id-ID')} Leads • ${crm.closing} Closing)`;
      if (crm.no === state.crmSelectedCampusNo) opt.selected = true;
      sel.appendChild(opt);
    });
  }

  function renderCrmCampusView() {
    const crm = CRM_CAMPUS_LIST.find((x) => x.no === state.crmSelectedCampusNo) || CRM_CAMPUS_LIST[0];
    if (!crm) return;
    const cluster = classifyCampusCluster(crm);
    const activeAgentsCount = (crm.agents || []).filter((a) => a.name !== 'Belum diassign').length;
    const safeTotal = Math.max(1, crm.total);

    const titleEl = document.getElementById('crmCampusRecTitle');
    if (titleEl) titleEl.textContent = crm.kampus;
    const headerSpan = document.getElementById('crmCampusHeaderNameSpan');
    if (headerSpan) headerSpan.textContent = crm.kampus;

    const badgeEl = document.getElementById('crmCampusClusterBadge');
    if (badgeEl) {
      badgeEl.textContent = cluster.badge;
      badgeEl.className = `px-2.5 py-0.5 text-xs font-extrabold rounded-full border ${cluster.badgeClass}`;
    }
    const metaEl = document.getElementById('crmCampusMetaSubtext');
    if (metaEl) {
      metaEl.textContent = `${crm.events?.length || 0} Event Ber-Leads • ${crm.total} Total Leads • ${activeAgentsCount} Agent Aktif`;
    }

    const countBadge = document.getElementById('crmCampusEventCountBadge');
    if (countBadge) {
      countBadge.textContent = `${crm.events?.length || 0} Event Terdaftar`;
    }

    // 6 Operational KPI Cards
    const kpiRow = document.getElementById('crmCampusKpiRow');
    const unassPct = crm.total > 0 ? Math.round((crm.unassigned / safeTotal) * 100) : 0;
    const closePct = crm.total > 0 ? ((crm.closing / safeTotal) * 100).toFixed(1) : '0.0';
    const warmTotal = (crm.minat || 0) + (crm.ragu || 0);

    if (kpiRow) {
      kpiRow.innerHTML = `
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Prospek Kampus</div>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">${crm.total.toLocaleString('id-ID')}</div>
          <div class="text-[11px] text-slate-500 mt-1">${crm.events?.length || 0} Event Ber-Leads</div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm ${crm.unassigned > 0 ? 'bg-rose-50/70 border-rose-200' : 'bg-emerald-50/50 border-emerald-200'}">
          <div class="text-[11px] font-bold uppercase tracking-wider ${crm.unassigned > 0 ? 'text-rose-800' : 'text-emerald-800'}">Belum Di-Assign</div>
          <div class="text-2xl font-extrabold ${crm.unassigned > 0 ? 'text-rose-700' : 'text-emerald-700'} mt-1">${crm.unassigned.toLocaleString('id-ID')} <span class="text-xs font-bold">(${unassPct}%)</span></div>
          <div class="text-[11px] text-slate-600 mt-1">Sudah Di-assign: <strong>${crm.assigned.toLocaleString('id-ID')}</strong></div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm ${crm.fresh > 0 ? 'bg-amber-50/70 border-amber-200' : 'bg-white border-slate-200'}">
          <div class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Saldo Fresh (Belum Kontak)</div>
          <div class="text-2xl font-extrabold text-amber-700 mt-1">${crm.fresh.toLocaleString('id-ID')}</div>
          <div class="text-[11px] text-amber-800 mt-1">Di Agent: <strong>${crm.freshAssigned || 0}</strong> • Unassigned: <strong>${crm.freshUnassigned || 0}</strong></div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm bg-sky-50/70 border-sky-200">
          <div class="text-[11px] font-bold uppercase tracking-wider text-sky-900">Prospek Hangat</div>
          <div class="text-2xl font-extrabold text-sky-700 mt-1">${warmTotal}</div>
          <div class="text-[11px] text-sky-800 mt-1"><strong>${crm.minat || 0} Minat</strong> • <strong>${crm.ragu || 0} Ragu</strong></div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm bg-emerald-50/70 border-emerald-200">
          <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-900">Closing (PMB)</div>
          <div class="text-2xl font-extrabold text-emerald-700 mt-1">${crm.closing} <span class="text-xs font-bold">(${closePct}%)</span></div>
          <div class="text-[11px] text-emerald-800 mt-1">Rata-rata Skor: <strong>${crm.avgScore || 0}</strong></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tdk Respon / Tolak</div>
          <div class="text-2xl font-extrabold text-slate-800 mt-1">${crm.noResp || 0} <span class="text-xs font-semibold text-rose-700">/ ${crm.menolak || 0} Tolak</span></div>
          <div class="text-[11px] text-slate-500 mt-1">${crm.schoolCount || 0} Sekolah Terdata</div>
        </div>
      `;
    }

    // Action plan
    const recs = buildCampusSpecificCrmRecs(crm);
    const recListEl = document.getElementById('crmCampusRecList');
    if (recListEl) {
      recListEl.innerHTML = '';
      recs.forEach((r) => {
        const div = document.createElement('div');
        div.className = `rounded-2xl p-4 border ${r.color} flex flex-col justify-between shadow-sm`;
        div.innerHTML = `
          <div>
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <span class="text-[10px] font-extrabold uppercase tracking-wider opacity-80">${r.priority}</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white/80 border border-current/20">${r.badge}</span>
            </div>
            <h4 class="text-sm font-extrabold leading-snug mb-1.5">${r.title}</h4>
            <p class="text-xs leading-relaxed opacity-90">${r.detail}</p>
          </div>
        `;
        recListEl.appendChild(div);
      });
    }

    // 6 Response Breakdown Pills
    const pillsEl = document.getElementById('crmCampusResponseSummaryPills');
    if (pillsEl) {
      const pClose = ((crm.closing / safeTotal) * 100).toFixed(1);
      const pMinat = (((crm.minat || 0) / safeTotal) * 100).toFixed(1);
      const pRagu = (((crm.ragu || 0) / safeTotal) * 100).toFixed(1);
      const pTolak = (((crm.menolak || 0) / safeTotal) * 100).toFixed(1);
      const pNoResp = (((crm.noResp || 0) / safeTotal) * 100).toFixed(1);
      const pFresh = (((crm.fresh || 0) / safeTotal) * 100).toFixed(1);

      pillsEl.innerHTML = `
        <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200">
          <div class="text-[10px] font-bold uppercase text-emerald-800">🟢 Closing</div>
          <div class="text-base font-extrabold text-emerald-700 mt-0.5">${crm.closing} <span class="text-[10px] font-medium">(${pClose}%)</span></div>
        </div>
        <div class="p-2 rounded-xl bg-sky-50 border border-sky-200">
          <div class="text-[10px] font-bold uppercase text-sky-800">🔵 Minat</div>
          <div class="text-base font-extrabold text-sky-700 mt-0.5">${crm.minat || 0} <span class="text-[10px] font-medium">(${pMinat}%)</span></div>
        </div>
        <div class="p-2 rounded-xl bg-amber-50 border border-amber-200">
          <div class="text-[10px] font-bold uppercase text-amber-800">🟡 Ragu-ragu</div>
          <div class="text-base font-extrabold text-amber-700 mt-0.5">${crm.ragu || 0} <span class="text-[10px] font-medium">(${pRagu}%)</span></div>
        </div>
        <div class="p-2 rounded-xl bg-rose-50 border border-rose-200">
          <div class="text-[10px] font-bold uppercase text-rose-800">🔴 Menolak</div>
          <div class="text-base font-extrabold text-rose-700 mt-0.5">${crm.menolak || 0} <span class="text-[10px] font-medium">(${pTolak}%)</span></div>
        </div>
        <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
          <div class="text-[10px] font-bold uppercase text-slate-600">⚫ Tdk Respon</div>
          <div class="text-base font-extrabold text-slate-800 mt-0.5">${crm.noResp || 0} <span class="text-[10px] font-medium">(${pNoResp}%)</span></div>
        </div>
        <div class="p-2 rounded-xl bg-orange-50 border border-orange-200">
          <div class="text-[10px] font-bold uppercase text-orange-800">🟠 Fresh</div>
          <div class="text-base font-extrabold text-orange-700 mt-0.5">${crm.fresh || 0} <span class="text-[10px] font-medium">(${pFresh}%)</span></div>
        </div>
      `;
    }

    // Events Table
    const evBody = document.getElementById('crmCampusEventTableBody');
    if (evBody) {
      evBody.innerHTML = '';
      const events = crm.events || [];
      if (events.length === 0) {
        evBody.innerHTML = `<tr><td colspan="13" class="py-4 text-center text-slate-500">Belum ada kegiatan/event yang ditambahkan leads pada kampus ini.</td></tr>`;
      } else {
        events.forEach((e) => {
          const evTot = Math.max(1, e.total);
          const evConv = ((e.closing / evTot) * 100).toFixed(1);
          let evRec = 'Follow-up berkala';
          if (e.unassigned > 0) {
            evRec = `🚨 Segera assign ${e.unassigned} data ke agent!`;
          } else if (e.fresh > 0) {
            evRec = `Hubungi ${e.fresh} leads Fresh yang sudah di-assign.`;
          } else if ((e.minat || 0) + (e.ragu || 0) > 0) {
            evRec = `Fokus closing ${(e.minat || 0) + (e.ragu || 0)} prospek hangat.`;
          } else if (e.closing > 0) {
            evRec = `Hasil bagus (${e.closing} closing). Pertahankan ritme.`;
          } else if (e.noResp > 0) {
            evRec = `Kirim ulang info beasiswa ke ${e.noResp} tidak merespon.`;
          }

          const tr = document.createElement('tr');
          tr.className = 'hover:bg-slate-50';
          tr.innerHTML = `
            <td class="py-2.5 px-3 font-bold text-slate-900">
              <span class="text-indigo-600 font-mono text-[11px] font-extrabold">${e.code}</span> — ${e.name}
            </td>
            <td class="py-2.5 px-2.5"><span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">${e.category || '-'}</span></td>
            <td class="py-2.5 px-2.5 text-slate-600">${e.date}<div class="text-[10px] text-slate-400">${e.location || '-'}</div></td>
            <td class="py-2.5 px-2 text-center font-extrabold">${e.total}</td>
            <td class="py-2.5 px-2 text-center font-bold ${e.unassigned > 0 ? 'text-rose-700 bg-rose-50' : 'text-slate-400'}">${e.unassigned || 0}</td>
            <td class="py-2.5 px-2 text-center font-bold ${e.fresh > 0 ? 'text-amber-700 bg-amber-50' : 'text-slate-400'}">${e.fresh || 0}</td>
            <td class="py-2.5 px-2 text-center text-slate-700">${e.noResp || 0}</td>
            <td class="py-2.5 px-2 text-center font-bold text-sky-700 bg-sky-50/40">${e.minat || 0}</td>
            <td class="py-2.5 px-2 text-center font-bold text-amber-700 bg-amber-50/40">${e.ragu || 0}</td>
            <td class="py-2.5 px-2 text-center text-rose-700">${e.menolak || 0}</td>
            <td class="py-2.5 px-2 text-center font-extrabold text-emerald-700 bg-emerald-50/60">${e.closing || 0}</td>
            <td class="py-2.5 px-2 text-center font-bold text-indigo-700">${evConv}%</td>
            <td class="py-2.5 px-3 text-[11px] font-medium text-slate-700">${evRec}</td>
          `;
          evBody.appendChild(tr);
        });
      }
    }

    // Agent Table
    const agBody = document.getElementById('crmCampusAgentTableBody');
    if (agBody) {
      agBody.innerHTML = '';
      const agents = crm.agents || [];
      if (agents.length === 0) {
        agBody.innerHTML = `<tr><td colspan="7" class="py-4 text-center text-slate-500">Belum ada data agent pada kampus ini.</td></tr>`;
      } else {
        agents.forEach((a) => {
          const isUnass = a.name === 'Belum diassign';
          const warm = (a.minat || 0) + (a.ragu || 0);
          let instruction = 'Pertahankan ritme kerja';
          if (isUnass) {
            instruction = `Wajib segera didistribusikan oleh Koordinator Kampus ke agent aktif!`;
          } else if (a.fresh === a.total && a.total > 0) {
            instruction = `Belum menyentuh data (${a.total} Fresh). Tegur & wajibkan kontak hari ini.`;
          } else if (a.fresh > 0) {
            instruction = `Selesaikan ${a.fresh} sisa data Fresh${warm > 0 ? ` & kawal ${warm} prospek hangat` : ''}.`;
          } else if (a.closing > 0) {
            instruction = `Performa baik (${a.closing} Closing). Lanjutkan follow up.`;
          }

          const tr = document.createElement('tr');
          tr.className = isUnass ? 'bg-rose-50/70 font-semibold' : 'hover:bg-slate-50';
          tr.innerHTML = `
            <td class="py-2 px-3 font-bold ${isUnass ? 'text-rose-800' : 'text-slate-900'}">${a.name}</td>
            <td class="py-2 px-2 text-center font-extrabold">${a.total}</td>
            <td class="py-2 px-2 text-center font-bold ${a.fresh > 0 ? 'text-amber-700' : 'text-slate-400'}">${a.fresh}</td>
            <td class="py-2 px-2 text-center">${a.noResp || 0}</td>
            <td class="py-2 px-2 text-center font-bold text-sky-700">${warm}</td>
            <td class="py-2 px-2 text-center font-extrabold text-emerald-700">${a.closing}</td>
            <td class="py-2 px-3 text-[11px] text-slate-700">${instruction}</td>
          `;
          agBody.appendChild(tr);
        });
      }
    }

    // Schools Table
    const schBody = document.getElementById('crmCampusSchoolTableBody');
    if (schBody) {
      schBody.innerHTML = '';
      const schools = crm.topSchools || [];
      if (schools.length === 0) {
        schBody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-slate-500">Belum ada rincian sekolah terdata.</td></tr>`;
      } else {
        schools.forEach((s) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="py-2 px-3 font-semibold text-slate-800">${s.name}</td>
            <td class="py-2 px-2 text-center font-bold">${s.total}</td>
            <td class="py-2 px-2 text-center text-sky-700 font-bold">${s.warm || 0}</td>
            <td class="py-2 px-2 text-center text-emerald-700 font-extrabold">${s.closing || 0}</td>
          `;
          schBody.appendChild(tr);
        });
      }
    }

    // Rejection Reasons
    const reasonEl = document.getElementById('crmCampusReasonList');
    if (reasonEl) {
      reasonEl.innerHTML = '';
      const reasons = crm.reasons || [];
      if (reasons.length === 0) {
        reasonEl.innerHTML = `<div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-500">Belum ada keterangan alasan penolakan yang dicatat di kampus ini.</div>`;
      } else {
        reasons.forEach((r) => {
          const item = document.createElement('div');
          item.className = 'flex items-center justify-between px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200';
          item.innerHTML = `<span class="font-medium text-slate-700">${r.reason}</span><span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">${r.count} siswa</span>`;
          reasonEl.appendChild(item);
        });
      }
    }

    updateCrmCampusCharts(crm);
    renderRecentLogsFeed();
  }

  function updateCrmCampusCharts(crm) {
    // 1. Donut Chart Tanggapan Hasil Respon
    const c1 = document.getElementById('crmCampusFunnelChart');
    if (c1) {
      const ctx1 = c1.getContext('2d');
      if (crmCampusFunnelChartInstance) crmCampusFunnelChartInstance.destroy();
      crmCampusFunnelChartInstance = new Chart(ctx1, {
        type: 'doughnut',
        data: {
          labels: [
            `Closing (${crm.closing})`,
            `Minat (${crm.minat || 0})`,
            `Ragu-ragu (${crm.ragu || 0})`,
            `Menolak (${crm.menolak || 0})`,
            `Tidak Merespon (${crm.noResp || 0})`,
            `Fresh Belum Kontak (${crm.fresh || 0})`
          ],
          datasets: [
            {
              data: [
                crm.closing,
                crm.minat || 0,
                crm.ragu || 0,
                crm.menolak || 0,
                crm.noResp || 0,
                crm.fresh || 0
              ],
              backgroundColor: [
                '#10b981', // Closing - Emerald
                '#0ea5e9', // Minat - Sky Blue
                '#f59e0b', // Ragu-ragu - Amber
                '#ef4444', // Menolak - Rose
                '#64748b', // Tidak Merespon - Slate
                '#f97316'  // Fresh - Orange
              ],
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 11, font: { size: 10 } } }
          }
        }
      });
    }

    // 2. Horizontal Stacked Bar Chart: Tanggapan Respon per Event di Kampus Ini
    const c2 = document.getElementById('crmCampusEventResponseChart');
    if (c2) {
      const ctx2 = c2.getContext('2d');
      if (crmCampusEventResponseChartInstance) crmCampusEventResponseChartInstance.destroy();
      const events = (crm.events || []).slice(0, 8);
      const evLabels = events.map((e) => (e.code + ': ' + e.name).slice(0, 24));

      crmCampusEventResponseChartInstance = new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: evLabels.length > 0 ? evLabels : ['Belum ada event'],
          datasets: [
            {
              label: 'Closing',
              data: events.map((e) => e.closing || 0),
              backgroundColor: '#10b981'
            },
            {
              label: 'Minat',
              data: events.map((e) => e.minat || 0),
              backgroundColor: '#0ea5e9'
            },
            {
              label: 'Ragu-ragu',
              data: events.map((e) => e.ragu || 0),
              backgroundColor: '#f59e0b'
            },
            {
              label: 'Menolak',
              data: events.map((e) => e.menolak || 0),
              backgroundColor: '#ef4444'
            },
            {
              label: 'Tdk Respon',
              data: events.map((e) => e.noResp || 0),
              backgroundColor: '#64748b'
            },
            {
              label: 'Fresh',
              data: events.map((e) => e.fresh || 0),
              backgroundColor: '#f97316'
            }
          ]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { stacked: true, beginAtZero: true },
            y: { stacked: true, ticks: { font: { size: 10 } } }
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
          }
        }
      });
    }
  }

  function renderRecentLogsFeed() {
    const tbody = document.getElementById('crmRecentLogsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const logs = CRM_RECENT_LOGS || [];
    if (logs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-slate-500">Belum ada rekaman log follow-up terbaru.</td></tr>`;
      return;
    }

    logs.slice(0, 25).forEach((log) => {
      let labelBadge = `<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px]">${log.new_label || log.new_stage || '-'}</span>`;
      const lb = (log.new_label || '').toLowerCase();
      if (lb.includes('closing')) {
        labelBadge = `<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">★ Closing</span>`;
      } else if (lb.includes('minat')) {
        labelBadge = `<span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-bold text-[10px]">Minat / Tertarik</span>`;
      } else if (lb.includes('ragu')) {
        labelBadge = `<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Ragu-ragu</span>`;
      } else if (lb.includes('menolak')) {
        labelBadge = `<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px]">Menolak</span>`;
      }

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-slate-50';
      tr.innerHTML = `
        <td class="py-2 px-3 text-slate-500 whitespace-nowrap">
          <div>${log.time_ago || log.created_at}</div>
          <div class="text-[9px] text-slate-400 font-mono">${log.created_at}</div>
        </td>
        <td class="py-2 px-3 font-bold text-slate-900">${log.student_name}</td>
        <td class="py-2 px-3 text-slate-600">
          <div class="font-semibold text-slate-800">${log.campus_name}</div>
          <div class="text-[10px] text-indigo-600 font-mono font-medium">${log.event_code} — ${log.event_title}</div>
        </td>
        <td class="py-2 px-2.5 font-medium text-slate-700">${log.agent_name}</td>
        <td class="py-2 px-2 text-center">${labelBadge}</td>
        <td class="py-2 px-3 text-slate-600 max-w-xs truncate" title="${log.summary}">${log.summary}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  function switchTab(tabName) {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) {
      tabName = 'campus-scoped';
    }
    state.activeTab = tabName;
    const tabs = ['admin-overview', 'admin-matrix', 'campus-scoped', 'access-guide'];
    tabs.forEach((t) => {
      const sec = document.getElementById(`view-${t}`);
      const btn = document.getElementById(`tabBtn-${t}`);
      if (sec) {
        if (t === tabName) {
          sec.classList.remove('hidden');
        } else {
          sec.classList.add('hidden');
        }
      }
      if (btn) {
        if (t === tabName) {
          btn.className = 'nav-tab-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all bg-indigo-600 text-white shadow';
        } else {
          btn.className = 'nav-tab-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60';
        }
      }
    });
    if (tabName === 'admin-overview') renderAdminOverview();
    if (tabName === 'admin-matrix') renderAdminMatrixAndAgents();
    if (tabName === 'campus-scoped') renderCrmCampusView();
  }

  function openCrmCampusViewFor(campusNo) {
    state.crmSelectedCampusNo = campusNo;
    state.crmCampusFilterGroup = 'ALL';
    const qf = document.getElementById('crmCampusQuickFilter');
    if (qf) qf.value = 'ALL';
    populateCrmCampusDropdown();
    switchTab('campus-scoped');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  async function syncRealtimeCrmData() {
    const btn = document.getElementById('crmRealtimeSyncBtn');
    const btnText = document.getElementById('crmRealtimeSyncBtnText');
    const timeText = document.getElementById('crmRealtimeTimeText');

    if (btn) btn.disabled = true;
    if (btnText) btnText.innerHTML = `<span>Menyinkronkan...</span>`;

    try {
      const campusParam = state.crmSelectedCampusNo || '';
      const res = await fetch(`<?= site_url('laporan-crm/realtime-data') ?>?campus=${encodeURIComponent(campusParam)}`);
      if (!res.ok) throw new Error('Network error');
      const data = await res.json();

      if (data && data.success) {
        CRM_CAMPUS_LIST = data.campuses || CRM_CAMPUS_LIST;
        CRM_AGENTS_GLOBAL = data.agents || CRM_AGENTS_GLOBAL;
        CRM_RECENT_LOGS = data.recentLogs || CRM_RECENT_LOGS;
        CRM_MACRO_SUMMARY = data.macro || CRM_MACRO_SUMMARY;

        if (timeText && data.timestamp) {
          timeText.textContent = data.timestamp;
        }

        populateCrmCampusDropdown();
        if (state.activeTab === 'admin-overview') renderAdminOverview();
        if (state.activeTab === 'admin-matrix') renderAdminMatrixAndAgents();
        renderCrmCampusView();
        renderRecentLogsFeed();

        if (btnText) btnText.innerHTML = `<span>✓ Data Terkini</span>`;
        setTimeout(() => {
          if (btnText) btnText.innerHTML = `<span>Refresh Real-Time</span>`;
          if (btn) btn.disabled = false;
        }, 1200);

        showToast('Data CRM, detail event, dan grafik respon real-time berhasil diperbarui!');
      }
    } catch (err) {
      console.error('Error syncing real-time CRM data:', err);
      if (btnText) btnText.innerHTML = `<span>Gagal Sync</span>`;
      setTimeout(() => {
        if (btnText) btnText.innerHTML = `<span>Refresh Real-Time</span>`;
        if (btn) btn.disabled = false;
      }, 1500);
    }
  }

  function showToast(msg) {
    const toast = document.getElementById('toastNotice');
    if (toast) {
      toast.textContent = msg;
      toast.classList.remove('hidden');
      setTimeout(() => toast.classList.add('hidden'), 2500);
    }
  }

  function exportAdminCrmCsv() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const headers = ['No', 'Kampus', 'Total_CRM', 'Belum_Diassign', 'Fresh', 'Tidak_Merespon', 'Minat', 'Ragu_Ragu', 'Menolak', 'Closing', 'Rata_Skor', 'Status_Klaster'];
    const lines = [headers.join(',')];
    CRM_CAMPUS_LIST.forEach((crm) => {
      const cl = classifyCampusCluster(crm);
      lines.push([
        crm.no, `"${crm.kampus}"`, crm.total, crm.unassigned, crm.fresh,
        crm.noResp, crm.minat, crm.ragu, crm.menolak, crm.closing,
        crm.avgScore, `"${cl.badge}"`
      ].join(','));
    });
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'laporan-crm-realtime-semua-kampus.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast('File CSV berhasil diunduh!');
  }

  function exportSingleCampusCrmCsv() {
    const crm = CRM_CAMPUS_LIST.find((x) => x.no === state.crmSelectedCampusNo) || CRM_CAMPUS_LIST[0];
    if (!crm) return;
    const lines = [
      `LAPORAN CRM DETAIL - ${crm.kampus}`,
      `Total Prospek,${crm.total},Belum Diassign,${crm.unassigned},Fresh,${crm.fresh},Tidak Merespon,${crm.noResp},Minat,${crm.minat},Ragu-ragu,${crm.ragu},Closing,${crm.closing}`,
      '',
      'DETAIL EVENT YANG MEMILIKI LEADS:',
      'Kode Event,Nama Event,Kategori,Tanggal,Total Leads,Unassigned,Fresh,Tidak Merespon,Minat,Ragu,Menolak,Closing'
    ];
    (crm.events || []).forEach((e) => {
      lines.push(`"${e.code}","${e.name.replace(/"/g, '""')}","${e.category}","${e.date}",${e.total},${e.unassigned},${e.fresh},${e.noResp},${e.minat || 0},${e.ragu || 0},${e.menolak || 0},${e.closing || 0}`);
    });
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `laporan-crm-${crm.kampus.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast('File CSV berhasil diunduh!');
  }

  window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-tab-btn').forEach((btn) => {
      btn.addEventListener('click', () => switchTab(btn.getAttribute('data-tab')));
    });

    document.getElementById('crmRealtimeSyncBtn')?.addEventListener('click', syncRealtimeCrmData);

    document.getElementById('adminChartModeVolBtn')?.addEventListener('click', () => {
      state.adminChartMode = 'VOL';
      document.getElementById('adminChartModeVolBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-slate-900 shadow-sm';
      document.getElementById('adminChartModePctBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900';
      updateAdminOverviewCharts();
    });

    document.getElementById('adminChartModePctBtn')?.addEventListener('click', () => {
      state.adminChartMode = 'PCT';
      document.getElementById('adminChartModePctBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-slate-900 shadow-sm';
      document.getElementById('adminChartModeVolBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900';
      updateAdminOverviewCharts();
    });

    document.getElementById('jumpToCampusViewBtn')?.addEventListener('click', () => switchTab('campus-scoped'));
    document.getElementById('exportAdminCsvBtn1')?.addEventListener('click', exportAdminCrmCsv);
    document.getElementById('exportAdminCsvBtn2')?.addEventListener('click', exportAdminCrmCsv);

    document.getElementById('adminCrmSearchInput')?.addEventListener('input', renderAdminMatrixAndAgents);
    document.getElementById('adminCrmClusterFilter')?.addEventListener('change', renderAdminMatrixAndAgents);
    document.getElementById('adminCrmSortSelect')?.addEventListener('change', renderAdminMatrixAndAgents);

    populateCrmCampusDropdown();
    document.getElementById('crmCampusSelect')?.addEventListener('change', (e) => {
      state.crmSelectedCampusNo = parseInt(e.target.value, 10);
      renderCrmCampusView();
    });

    document.getElementById('crmCampusQuickFilter')?.addEventListener('change', (e) => {
      state.crmCampusFilterGroup = e.target.value;
      populateCrmCampusDropdown();
      renderCrmCampusView();
    });

    document.getElementById('crmPrevCampusBtn')?.addEventListener('click', () => {
      const list = getFilteredCrmCampusList();
      if (!list.length) return;
      const idx = list.findIndex((c) => c.no === state.crmSelectedCampusNo);
      const prevIdx = (idx - 1 + list.length) % list.length;
      state.crmSelectedCampusNo = list[prevIdx].no;
      populateCrmCampusDropdown();
      renderCrmCampusView();
    });

    document.getElementById('crmNextCampusBtn')?.addEventListener('click', () => {
      const list = getFilteredCrmCampusList();
      if (!list.length) return;
      const idx = list.findIndex((c) => c.no === state.crmSelectedCampusNo);
      const nextIdx = (idx + 1) % list.length;
      state.crmSelectedCampusNo = list[nextIdx].no;
      populateCrmCampusDropdown();
      renderCrmCampusView();
    });

    document.getElementById('backToAdminViewBtn')?.addEventListener('click', () => switchTab('admin-overview'));
    document.getElementById('exportSingleCampusCrmBtn')?.addEventListener('click', exportSingleCampusCrmCsv);

    switchTab(state.activeTab);
    if (CAN_VIEW_ALL_CRM_CAMPUSES) {
      renderAdminOverview();
      renderAdminMatrixAndAgents();
    }
    renderCrmCampusView();

    // Background real-time polling every 30 seconds
    setInterval(syncRealtimeCrmData, 30000);
  });
</script>