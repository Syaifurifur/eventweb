eb<?php
$campuses = $campuses ?? [];
$schoolReports = $schoolReports ?? [];
$bannerReports = $bannerReports ?? [];
$ojtReports = $ojtReports ?? [];
$selectedOneCampusId = $selectedOneCampusId ?? null;
$selectedOneCampusName = $selectedOneCampusName ?? '';
$selectedProgram = $selectedProgram ?? 'all';
if (! in_array($selectedProgram, ['ojt', 'school', 'banner', 'all'], true)) {
    $selectedProgram = 'all';
}

$campusQuery = $selectedOneCampusId ? ('?campus_id=' . urlencode((string) $selectedOneCampusId)) : '';
$selectedPmbPeriod = $selectedPmbPeriod ?? 'September 2026 - September 2027';
$availablePmbPeriods = $availablePmbPeriods ?? [
    'September 2026 - September 2027' => 'September 2026 - September 2027 (Berjalan)',
    'September 2025 - September 2026' => 'September 2025 - September 2026',
    'September 2024 - September 2025' => 'September 2024 - September 2025',
    'September 2027 - September 2028' => 'September 2027 - September 2028 (Mendatang)',
];
$pmbPeriodCounts = $pmbPeriodCounts ?? [];
$gdriveSettings = $gdriveSettings ?? [
    'admin_email' => 'admin.markom@bsi.ac.id',
    'folder_url' => 'https://drive.google.com/drive/folders/1MOU-BJS-SEKOLAH-BSI-GROUP',
    'folder_name' => 'MOU BJS Sekolah - BSI Group',
];
$schoolExportQuery = $campusQuery . ($selectedPmbPeriod !== '' ? (($campusQuery !== '' ? '&' : '?') . 'pmb_period=' . urlencode($selectedPmbPeriod)) : '');
$currentProgramUrl = match ($selectedProgram) {
    'ojt' => site_url('one-campus/ojt'),
    'school' => site_url('one-campus/schools'),
    'banner' => site_url('one-campus/banners'),
    default => site_url('one-campus'),
};

$showOjt = in_array($selectedProgram, ['ojt', 'all'], true);
$showSchool = in_array($selectedProgram, ['school', 'all'], true);
$showBanner = in_array($selectedProgram, ['banner', 'all'], true);

$dateValue = static fn (mixed $value): string => preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value, $matches) === 1 ? $matches[0] : '';
$dateTimeLocalValue = static fn (mixed $value): string => strtotime((string) $value) !== false ? date('Y-m-d\TH:i', strtotime((string) $value)) : '';
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">Laporan Kepala Kampus &bull; Pengelolaan CRUD &amp; Rekap XLSX</span>
        <h2>
            <?php if ($selectedProgram === 'ojt'): ?>
                One Campus Five OJT
            <?php elseif ($selectedProgram === 'school'): ?>
                One Campus Sixty School
            <?php elseif ($selectedProgram === 'banner'): ?>
                One Campus Five Spanduk
            <?php else: ?>
                Rekap &amp; Pengelolaan One Campus Programs
            <?php endif; ?>
        </h2>
    </div>
    <div class="toolbar-summary">
        <span class="status status-selesai"><?= esc((string) count($ojtReports)) ?> Five OJT</span>
        <span class="status status-aktif"><?= esc((string) count($schoolReports)) ?> Sixty School</span>
        <span class="status status-diajukan"><?= esc((string) count($bannerReports)) ?> Five Spanduk</span>
    </div>
</div>

<!-- Sub-Menu Navigation Bar -->
<section class="panel" style="padding: 14px 18px; margin-bottom: 18px;">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <a href="<?= site_url('one-campus/ojt') . $campusQuery ?>"
               class="<?= $selectedProgram === 'ojt' ? 'primary-button compact-button' : 'ghost-button compact-button' ?>">
                One Campus Five OJT (<?= esc((string) count($ojtReports)) ?>)
            </a>
            <a href="<?= site_url('one-campus/schools') . $campusQuery ?>"
               class="<?= $selectedProgram === 'school' ? 'primary-button compact-button' : 'ghost-button compact-button' ?>">
                One Campus Sixty School (<?= esc((string) count($schoolReports)) ?>)
            </a>
            <a href="<?= site_url('one-campus/banners') . $campusQuery ?>"
               class="<?= $selectedProgram === 'banner' ? 'primary-button compact-button' : 'ghost-button compact-button' ?>">
                One Campus Five Spanduk (<?= esc((string) count($bannerReports)) ?>)
            </a>
            <a href="<?= site_url('one-campus') . $campusQuery ?>"
               class="<?= $selectedProgram === 'all' ? 'primary-button compact-button' : 'ghost-button compact-button' ?>">
                Semua Rekap One Campus
            </a>
        </div>

        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <?php if ($showOjt): ?>
                <a class="ghost-button compact-button" href="<?= site_url('one-campus/ojt/export') . $campusQuery ?>">
                    Download XLSX Five OJT
                </a>
            <?php endif; ?>
            <?php if ($showSchool): ?>
                <a class="ghost-button compact-button" href="<?= site_url('one-campus/schools/export') . $schoolExportQuery ?>">
                    Download XLSX Sixty School
                </a>
            <?php endif; ?>
            <?php if ($showBanner): ?>
                <a class="ghost-button compact-button" href="<?= site_url('one-campus/banners/export') . $campusQuery ?>">
                    Download XLSX Five Spanduk
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Filter Kampus Rekap -->
<section class="panel one-campus-recap-filter">
    <form class="report-filter-bar" action="<?= $currentProgramUrl ?>" method="get" data-campus-combobox>
        <label class="combo-field">
            Filter kampus rekap
            <input
                class="control"
                type="text"
                list="one-campus-recap-campus-options"
                value="<?= esc($selectedOneCampusName) ?>"
                placeholder="Cari ID atau nama kampus"
                autocomplete="off"
            >
            <input type="hidden" name="campus_id" value="<?= esc((string) ($selectedOneCampusId ?? '')) ?>">
            <datalist id="one-campus-recap-campus-options">
                <?php foreach ($campuses as $campus): ?>
                    <option value="ID <?= esc((string) $campus['id']) ?> - <?= esc($campus['name']) ?>" data-id="<?= esc((string) $campus['id']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </label>
        <div class="report-filter-actions">
            <button class="primary-button compact-button" type="submit">Terapkan Filter</button>
            <a class="ghost-button compact-button" href="<?= $currentProgramUrl ?>">Reset</a>
        </div>
    </form>
</section>

<?php if ($showOjt): ?>
<section class="panel" id="section-ojt">
    <div class="panel-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
        <div>
            <span class="eyebrow">Sub Menu 1 &bull; Target Minimal 5 Peserta OJT / Kampus</span>
            <h2>Laporan Rekap &amp; CRUD: One Campus Five OJT</h2>
        </div>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <a class="primary-button compact-button" href="<?= site_url('one-campus/ojt/export') . $campusQuery ?>">
                Download Hasil XLSX (Five OJT)
            </a>
        </div>
    </div>

    <details class="panel management-drawer one-campus-drawer" style="margin-bottom: 18px;" <?= $selectedProgram === 'ojt' ? 'open' : '' ?>>
        <summary>
            <div>
                <span class="eyebrow">Tambah Data Baru</span>
                <strong>Input Laporan One Campus Five OJT</strong>
            </div>
            <span class="ghost-button compact-button">Form Input OJT</span>
        </summary>
        <form class="drawer-form one-campus-form" action="<?= site_url('one-campus/ojt') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Kampus
                <select class="control" name="campus_id">
                    <option value="">Pilih kampus</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?= esc($campus['id']) ?>" <?= (string) ($selectedOneCampusId ?? '') === (string) $campus['id'] ? 'selected' : '' ?>><?= esc($campus['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Nama Anak OJT
                <input class="control" type="text" name="student_name" required>
            </label>
            <label>
                Asal Sekolah/Kampus
                <input class="control" type="text" name="origin_school" required>
            </label>
            <label>
                Jurusan
                <input class="control" type="text" name="major">
            </label>
            <label>
                No Handphone
                <input class="control" type="text" name="phone">
            </label>
            <label>
                Bulan Awal OJT
                <input class="control" type="date" name="ojt_start_month" required>
            </label>
            <label>
                Bulan Akhir OJT
                <input class="control" type="date" name="ojt_end_month" required>
            </label>
            <label class="span-2">
                Keterangan
                <textarea class="control" name="notes" rows="3"></textarea>
            </label>
            <button class="primary-button" type="submit">Simpan Data OJT</button>
        </form>
    </details>

    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari OJT, sekolah, jurusan, periode, kampus, atau catatan" data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>
    <div class="table-wrap">
        <table class="data-table" data-paginated-table data-page-size="10">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kampus</th>
                    <th>Nama Anak OJT</th>
                    <th>Asal Sekolah/Kampus</th>
                    <th>Jurusan</th>
                    <th>No Handphone</th>
                    <th>Periode OJT</th>
                    <th>Keterangan</th>
                    <th>Aksi CRUD</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ojtReports as $index => $row): ?>
                    <?php
                    $ojtFormId = 'one-campus-ojt-update-' . $row['id'];
                    $ojtSearchText = implode(' ', [
                        $row['campus_name'] ?? '',
                        $row['student_name'] ?? '',
                        $row['origin_school'] ?? '',
                        $row['major'] ?? '',
                        $row['phone'] ?? '',
                        $row['ojt_period'] ?? '',
                        $row['notes'] ?? '',
                    ]);
                    ?>
                    <tr data-page-row data-search-text="<?= esc($ojtSearchText, 'attr') ?>">
                        <td><?= esc((string) ($index + 1)) ?></td>
                        <td><?= esc($row['campus_name'] ?? '-') ?></td>
                        <td><?= esc($row['student_name']) ?></td>
                        <td><?= esc($row['origin_school']) ?></td>
                        <td><?= esc($row['major'] ?? '-') ?></td>
                        <td><?= esc($row['phone'] ?? '-') ?></td>
                        <td><?= esc($row['ojt_period']) ?></td>
                        <td><?= esc($row['notes'] ?? '-') ?></td>
                        <td>
                            <details class="row-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <div class="row-edit-panel">
                                    <form id="<?= esc($ojtFormId) ?>" action="<?= site_url('one-campus/ojt/' . $row['id'] . '/update') ?>" method="post" class="form-grid compact-edit-grid">
                                        <?= csrf_field() ?>
                                        <label>
                                            Kampus
                                            <select class="control table-control" name="campus_id">
                                                <option value="">Pilih kampus</option>
                                                <?php foreach ($campuses as $campus): ?>
                                                    <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($row['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                                                        <?= esc($campus['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Nama Anak OJT
                                            <input class="control table-control" type="text" name="student_name" value="<?= esc($row['student_name'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Asal Sekolah/Kampus
                                            <input class="control table-control" type="text" name="origin_school" value="<?= esc($row['origin_school'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Jurusan
                                            <input class="control table-control" type="text" name="major" value="<?= esc($row['major'] ?? '', 'attr') ?>">
                                        </label>
                                        <label>
                                            No Handphone
                                            <input class="control table-control" type="text" name="phone" value="<?= esc($row['phone'] ?? '', 'attr') ?>">
                                        </label>
                                        <label>
                                            Bulan Awal OJT
                                            <input class="control table-control" type="date" name="ojt_start_month" value="<?= esc($dateValue($row['ojt_start_month'] ?? ''), 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Bulan Akhir OJT
                                            <input class="control table-control" type="date" name="ojt_end_month" value="<?= esc($dateValue($row['ojt_end_month'] ?? ''), 'attr') ?>" required>
                                        </label>
                                        <label class="span-2">
                                            Keterangan
                                            <textarea class="control table-control" name="notes" rows="3"><?= esc($row['notes'] ?? '') ?></textarea>
                                        </label>
                                    </form>
                                    <div class="button-row">
                                        <button class="primary-button compact-button" form="<?= esc($ojtFormId) ?>" type="submit">Simpan OJT</button>
                                        <form action="<?= site_url('one-campus/ojt/' . $row['id'] . '/delete') ?>" method="post" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button class="danger-button compact-button" type="submit">Hapus OJT</button>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($ojtReports === []): ?>
                    <tr data-empty-row>
                        <td colspan="9"><span class="muted-note">Tidak ada rekap One Campus Five OJT untuk kampus ini.</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination-bar table-pagination-bar" aria-label="Navigasi rekap One Campus Five OJT" data-table-pagination></nav>
</section>
<?php endif; ?>

<?php if ($showSchool): ?>
<section class="panel" id="section-school">
    <div class="panel-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
        <div>
            <span class="eyebrow">Sub Menu 2 &bull; Target Minimal 60 Sekolah / Kampus</span>
            <h2>Laporan Rekap &amp; CRUD: One Campus Sixty School</h2>
            <p style="margin: 4px 0 0 0; font-size: 0.88rem; color: var(--text-muted, #64748b);">
                Klasterisasi Sekolah Mitra PMB, Pendataan Beasiswa Jalur Undangan (BJS), dan Arsip Media MOU ke Google Drive Admin.
            </p>
        </div>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <a class="primary-button compact-button" href="<?= site_url('one-campus/schools/export') . $schoolExportQuery ?>">
                Download Hasil XLSX (Sixty School)
            </a>
        </div>
    </div>

    <!-- Panel Pengaturan Google Drive Admin Akun MOU & Upload Otomatis -->
    <details class="panel management-drawer one-campus-drawer" style="margin-bottom: 16px; border-left: 4px solid #0d9488;">
        <summary style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="eyebrow" style="color: #0d9488;">Integrasi Langsung Google Drive</span>
                <strong>Setting Akun, Folder &amp; Webhook Upload Langsung ke Drive Admin</strong>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <?php if (! empty($gdriveSettings['webhook_url'])): ?>
                    <span class="status status-selesai" style="font-size: 0.74rem;">&#10004; Webhook Aktif</span>
                <?php else: ?>
                    <span class="status status-menunggu" style="font-size: 0.74rem;">Webhook Belum Diisi</span>
                <?php endif; ?>
                <span class="ghost-button compact-button">Konfigurasi Drive Admin</span>
            </div>
        </summary>
        <div style="padding-top: 14px;">
            <form action="<?= site_url('one-campus/gdrive-settings') ?>" method="post">
                <?= csrf_field() ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <label>
                        <span style="font-weight: 600; font-size: 0.84rem; display: block; margin-bottom: 4px;">Email Akun Google Drive Admin</span>
                        <input class="control" type="email" name="gdrive_admin_email" value="<?= esc($gdriveSettings['admin_email']) ?>" placeholder="admin.markom@bsi.ac.id" required>
                    </label>
                    <label>
                        <span style="font-weight: 600; font-size: 0.84rem; display: block; margin-bottom: 4px;">URL / ID Folder Google Drive MOU</span>
                        <input class="control" type="text" name="gdrive_mou_folder_url" value="<?= esc($gdriveSettings['folder_url']) ?>" placeholder="https://drive.google.com/drive/folders/..." required>
                    </label>
                    <label>
                        <span style="font-weight: 600; font-size: 0.84rem; display: block; margin-bottom: 4px;">Label Nama Folder Drive</span>
                        <input class="control" type="text" name="gdrive_mou_folder_name" value="<?= esc($gdriveSettings['folder_name']) ?>" placeholder="MOU BJS Sekolah - BSI Group" required>
                    </label>
                </div>

                <!-- Baris URL Webhook Google Apps Script untuk Upload Langsung -->
                <div style="background: var(--bg-hover, #f8fafc); border: 1px solid var(--border-color, #cbd5e1); border-radius: 8px; padding: 14px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                        <span style="font-weight: 700; font-size: 0.86rem; color: var(--text-main, #1e293b); display: inline-flex; align-items: center; gap: 6px;">
                            <span>&#9729;</span> Webhook URL Google Apps Script (Upload Otomatis Langsung)
                        </span>
                        <a href="https://script.google.com/" target="_blank" rel="noopener noreferrer" style="font-size: 0.78rem; color: #0284c7; text-decoration: underline;">
                            Buka Google Apps Script &nearr;
                        </a>
                    </div>
                    <label style="display: block; margin-bottom: 8px;">
                        <input class="control" type="url" name="gdrive_webhook_url" value="<?= esc($gdriveSettings['webhook_url']) ?>" placeholder="https://script.google.com/macros/s/AKfycb.../exec">
                    </label>
                    <div style="display: align-items: center; gap: 8px;">
                        <input type="checkbox" id="gdrive_auto_upload_blade" name="gdrive_auto_upload_enabled" value="1" <?= ! empty($gdriveSettings['auto_upload_enabled']) ? 'checked' : '' ?>>
                        <label for="gdrive_auto_upload_blade" style="font-size: 0.84rem; cursor: pointer; font-weight: 600; color: var(--text-main, #1e293b);">
                            Aktifkan unggah otomatis langsung ke Google Drive Admin saat file MOU PDF disimpan
                        </label>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <button class="primary-button compact-button" type="submit" style="background: #0d9488; border-color: #0d9488;">
                        Simpan Setting Drive
                    </button>
                    <?php if (! empty($gdriveSettings['folder_url'])): ?>
                        <a href="<?= esc($gdriveSettings['folder_url']) ?>" target="_blank" rel="noopener noreferrer" class="ghost-button compact-button" style="display: inline-flex; align-items: center; gap: 4px;">
                            Buka Folder Drive Admin &nearr;
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Aksi Tes Koneksi & Kode Apps Script Siap Pakai -->
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--border-color, #e2e8f0); display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
                <form action="<?= site_url('one-campus/gdrive-test') ?>" method="post" style="display: inline-flex; gap: 8px;">
                    <?= csrf_field() ?>
                    <button type="submit" class="ghost-button compact-button" style="color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 4px;">
                        &#9889; Tes Koneksi Webhook Drive
                    </button>
                </form>

                <details style="flex: 1; min-width: 280px;">
                    <summary class="ghost-button compact-button" style="cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                        &#128203; Lihat Kode Script Google Drive (Siap Pakai)
                    </summary>
                    <div style="margin-top: 8px; background: #0f172a; color: #f8fafc; padding: 14px; border-radius: 6px; font-family: monospace; font-size: 0.78rem; overflow-x: auto; max-height: 250px;">
                        <pre style="margin: 0; white-space: pre-wrap;"><?= esc($gdriveSettings['apps_script_template'] ?? '') ?></pre>
                    </div>
                </details>
            </div>

            <div style="margin-top: 10px; background: rgba(13, 148, 136, 0.08); border-left: 3px solid #0d9488; padding: 10px 14px; border-radius: 4px; font-size: 0.8rem; color: var(--text-main, #1e293b);">
                <strong>💡 Cara Mengatasi Pesan "Akses ditolak: DriveApp":</strong>
                <ol style="margin: 6px 0 0 18px; padding: 0;">
                    <li>Di editor Apps Script, pilih fungsi <code>setupOtorisasi</code> lalu klik <strong>Run (Jalankan)</strong> 1x untuk memberikan izin Google Drive (<em>Review permissions &rarr; Advanced &rarr; Go to ... (unsafe) &rarr; Allow</em>).</li>
                    <li>Saat Deploy: pastikan <strong>Execute as: Me (Saya)</strong> dan <strong>Who has access: Anyone (Siapa saja)</strong>.</li>
                    <li>Jika memperbarui kode, buka <strong>Deploy &rarr; Manage deployments &rarr; Edit (ikon pensil) &rarr; Version: New version &rarr; Deploy</strong>.</li>
                </ol>
            </div>

            <p style="margin: 10px 0 0 0; font-size: 0.8rem; color: var(--text-muted, #64748b);">
                Ketika webhook Google Apps Script aktif, setiap kali Anda atau kampus mengunggah file MOU PDF, sistem akan otomatis mengirimkannya langsung ke folder Google Drive akun admin (<strong><?= esc($gdriveSettings['admin_email']) ?></strong>) dan tautan Drive langsung tersimpan di database.
            </p>
        </div>
    </details>

    <!-- Klasterisasi Berdasarkan Tahun PMB (Sedang Berjalan & Sebelumnya) -->
    <div class="pmb-cluster-container" style="background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 14px 18px; margin-bottom: 18px;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                <span style="font-size: 0.86rem; font-weight: 700; color: var(--text-main, #1e293b); margin-right: 6px;">
                    Klaster Tahun PMB:
                </span>
                <?php foreach ($availablePmbPeriods as $pKey => $pLabel): ?>
                    <?php $isActive = ($selectedPmbPeriod === $pKey); ?>
                    <a href="<?= site_url('one-campus/schools?pmb_period=' . urlencode($pKey) . (! empty($selectedSort) ? '&sort=' . urlencode($selectedSort) : '') . ($selectedOneCampusId ? '&campus_id=' . urlencode((string) $selectedOneCampusId) : '')) ?>"
                       class="<?= $isActive ? 'primary-button compact-button' : 'ghost-button compact-button' ?>"
                       style="<?= $isActive ? 'background: #0d9488; border-color: #0d9488; color: #ffffff; font-weight: 700;' : '' ?>">
                        <?= esc($pLabel) ?>
                        <span style="display: inline-block; padding: 1px 7px; margin-left: 6px; border-radius: 999px; font-size: 0.76rem; background: <?= $isActive ? 'rgba(255,255,255,0.28)' : 'var(--border-color, #cbd5e1)' ?>; color: <?= $isActive ? '#ffffff' : 'inherit' ?>;">
                            <?= esc((string) ($pmbPeriodCounts[$pKey] ?? 0)) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
                <?php $isAll = ($selectedPmbPeriod === 'all'); ?>
                <a href="<?= site_url('one-campus/schools?pmb_period=all' . (! empty($selectedSort) ? '&sort=' . urlencode($selectedSort) : '') . ($selectedOneCampusId ? '&campus_id=' . urlencode((string) $selectedOneCampusId) : '')) ?>"
                   class="<?= $isAll ? 'primary-button compact-button' : 'ghost-button compact-button' ?>"
                   style="<?= $isAll ? 'background: #0d9488; border-color: #0d9488; color: #ffffff; font-weight: 700;' : '' ?>">
                    Semua Periode PMB
                    <span style="display: inline-block; padding: 1px 7px; margin-left: 6px; border-radius: 999px; font-size: 0.76rem; background: <?= $isAll ? 'rgba(255,255,255,0.28)' : 'var(--border-color, #cbd5e1)' ?>; color: <?= $isAll ? '#ffffff' : 'inherit' ?>;">
                        <?= esc((string) ($pmbPeriodCounts['all'] ?? 0)) ?>
                    </span>
                </a>

                <?php if (session('user.role') === 'Admin Sistem' || session('user.role') === 'Super Admin'): ?>
                    <button type="button" class="ghost-button compact-button" onclick="document.getElementById('dialog-add-pmb-cluster').showModal()" style="border-color: #f59e0b; color: #b45309; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: rgba(245, 158, 11, 0.1);" title="Khusus Superadmin: Tambah Klasterisasi PMB Baru">
                        &#10010; Buat Klasterisasi Baru (Superadmin)
                    </button>
                <?php endif; ?>
            </div>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                <span class="status status-aktif" style="font-size: 0.8rem; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.35); padding: 4px 10px; border-radius: 999px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    ✓ Waktu PMB Berjalan (Default): <?= esc($defaultPmbPeriod ?? 'September 2026 - September 2027') ?>
                </span>
                <?php $isSuper = (session('user.role') === 'Admin Sistem' || session('user.role') === 'Super Admin'); ?>
                <?php if ($isSuper && $selectedPmbPeriod !== 'all' && $selectedPmbPeriod !== ($defaultPmbPeriod ?? 'September 2026 - September 2027')): ?>
                    <form action="<?= site_url('one-campus/pmb-clusters/set-default') ?>" method="post" style="display: inline-block;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="pmb_period" value="<?= esc($selectedPmbPeriod, 'attr') ?>">
                        <button type="submit" class="ghost-button compact-button" style="border-color: #f59e0b; color: #b45309; background: #fffbeb; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" title="Jadikan klaster '<?= esc($selectedPmbPeriod, 'attr') ?>' sebagai default / waktu PMB berjalan Sixty School">
                            ⭐ Jadikan Default (Waktu Berjalan)
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upgrade Proses Input: Tambah Data Sekolah Penerima BJS (Sesuai Screenshot User) -->
    <details id="drawer-add-school" class="panel management-drawer one-campus-drawer" style="margin-bottom: 20px; border-top: 3px solid #0d9488;" <?= $selectedProgram === 'school' ? 'open' : '' ?>>
        <summary style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="eyebrow" style="color: #0d9488;">Tambah Data Sekolah Penerima BJS</span>
                <strong style="font-size: 1.05rem;">Data Sekolah Penerima Beasiswa</strong>
            </div>
            <span class="ghost-button compact-button" style="border-color: #0d9488; color: #0d9488;">+ Form Tambah Sekolah BJS</span>
        </summary>

        <div style="padding: 16px 4px 6px 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main, #1e293b);">Data Sekolah Penerima Beasiswa</h3>
                <button type="button" class="compact-button" style="background: #10b981; color: #ffffff; border: none; border-radius: 6px; padding: 6px 14px; font-size: 0.84rem; font-weight: 600; cursor: pointer;" onclick="document.getElementById('drawer-add-school').removeAttribute('open')">
                    + Kembali
                </button>
            </div>

            <form class="one-campus-school-form" action="<?= site_url('one-campus/schools') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Baris 1: NPSN (dengan rekomendasi AI & auto-fill database nasional), Nama Sekolah, Kota, Jenis Skl, Status Skl -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <div style="position: relative;">
                        <label>
                            <span style="display: flex; align-items: center; justify-content: space-between; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">
                                <span>NPSN</span>
                                <span class="npsn-lookup-indicator" style="font-size: 0.72rem; font-weight: 700; color: #2563eb; display: none;">⚡ Mencari...</span>
                            </span>
                            <input class="control school-npsn-input" type="text" name="npsn" placeholder="Ketik 8 digit NPSN..." autocomplete="off">
                        </label>
                        <div class="school-autocomplete-dropdown" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; min-width: 340px; max-width: 440px; z-index: 1000; max-height: 280px; overflow-y: auto; background: var(--surface, #ffffff); border: 1px solid var(--border-color, #cbd5e1); border-radius: 12px; box-shadow: 0 12px 28px -6px rgba(15,23,42,0.25);"></div>
                        <span class="npsn-autofill-hint" style="display: block; font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                            💡 Rekomendasi 42.856 sekolah se-Indonesia
                        </span>
                    </div>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Nama Sekolah <span style="color: #ef4444;">*</span></span>
                        <input class="control" type="text" name="school_name" placeholder="Nama Sekolah Lengkap" required>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Kota</span>
                        <input class="control" type="text" name="city" placeholder="Kota / Kabupaten">
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Jenis Skl</span>
                        <select class="control" name="school_type">
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                            <option value="MA">MA</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Status Skl</span>
                        <select class="control" name="school_status">
                            <option value="Negeri">Negeri</option>
                            <option value="Swasta">Swasta</option>
                        </select>
                    </label>
                </div>

                <!-- Baris 2 (3 Kolom seperti di screenshot): Nama Kepala Sekolah, Telp Sekolah, Email Sekolah -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Nama Kepala Sekolah</span>
                        <input class="control" type="text" name="headmaster_name" placeholder="Nama Kepala Sekolah">
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Telp Sekolah</span>
                        <input class="control" type="text" name="school_phone" placeholder="No. Telp Resmi Sekolah">
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Email Sekolah</span>
                        <input class="control" type="email" name="school_email" placeholder="official@sekolah.sch.id">
                    </label>
                </div>

                <!-- Baris 3 (3 Kolom seperti di screenshot): Nama Kontak, Telp Kontak, Email Kontak -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Nama Kontak (PIC) <span style="color: #ef4444;">*</span></span>
                        <input class="control" type="text" name="contact_name" placeholder="Nama Guru / PIC BK" required>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Telp Kontak <span style="color: #ef4444;">*</span></span>
                        <input class="control" type="text" name="contact_phone" placeholder="No. HP / WA PIC" required>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Email Kontak</span>
                        <input class="control" type="email" name="contact_email" placeholder="pic@sekolah.com">
                    </label>
                </div>

                <!-- Baris 4: Status Skl, Tahun PMB (Default September 2026 - September 2027), Kampus Pelaksana, Jumlah PMB -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Status</span>
                        <select class="control" name="status">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Tahun PMB</span>
                        <select class="control" name="pmb_period">
                            <?php foreach ($availablePmbPeriods as $pKey => $pLabel): ?>
                                <option value="<?= esc($pKey) ?>" <?= ($selectedPmbPeriod === $pKey || ($selectedPmbPeriod === 'all' && $pKey === 'September 2026 - September 2027')) ? 'selected' : '' ?>>
                                    <?= esc($pKey) ?><?= $pKey === 'September 2026 - September 2027' ? ' (Default Berjalan)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Kampus Pelaksana</span>
                        <select class="control" name="campus_id">
                            <option value="">Pilih kampus</option>
                            <?php foreach ($campuses as $campus): ?>
                                <option value="<?= esc($campus['id']) ?>" <?= (string) ($selectedOneCampusId ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                                    <?= esc($campus['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Perolehan Siswa PMB</span>
                        <input class="control" type="number" name="pmb_count" min="0" value="0">
                    </label>
                </div>

                <!-- Baris 5: Media Upload MOU PDF & Integrasi Google Drive -->
                <div style="background: var(--bg-hover, #f8fafc); border: 1px dashed var(--border-color, #cbd5e1); border-radius: 8px; padding: 14px 16px; margin-bottom: 14px;">
                    <div style="font-weight: 700; font-size: 0.88rem; margin-bottom: 10px; color: var(--text-main, #1e293b); display: flex; align-items: center; gap: 8px;">
                        <span>&#128196;</span>
                        <span>Media Upload Dokumen MOU (PDF) &amp; Google Drive</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                        <label>
                            <span style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Upload File MOU (.pdf, maks. 15MB)</span>
                            <input class="control" type="file" name="mou_file" accept=".pdf,application/pdf">
                            <span style="font-size: 0.76rem; color: var(--text-muted, #64748b); display: block; margin-top: 3px;">
                                File PDF akan diarsip dan siap disinkronkan ke Google Drive Admin.
                            </span>
                        </label>
                        <label>
                            <span style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Link Google Drive Dokumen MOU (Opsional)</span>
                            <input class="control" type="url" name="mou_drive_link" placeholder="https://drive.google.com/file/d/.../view?usp=sharing">
                            <span style="font-size: 0.76rem; color: var(--text-muted, #64748b); display: block; margin-top: 3px;">
                                Target Folder Admin: <strong><?= esc($gdriveSettings['folder_name']) ?></strong> (<?= esc($gdriveSettings['admin_email']) ?>)
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Baris 6: Keterangan -->
                <label style="display: block; margin-bottom: 16px;">
                    <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Keterangan / Catatan Tambahan</span>
                    <textarea class="control" name="notes" rows="2" placeholder="Catatan mengenai kuota beasiswa, tanggal penandatanganan MOU, dll."></textarea>
                </label>

                <!-- Tombol Simpan (Sesuai Tombol Teal di Screenshot) -->
                <div>
                    <button class="primary-button" type="submit" style="background: #00b4d8; background: linear-gradient(135deg, #0d9488, #0ea5e9); border: none; color: #ffffff; padding: 10px 24px; font-weight: 700; border-radius: 6px; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                        Simpan Data Sekolah
                    </button>
                </div>
            </form>
        </div>
    </details>

    <!-- Bar Aksi Tambahan: Upload Excel PMB & Fasilitas Sortir Data -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 10px 14px;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <button type="button" class="primary-button compact-button" onclick="document.getElementById('dialog-upload-pmb').showModal()" style="background: #0284c7; border-color: #0284c7; color: #ffffff; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <span>&#128228;</span> Upload Data PMB (Excel)
            </button>
            <a href="<?= site_url('one-campus/pmb-template') ?>" class="ghost-button compact-button" style="color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 4px;" title="Unduh template Excel untuk input data mahasiswa baru">
                <span>&#128229;</span> Template Excel PMB
            </a>
        </div>

        <!-- Kontrol Sortir Ranking PMB: Banyak ke Sedikit -->
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 0.82rem;">
            <span style="color: var(--text-muted, #64748b); font-weight: 600;">Urutkan:</span>
            <?php
            $baseSortUrl = site_url('one-campus/schools?pmb_period=' . urlencode($selectedPmbPeriod) . ($selectedOneCampusId ? '&campus_id=' . urlencode((string) $selectedOneCampusId) : ''));
            ?>
            <a href="<?= $baseSortUrl ?>&sort=pmb_desc" class="<?= ($selectedSort === 'pmb_desc') ? 'primary-button compact-button' : 'ghost-button compact-button' ?>" style="<?= ($selectedSort === 'pmb_desc') ? 'background: #0d9488; border-color: #0d9488; color: #fff; font-weight: 700;' : '' ?>" title="Urutkan dari perolehan PMB terbanyak ke tersedikit">
                &#8645; PMB Terbanyak (Banyak ke Sedikit)
            </a>
            <a href="<?= $baseSortUrl ?>&sort=pmb_asc" class="<?= ($selectedSort === 'pmb_asc') ? 'primary-button compact-button' : 'ghost-button compact-button' ?>" style="<?= ($selectedSort === 'pmb_asc') ? 'background: #0d9488; border-color: #0d9488; color: #fff; font-weight: 700;' : '' ?>" title="Urutkan dari perolehan PMB terkecil">
                &#8645; PMB Tersedikit
            </a>
            <a href="<?= $baseSortUrl ?>&sort=name_asc" class="<?= ($selectedSort === 'name_asc') ? 'primary-button compact-button' : 'ghost-button compact-button' ?>" style="<?= ($selectedSort === 'name_asc') ? 'background: #0d9488; border-color: #0d9488; color: #fff; font-weight: 700;' : '' ?>">
                Nama Sekolah (A-Z)
            </a>
            <?php if (! empty($selectedSort)): ?>
                <a href="<?= $baseSortUrl ?>" class="ghost-button compact-button" style="color: var(--text-muted, #64748b);" title="Reset Pengurutan">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bar Pencarian & Ringkasan Tabel -->
    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari NPSN, sekolah, kota, jenis, kepala sekolah, kontak, atau MOU..." data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>

    <!-- Tabel Rekap Sixty School -->
    <div class="table-wrap">
        <table class="data-table" data-paginated-table data-page-size="10">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kampus</th>
                    <th>NPSN</th>
                    <th>Nama Sekolah &amp; Kota</th>
                    <th>Kontak Sekolah &amp; PIC</th>
                    <th>Tahun PMB</th>
                    <th>Dokumen MOU</th>
                    <th>Status</th>
                    <th>Status BJS</th>
                    <th>
                        <a href="<?= $baseSortUrl ?>&sort=<?= $selectedSort === 'pmb_desc' ? 'pmb_asc' : 'pmb_desc' ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Klik untuk mengurutkan perolehan PMB">
                            Perolehan PMB
                            <span style="color: #0d9488; font-weight: 800;"><?= $selectedSort === 'pmb_desc' ? '&#9660;' : ($selectedSort === 'pmb_asc' ? '&#9650;' : '&#8645;') ?></span>
                        </a>
                    </th>
                    <th>Aksi CRUD</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schoolReports as $index => $row): ?>
                    <?php
                    $schoolFormId = 'one-campus-school-update-' . $row['id'];
                    $schoolSearchText = implode(' ', [
                        $row['campus_name'] ?? '',
                        $row['npsn'] ?? '',
                        $row['school_name'] ?? '',
                        $row['city'] ?? '',
                        $row['school_type'] ?? '',
                        $row['school_status'] ?? '',
                        $row['headmaster_name'] ?? '',
                        $row['school_phone'] ?? '',
                        $row['school_email'] ?? '',
                        $row['contact_name'] ?? ($row['teacher_name'] ?? ''),
                        $row['contact_phone'] ?? ($row['phone'] ?? ''),
                        $row['contact_email'] ?? '',
                        $row['pmb_period'] ?? '',
                        $row['status'] ?? '',
                        $row['bjs_status'] ?? '',
                        (string) ($row['pmb_count'] ?? ''),
                        $row['notes'] ?? '',
                    ]);
                    $hasMouFile = ! empty($row['mou_file']);
                    $hasDriveLink = ! empty($row['mou_drive_link']);
                    $schoolDataForModal = [
                        'id' => (int) $row['id'],
                        'school_name' => (string) ($row['school_name'] ?? ''),
                        'npsn' => (string) ($row['npsn'] ?? ''),
                        'city' => (string) ($row['city'] ?? ''),
                        'school_type' => (string) ($row['school_type'] ?? 'SMA'),
                        'school_status' => (string) ($row['school_status'] ?? 'Negeri'),
                        'headmaster_name' => (string) ($row['headmaster_name'] ?? ''),
                        'school_phone' => (string) ($row['school_phone'] ?? ''),
                        'school_email' => (string) ($row['school_email'] ?? ''),
                        'contact_name' => (string) ($row['contact_name'] ?? ($row['teacher_name'] ?? '')),
                        'position' => (string) ($row['position'] ?? 'Kontak Sekolah'),
                        'contact_phone' => (string) ($row['contact_phone'] ?? ($row['phone'] ?? '')),
                        'contact_email' => (string) ($row['contact_email'] ?? ''),
                        'campus_name' => (string) ($row['campus_name'] ?? 'Semua Kampus'),
                        'campus_code' => (string) ($row['campus_kode_kampus'] ?? ($row['campus_code'] ?? '')),
                        'campus_ubsi' => (string) ($row['campus_kode_ubsi'] ?? ($row['kode_ubsi'] ?? '')),
                        'pmb_period' => (string) ($row['pmb_period'] ?? 'September 2026 - September 2027'),
                        'status' => (string) ($row['status'] ?? 'Aktif'),
                        'bjs_status' => (string) ($row['bjs_status'] ?? 'Belum Aktif'),
                        'bjs_verifier_name' => (string) ($row['bjs_verifier_name'] ?? ''),
                        'bjs_verified_at' => (string) ($row['bjs_verified_at'] ?? ''),
                        'pmb_count' => (int) ($row['pmb_count'] ?? 0),
                        'mou_file' => (string) ($row['mou_file'] ?? ''),
                        'mou_drive_link' => (string) ($row['mou_drive_link'] ?? ''),
                        'mou_url' => ! empty($row['mou_file']) ? site_url('one-campus/schools/' . $row['id'] . '/mou') : '',
                        'edit_url' => site_url('one-campus/schools/' . $row['id'] . '/edit'),
                        'notes' => (string) ($row['notes'] ?? ''),
                        'creator_name' => (string) ($row['creator_name'] ?? 'Admin'),
                        'created_at' => ! empty($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) . ' WIB' : '-',
                    ];
                    ?>
                    <tr data-page-row data-search-text="<?= esc($schoolSearchText, 'attr') ?>">
                        <td><?= esc((string) ($index + 1)) ?></td>
                        <td>
                            <div style="font-weight: 600;">
                                <?php if (! empty($row['campus_kode_kampus'])): ?>
                                    <span class="status status-aktif" style="font-size: 0.7rem; padding: 1px 6px; margin-right: 4px; font-weight: 700;"><?= esc($row['campus_kode_kampus']) ?></span>
                                <?php endif; ?>
                                <?= esc($row['campus_name'] ?? '-') ?>
                            </div>
                        </td>
                        <td><code><?= esc($row['npsn'] ?: '-') ?></code></td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main, #1e293b);"><?= esc($row['school_name']) ?></div>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 3px; font-size: 0.74rem;">
                                <span style="background: var(--badge-bg, #e2e8f0); color: var(--text-main, #334155); padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                                    <?= esc($row['school_type'] ?? 'SMA') ?>
                                </span>
                                <span style="background: <?= ($row['school_status'] ?? 'Negeri') === 'Negeri' ? '#e0f2fe' : '#fef3c7' ?>; color: <?= ($row['school_status'] ?? 'Negeri') === 'Negeri' ? '#0369a1' : '#b45309' ?>; padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                                    <?= esc($row['school_status'] ?? 'Negeri') ?>
                                </span>
                                <?php if (! empty($row['city'])): ?>
                                    <span style="color: var(--text-muted, #64748b);"><?= esc($row['city']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                <strong>PIC:</strong> <?= esc($row['contact_name'] ?? ($row['teacher_name'] ?? '-')) ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted, #64748b);">
                                &#128222; <?= esc($row['contact_phone'] ?? ($row['phone'] ?? '-')) ?>
                            </div>
                            <?php if (! empty($row['headmaster_name'])): ?>
                                <div style="font-size: 0.74rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                                    Kepsek: <?= esc($row['headmaster_name']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-aktif" style="font-size: 0.78rem; white-space: nowrap;">
                                <?= esc($row['pmb_period'] ?? 'September 2026 - September 2027') ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <?php if ($hasMouFile): ?>
                                    <a class="ghost-button compact-button" href="<?= site_url('one-campus/schools/' . $row['id'] . '/mou') ?>" target="_blank" style="color: #ef4444; border-color: #fca5a5; display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem;" title="Buka Dokumen PDF MOU">
                                        &#128196; Lihat PDF MOU
                                    </a>
                                <?php endif; ?>
                                <?php if ($hasDriveLink): ?>
                                    <a class="ghost-button compact-button" href="<?= esc($row['mou_drive_link']) ?>" target="_blank" rel="noopener noreferrer" style="color: #0284c7; border-color: #7dd3fc; display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem;" title="Buka Google Drive Admin">
                                        &#9729; Buka di Drive &nearr;
                                    </a>
                                <?php elseif ($hasMouFile && ! empty($gdriveSettings['is_configured'])): ?>
                                    <form action="<?= site_url('one-campus/schools/' . $row['id'] . '/sync-drive') ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="ghost-button compact-button" style="color: #0d9488; border-color: #5eead4; font-size: 0.72rem; padding: 2px 6px;" title="Unggah file MOU ini langsung ke Google Drive Admin">
                                            &#9729; Kirim ke Drive
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (! $hasMouFile && ! $hasDriveLink): ?>
                                    <span class="status status-menunggu" style="font-size: 0.74rem;">Belum ada MOU</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status <?= ($row['status'] ?? 'Aktif') === 'Aktif' ? 'status-selesai' : 'status-menunggu' ?>" style="font-size: 0.78rem;">
                                <?= esc($row['status'] ?? 'Aktif') ?>
                            </span>
                        </td>
                        <td>
                            <?php $isBjsActive = ($row['bjs_status'] ?? '') === 'Aktif'; ?>
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                <?php if ($isBjsActive): ?>
                                    <span class="status status-selesai" style="font-size: 0.74rem; font-weight: 700; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;" title="Diverifikasi oleh <?= esc($row['bjs_verifier_name'] ?? 'Admin') ?> pada <?= esc($row['bjs_verified_at'] ?? '-') ?>">
                                        ✓ BJS Aktif
                                    </span>
                                    <?php if (! empty($row['bjs_verifier_name'])): ?>
                                        <span style="font-size: 0.68rem; color: var(--text-muted, #64748b);" title="<?= esc($row['bjs_verified_at'] ?? '') ?>">Oleh: <?= esc($row['bjs_verifier_name']) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status status-menunggu" style="font-size: 0.74rem; font-weight: 700; background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                                        ⏳ Belum Aktif
                                    </span>
                                <?php endif; ?>

                                <!-- Tombol Verifikasi / Batalkan untuk Admin -->
                                <form action="<?= site_url('one-campus/schools/' . $row['id'] . '/verify-bjs') ?>" method="post" style="display: inline-block; margin-top: 2px;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="<?= $isBjsActive ? 'unverify' : 'verify' ?>">
                                    <?php if ($isBjsActive): ?>
                                        <button type="submit" class="ghost-button compact-button" style="font-size: 0.68rem; padding: 1px 6px; color: #94a3b8; border-color: #cbd5e1;" onclick="return confirm('Batalkan status verifikasi BJS untuk <?= esc($row['school_name'], 'attr') ?>?');" title="Batalkan verifikasi BJS">
                                            Batalkan
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="primary-button compact-button" style="font-size: 0.7rem; padding: 2px 8px; background: #059669; border-color: #059669; color: #fff; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;" onclick="return confirm('Verifikasi dan aktifkan program Beasiswa Juara Sekolah (BJS) untuk <?= esc($row['school_name'], 'attr') ?>?');" title="Verifikasi dan aktifkan BJS">
                                            <span>&#10003;</span> Verifikasi BJS
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                        <td>
                            <?php $pmbVal = (int) ($row['pmb_count'] ?? 0); ?>
                            <?php if ($pmbVal > 0): ?>
                                <button type="button" class="ghost-button compact-button" onclick="showSchoolStudents(<?= (int) $row['id'] ?>, '<?= esc($row['school_name'], 'js') ?>')" style="color: #0284c7; border-color: #7dd3fc; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; cursor: pointer;" title="Klik untuk melihat rincian nama mahasiswa baru dari sekolah ini">
                                    <?php if ($selectedSort === 'pmb_desc'): ?>
                                        <?= $index === 0 ? '🥇 ' : ($index === 1 ? '🥈 ' : ($index === 2 ? '🥉 ' : '#' . ($index + 1) . ' ')) ?>
                                    <?php endif; ?>
                                    <strong><?= $pmbVal ?> Mhs</strong> &#128065;
                                </button>
                            <?php else: ?>
                                <span style="color: var(--text-muted, #94a3b8); font-size: 0.82rem;">0 Mhs</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                                <button type="button" class="ghost-button compact-button btn-view-school"
                                        data-school='<?= esc(json_encode($schoolDataForModal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), 'attr') ?>'
                                        onclick="openSchoolDetailModal(this)"
                                        style="display: inline-flex; align-items: center; gap: 4px; font-weight: 600; color: #0284c7; border-color: #7dd3fc; background: rgba(2,132,199,0.06); white-space: nowrap;"
                                        title="Lihat Data Lengkap &amp; Salin Tiap Field">
                                    <iconify-icon icon="solar:eye-outline" class="text-sm"></iconify-icon>
                                    Lihat Data
                                </button>
                                <a class="ghost-button compact-button" href="<?= site_url('one-campus/schools/' . $row['id'] . '/edit') ?>" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 600; white-space: nowrap;" title="Edit Data Sekolah">
                                    &#9998; Kelola
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($schoolReports === []): ?>
                    <tr data-empty-row>
                        <td colspan="11"><span class="muted-note">Tidak ada rekap One Campus Sixty School untuk kampus atau klaster PMB ini.</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination-bar table-pagination-bar" aria-label="Navigasi rekap One Campus Sixty School" data-table-pagination></nav>
</section>
<?php endif; ?>

<?php if ($showBanner): ?>
<section class="panel" id="section-banner">
    <div class="panel-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
        <div>
            <span class="eyebrow">Sub Menu 3 &bull; Target Minimal 5 Titik Spanduk / Kampus</span>
            <h2>Laporan Rekap &amp; CRUD: One Campus Five Spanduk</h2>
        </div>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <a class="primary-button compact-button" href="<?= site_url('one-campus/banners/export') . $campusQuery ?>">
                Download Hasil XLSX (Five Spanduk)
            </a>
        </div>
    </div>

    <details class="panel management-drawer one-campus-drawer" style="margin-bottom: 18px;" <?= $selectedProgram === 'banner' ? 'open' : '' ?>>
        <summary>
            <div>
                <span class="eyebrow">Tambah Data Baru</span>
                <strong>Input Laporan One Campus Five Spanduk</strong>
            </div>
            <span class="ghost-button compact-button">Form Input Spanduk</span>
        </summary>
        <form class="drawer-form one-campus-form" action="<?= site_url('one-campus/banners') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Kampus
                <select class="control" name="campus_id">
                    <option value="">Pilih kampus</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?= esc($campus['id']) ?>" <?= (string) ($selectedOneCampusId ?? '') === (string) $campus['id'] ? 'selected' : '' ?>><?= esc($campus['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Lokasi Pemasangan Spanduk
                <input class="control" type="text" name="location" required>
            </label>
            <label>
                Waktu Pemasangan Spanduk
                <input class="control" type="datetime-local" name="installed_at" required>
            </label>
            <label>
                Tema Spanduk
                <input class="control" type="text" name="theme" required>
            </label>
            <label class="span-2">
                Link Google Drive Foto Spanduk
                <input class="control" type="url" name="drive_photo_link" placeholder="https://drive.google.com/..." required>
            </label>
            <button class="primary-button" type="submit">Simpan Data Spanduk</button>
        </form>
    </details>

    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari spanduk, lokasi, tema, kampus, atau link foto" data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>
    <div class="table-wrap">
        <table class="data-table" data-paginated-table data-page-size="10">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kampus</th>
                    <th>Lokasi Pemasangan Spanduk</th>
                    <th>Waktu Pemasangan Spanduk</th>
                    <th>Tema Spanduk</th>
                    <th>Foto Spanduk</th>
                    <th>Aksi CRUD</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bannerReports as $index => $row): ?>
                    <?php
                    $bannerFormId = 'one-campus-banner-update-' . $row['id'];
                    $bannerSearchText = implode(' ', [
                        $row['campus_name'] ?? '',
                        $row['location'] ?? '',
                        ! empty($row['installed_at']) ? date('d M Y H:i', strtotime($row['installed_at'])) : '',
                        $row['theme'] ?? '',
                        $row['drive_photo_link'] ?? '',
                    ]);
                    ?>
                    <tr data-page-row data-search-text="<?= esc($bannerSearchText, 'attr') ?>">
                        <td><?= esc((string) ($index + 1)) ?></td>
                        <td><?= esc($row['campus_name'] ?? '-') ?></td>
                        <td><?= esc($row['location']) ?></td>
                        <td><?= esc(date('d M Y H:i', strtotime($row['installed_at']))) ?></td>
                        <td><?= esc($row['theme']) ?></td>
                        <td><a class="text-link" href="<?= esc($row['drive_photo_link']) ?>" target="_blank" rel="noopener">Google Drive</a></td>
                        <td>
                            <details class="row-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <div class="row-edit-panel">
                                    <form id="<?= esc($bannerFormId) ?>" action="<?= site_url('one-campus/banners/' . $row['id'] . '/update') ?>" method="post" class="form-grid compact-edit-grid">
                                        <?= csrf_field() ?>
                                        <label>
                                            Kampus
                                            <select class="control table-control" name="campus_id">
                                                <option value="">Pilih kampus</option>
                                                <?php foreach ($campuses as $campus): ?>
                                                    <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($row['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                                                        <?= esc($campus['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Lokasi Pemasangan
                                            <input class="control table-control" type="text" name="location" value="<?= esc($row['location'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Waktu Pemasangan
                                            <input class="control table-control" type="datetime-local" name="installed_at" value="<?= esc($dateTimeLocalValue($row['installed_at']), 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Tema Spanduk
                                            <input class="control table-control" type="text" name="theme" value="<?= esc($row['theme'], 'attr') ?>" required>
                                        </label>
                                        <label class="span-2">
                                            Link Google Drive Foto
                                            <input class="control table-control" type="url" name="drive_photo_link" value="<?= esc($row['drive_photo_link'], 'attr') ?>" required>
                                        </label>
                                    </form>
                                    <div class="button-row">
                                        <button class="primary-button compact-button" form="<?= esc($bannerFormId) ?>" type="submit">Simpan Spanduk</button>
                                        <form action="<?= site_url('one-campus/banners/' . $row['id'] . '/delete') ?>" method="post" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button class="danger-button compact-button" type="submit">Hapus Spanduk</button>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($bannerReports === []): ?>
                    <tr data-empty-row>
                        <td colspan="7"><span class="muted-note">Tidak ada rekap One Campus Five Spanduk untuk kampus ini.</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination-bar table-pagination-bar" aria-label="Navigasi rekap One Campus Five Spanduk" data-table-pagination></nav>
</section>
<?php endif; ?>

<!-- ==================== MODAL 1: TAMBAH KLASTERISASI PMB BARU (SUPERADMIN) ==================== -->
<dialog id="dialog-add-pmb-cluster" class="panel" style="max-width: 480px; width: 92%; border-radius: 12px; border: 1px solid var(--border-color, #cbd5e1); box-shadow: 0 10px 30px rgba(0,0,0,0.3); padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #b45309; display: flex; align-items: center; gap: 6px;">
            <span>⚡</span> Buat Klasterisasi PMB Baru
        </h3>
        <button type="button" class="ghost-button compact-button" onclick="document.getElementById('dialog-add-pmb-cluster').close()">✕</button>
    </div>
    <form action="<?= site_url('one-campus/pmb-clusters') ?>" method="post">
        <?= csrf_field() ?>
        <p style="font-size: 0.82rem; color: var(--text-muted, #64748b); margin-top: 0; margin-bottom: 14px;">
            Khusus Superadmin: Tambahkan klaster periode PMB mendatang atau tahun akademik terbaru untuk One Campus Sixty School.
        </p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
            <label>
                <span style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Tahun Mulai</span>
                <input class="control" type="number" name="start_year" min="2020" max="2040" value="<?= date('Y') + 1 ?>" required>
            </label>
            <label>
                <span style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Tahun Selesai</span>
                <input class="control" type="number" name="end_year" min="2021" max="2041" value="<?= date('Y') + 2 ?>" required>
            </label>
        </div>
        <label style="display: block; margin-bottom: 14px;">
            <span style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Nama Klaster (Otomatis / Custom)</span>
            <input class="control" type="text" name="pmb_period_name" placeholder="Contoh: September 2027 - September 2028">
            <span style="font-size: 0.74rem; color: var(--text-muted, #64748b); margin-top: 3px; display: block;">Kosongkan untuk otomatis menggunakan format bulan September.</span>
        </label>
        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; cursor: pointer;">
            <input type="checkbox" name="set_as_default" value="1" checked>
            <span style="font-size: 0.82rem; font-weight: 600; color: var(--ink, #1e293b);">Langsung jadikan sebagai Klaster Default (Waktu PMB Berjalan)</span>
        </label>
        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="ghost-button" onclick="document.getElementById('dialog-add-pmb-cluster').close()">Batal</button>
            <button type="submit" class="primary-button" style="background: #f59e0b; border-color: #f59e0b; color: #ffffff; font-weight: 700;">Simpan Klaster Baru</button>
        </div>
    </form>

    <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--line, #e2e8f0);">
        <span style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--ink, #1e293b); margin-bottom: 6px;">
            ⭐ Tetapkan Klaster Default / Waktu PMB Berjalan:
        </span>
        <form action="<?= site_url('one-campus/pmb-clusters/set-default') ?>" method="post" style="display: flex; gap: 8px; align-items: center;">
            <?= csrf_field() ?>
            <select name="pmb_period" class="control" style="flex: 1; padding: 6px 10px; font-size: 0.85rem;">
                <?php foreach ($availablePmbPeriods as $pKey => $pLabel): ?>
                    <option value="<?= esc($pKey, 'attr') ?>" <?= (($defaultPmbPeriod ?? 'September 2026 - September 2027') === $pKey) ? 'selected' : '' ?>>
                        <?= esc($pLabel) ?><?= (($defaultPmbPeriod ?? 'September 2026 - September 2027') === $pKey) ? ' (Default Berjalan Saat Ini)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="primary-button compact-button" style="background: #0d9488; border-color: #0d9488; color: #ffffff; font-weight: 700;">
                Setel Waktu Berjalan
            </button>
        </form>
    </div>
</dialog>

<!-- ==================== MODAL 2: UPLOAD DATA PMB EXCEL ==================== -->
<dialog id="dialog-upload-pmb" class="panel" style="max-width: 520px; width: 92%; border-radius: 12px; border: 1px solid var(--border-color, #cbd5e1); box-shadow: 0 10px 30px rgba(0,0,0,0.3); padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0284c7; display: flex; align-items: center; gap: 6px;">
            <span>📤</span> Upload Data PMB Sekolah (Excel)
        </h3>
        <button type="button" class="ghost-button compact-button" onclick="document.getElementById('dialog-upload-pmb').close()">✕</button>
    </div>
    <form action="<?= site_url('one-campus/upload-pmb') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <p style="font-size: 0.82rem; color: var(--text-muted, #64748b); margin-top: 0; margin-bottom: 14px;">
            Unggah file Excel daftar mahasiswa baru untuk secara otomatis menghitung dan memperbarui <strong>Jumlah PMB</strong> per sekolah serta rincian mahasiswanya.
        </p>
        <label style="display: block; margin-bottom: 12px;">
            <span style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Pilih Klaster PMB Target <span style="color: #ef4444;">*</span></span>
            <select class="control" name="pmb_period" required>
                <?php foreach ($availablePmbPeriods as $pKey => $pLabel): ?>
                    <option value="<?= esc($pKey) ?>" <?= ($selectedPmbPeriod === $pKey || ($selectedPmbPeriod === 'all' && $pKey === 'September 2026 - September 2027')) ? 'selected' : '' ?>>
                        <?= esc($pLabel) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label style="display: block; margin-bottom: 12px;">
            <span style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Pilih File Excel (.xlsx / .csv) <span style="color: #ef4444;">*</span></span>
            <input class="control" type="file" name="pmb_file" accept=".xlsx,.csv,.xls" required>
        </label>
        <div style="background: var(--bg-hover, #f8fafc); border: 1px dashed var(--border-color, #cbd5e1); border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
            <div style="font-size: 0.78rem; color: var(--text-muted, #64748b); line-height: 1.45;">
                <div style="margin-bottom: 4px;">
                    💡 <strong>Terintegrasi Kode Kampus &amp; Kode UBSI:</strong> Template baru telah dilengkapi kolom <code>Kode Kampus</code> (misal: <em>D2, A1, E1, K1</em>) &amp; <code>Kode UBSI</code> (<em>01, 02, 04, dst.</em>), serta Sheet 2 berisi referensi 27 kampus resmi UBSI.
                </div>
                <div>
                    Belum punya template Excel?
                    <a href="<?= site_url('one-campus/pmb-template') ?>" style="color: #0d9488; font-weight: 700; text-decoration: underline; margin-left: 4px;">
                        Unduh Template Excel PMB di Sini
                    </a>
                </div>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="ghost-button" onclick="document.getElementById('dialog-upload-pmb').close()">Batal</button>
            <button type="submit" class="primary-button" style="background: #0284c7; border-color: #0284c7; color: #ffffff; font-weight: 700;">
                Unggah &amp; Rekap PMB
            </button>
        </div>
    </form>
</dialog>

<!-- ==================== MODAL 3: RINCIAN MAHASISWA BARU PER SEKOLAH ==================== -->
<dialog id="dialog-school-students" class="panel" style="max-width: 780px; width: 95%; border-radius: 12px; border: 1px solid var(--border-color, #cbd5e1); box-shadow: 0 10px 30px rgba(0,0,0,0.3); padding: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
        <div>
            <h3 id="modalStudentSchoolName" style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main, #1e293b);">
                Daftar Mahasiswa Baru
            </h3>
            <span id="modalStudentPeriodBadge" class="status status-aktif" style="font-size: 0.75rem; margin-top: 4px; display: inline-block;">
                Klaster PMB
            </span>
        </div>
        <button type="button" class="ghost-button compact-button" onclick="document.getElementById('dialog-school-students').close()" style="font-size: 1rem; padding: 4px 10px;">✕</button>
    </div>

    <div id="modalStudentLoading" style="text-align: center; padding: 24px 0; color: var(--text-muted, #64748b);">
        Memuat data mahasiswa baru...
    </div>

    <div id="modalStudentContent" style="display: none; max-height: 400px; overflow-y: auto;">
        <table class="data-table" style="width: 100%; font-size: 0.82rem;">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Nama Mahasiswa</th>
                    <th>No Pendaftaran</th>
                    <th>Kampus / Kode</th>
                    <th>Program Studi</th>
                    <th>No HP / WA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="modalStudentTableBody">
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px; padding-top: 10px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
        <span id="modalStudentTotal" style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted, #64748b);">Total: 0 Mahasiswa</span>
        <button type="button" class="ghost-button" onclick="document.getElementById('dialog-school-students').close()">Tutup</button>
    </div>
</dialog>

<!-- Dialog Modal Detail Data Sekolah & Fitur Salin Tiap Field -->
<dialog id="dialog-school-detail" class="school-detail-dialog">
    <div class="school-detail-header">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7;">
                    <iconify-icon icon="solar:buildings-3-bold" style="font-size: 1.25rem;"></iconify-icon>
                </span>
                <div>
                    <h3 id="schoolDetailName" style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main, #0f172a);">Detail Data Sekolah</h3>
                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                        <span id="schoolDetailNpsnBadge" style="font-family: monospace; font-size: 0.76rem; background: var(--badge-bg, #e2e8f0); color: var(--text-main, #334155); padding: 1px 7px; border-radius: 4px; font-weight: 700;">NPSN: -</span>
                        <span id="schoolDetailTypeBadge" style="font-size: 0.76rem; background: #e0f2fe; color: #0369a1; padding: 1px 7px; border-radius: 4px; font-weight: 600;">SMA (Negeri)</span>
                        <span id="schoolDetailBjsBadge" class="status status-selesai" style="font-size: 0.74rem;">✓ BJS Aktif</span>
                    </div>
                </div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" id="btnCopyAllSchoolData" onclick="copyAllSchoolData()" class="primary-button compact-button" style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.78rem; background: #0284c7; border-color: #0284c7; color: #ffffff; font-weight: 700; padding: 6px 12px;" title="Salin seluruh informasi lengkap sekolah ini dalam satu teks rapi">
                <iconify-icon icon="solar:copy-outline" style="font-size: 1rem;"></iconify-icon>
                <span>Salin Semua Data</span>
            </button>
            <button type="button" class="ghost-button compact-button" onclick="document.getElementById('dialog-school-detail').close()" style="font-size: 1.1rem; padding: 4px 10px; border-radius: 8px;" title="Tutup Dialog">✕</button>
        </div>
    </div>

    <div class="school-detail-body">
        <!-- Section 1: Identitas Satuan Pendidikan -->
        <div class="school-detail-section">
            <div class="school-detail-section-title">
                <iconify-icon icon="solar:diploma-verified-outline" style="font-size: 1.1rem;"></iconify-icon>
                Data Identitas Sekolah
            </div>
            <div class="school-detail-grid">
                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Nama Sekolah</span>
                        <span class="school-item-val" id="dt_school_name">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_school_name" onclick="copyFieldById('dt_school_name', 'Nama Sekolah', this)" title="Salin Nama Sekolah">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">NPSN</span>
                        <span class="school-item-val" id="dt_npsn" style="font-family: monospace;">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_npsn" onclick="copyFieldById('dt_npsn', 'NPSN', this)" title="Salin NPSN">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Kota / Kabupaten</span>
                        <span class="school-item-val" id="dt_city">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_city" onclick="copyFieldById('dt_city', 'Kota / Kabupaten', this)" title="Salin Kota/Kabupaten">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Jenis Satuan (SKL)</span>
                        <span class="school-item-val" id="dt_school_type">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_school_type" onclick="copyFieldById('dt_school_type', 'Jenis Satuan Pendidikan', this)" title="Salin Jenis Sekolah">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Status Sekolah</span>
                        <span class="school-item-val" id="dt_school_status">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_school_status" onclick="copyFieldById('dt_school_status', 'Status Sekolah', this)" title="Salin Status Sekolah">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Nama Kepala Sekolah</span>
                        <span class="school-item-val" id="dt_headmaster_name">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_headmaster_name" onclick="copyFieldById('dt_headmaster_name', 'Nama Kepala Sekolah', this)" title="Salin Nama Kepala Sekolah">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">No. Telepon Sekolah</span>
                        <span class="school-item-val" id="dt_school_phone">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_school_phone" onclick="copyFieldById('dt_school_phone', 'No. Telepon Sekolah', this)" title="Salin No. Telepon Sekolah">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Email Resmi Sekolah</span>
                        <span class="school-item-val" id="dt_school_email">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_school_email" onclick="copyFieldById('dt_school_email', 'Email Resmi Sekolah', this)" title="Salin Email Resmi Sekolah">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 2: Kontak PIC & Guru Sekolah -->
        <div class="school-detail-section">
            <div class="school-detail-section-title">
                <iconify-icon icon="solar:user-hand-up-outline" style="font-size: 1.1rem;"></iconify-icon>
                Kontak PIC &amp; Guru Sekolah
            </div>
            <div class="school-detail-grid">
                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Nama Guru / Kontak PIC</span>
                        <span class="school-item-val" id="dt_contact_name">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_contact_name" onclick="copyFieldById('dt_contact_name', 'Nama Kontak PIC', this)" title="Salin Nama PIC">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Jabatan / Posisi</span>
                        <span class="school-item-val" id="dt_position">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_position" onclick="copyFieldById('dt_position', 'Jabatan / Posisi', this)" title="Salin Jabatan">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">No. HP / WhatsApp PIC</span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="school-item-val" id="dt_contact_phone">-</span>
                            <a id="schoolDetailWaBtn" href="#" target="_blank" rel="noopener noreferrer" class="ghost-button compact-button" style="display: none; padding: 1px 6px; font-size: 0.72rem; color: #059669; border-color: #a7f3d0;" title="Chat WhatsApp">
                                💬 WA
                            </a>
                        </div>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_contact_phone" onclick="copyFieldById('dt_contact_phone', 'No. HP / WhatsApp PIC', this)" title="Salin No. HP PIC">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Email Kontak PIC</span>
                        <span class="school-item-val" id="dt_contact_email">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_contact_email" onclick="copyFieldById('dt_contact_email', 'Email Kontak PIC', this)" title="Salin Email PIC">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 3: Program PMB & Beasiswa BJS -->
        <div class="school-detail-section">
            <div class="school-detail-section-title">
                <iconify-icon icon="solar:case-round-minimalistic-outline" style="font-size: 1.1rem;"></iconify-icon>
                Detail Kampus, Program PMB &amp; Beasiswa (BJS)
            </div>
            <div class="school-detail-grid">
                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Kampus Pengusul</span>
                        <span class="school-item-val" id="dt_campus">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_campus" onclick="copyFieldById('dt_campus', 'Kampus Pengusul', this)" title="Salin Kampus">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Tahun / Periode PMB</span>
                        <span class="school-item-val" id="dt_pmb_period">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_pmb_period" onclick="copyFieldById('dt_pmb_period', 'Periode PMB', this)" title="Salin Periode PMB">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Status Kerjasama</span>
                        <span class="school-item-val" id="dt_status">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_status" onclick="copyFieldById('dt_status', 'Status Kerjasama', this)" title="Salin Status Kerjasama">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Status Beasiswa (BJS)</span>
                        <span class="school-item-val" id="dt_bjs_status">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_bjs_status" onclick="copyFieldById('dt_bjs_status', 'Status Beasiswa BJS', this)" title="Salin Status BJS">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item">
                    <div class="school-item-content">
                        <span class="school-item-label">Perolehan Mahasiswa PMB</span>
                        <span class="school-item-val" id="dt_pmb_count">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_pmb_count" onclick="copyFieldById('dt_pmb_count', 'Perolehan Mahasiswa PMB', this)" title="Salin Perolehan PMB">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item full-width">
                    <div class="school-item-content">
                        <span class="school-item-label">Dokumen MoU (Berkas / Google Drive)</span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="school-item-val" id="dt_mou_text" style="font-size: 0.82rem; word-break: break-all;">-</span>
                            <a id="schoolDetailPdfLink" href="#" target="_blank" class="ghost-button compact-button" style="display: none; color: #ef4444; border-color: #fca5a5; font-size: 0.74rem;">
                                📄 Buka PDF Lokal
                            </a>
                            <a id="schoolDetailDriveLink" href="#" target="_blank" rel="noopener noreferrer" class="ghost-button compact-button" style="display: none; color: #0284c7; border-color: #7dd3fc; font-size: 0.74rem;">
                                ☁ Buka di Google Drive &nearr;
                            </a>
                        </div>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_mou" onclick="copyFieldById('dt_mou_text', 'Dokumen MoU', this)" title="Salin Tautan Dokumen MoU">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>

                <div class="school-detail-item full-width">
                    <div class="school-item-content">
                        <span class="school-item-label">Catatan Tambahan</span>
                        <span class="school-item-val" id="dt_notes" style="font-size: 0.84rem; white-space: pre-wrap;">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_notes" onclick="copyFieldById('dt_notes', 'Catatan Tambahan', this)" title="Salin Catatan">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 4: Metadata Sistem -->
        <div class="school-detail-section" style="padding: 12px 16px;">
            <div class="school-detail-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="school-detail-item" style="padding: 8px 10px;">
                    <div class="school-item-content">
                        <span class="school-item-label">Diinput Oleh</span>
                        <span class="school-item-val" id="dt_creator_name" style="font-size: 0.82rem;">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_creator" onclick="copyFieldById('dt_creator_name', 'Penginput', this)" title="Salin Nama Penginput">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                    </button>
                </div>

                <div class="school-detail-item" style="padding: 8px 10px;">
                    <div class="school-item-content">
                        <span class="school-item-label">Waktu Pendaftaran</span>
                        <span class="school-item-val" id="dt_created_at" style="font-size: 0.82rem;">-</span>
                    </div>
                    <button type="button" class="school-copy-btn" id="cp_created_at" onclick="copyFieldById('dt_created_at', 'Waktu Pendaftaran', this)" title="Salin Waktu">
                        <iconify-icon icon="solar:copy-outline"></iconify-icon>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="school-detail-footer">
        <div>
            <span id="schoolCopyToast" style="display: none; align-items: center; gap: 5px; color: #10b981; font-weight: 700; font-size: 0.84rem;">
                <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                <span id="schoolCopyToastMsg">Tersalin ke clipboard!</span>
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a id="schoolDetailEditBtn" href="#" class="ghost-button" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                ✎ Kelola / Edit Data Sekolah
            </a>
            <button type="button" class="ghost-button" onclick="document.getElementById('dialog-school-detail').close()">Tutup</button>
        </div>
    </div>
</dialog>

<script>
function openSchoolDetailModal(btn) {
    const raw = btn.getAttribute('data-school');
    if (!raw) return;
    let s;
    try {
        s = JSON.parse(raw);
    } catch (e) {
        console.error('Failed to parse school data', e);
        return;
    }

    const dialog = document.getElementById('dialog-school-detail');
    if (!dialog) return;

    // Header info
    document.getElementById('schoolDetailName').textContent = s.school_name || 'Detail Sekolah';
    document.getElementById('schoolDetailNpsnBadge').textContent = s.npsn ? ('NPSN: ' + s.npsn) : 'Tanpa NPSN';
    document.getElementById('schoolDetailTypeBadge').textContent = (s.school_type || 'SMA') + ' (' + (s.school_status || 'Negeri') + ')';

    const bjsBadge = document.getElementById('schoolDetailBjsBadge');
    if (s.bjs_status === 'Aktif') {
        bjsBadge.textContent = '✓ BJS Aktif';
        bjsBadge.className = 'status status-selesai';
    } else {
        bjsBadge.textContent = '⏳ BJS Belum Aktif';
        bjsBadge.className = 'status status-menunggu';
    }

    function setVal(id, val, explicitCopy) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = (val !== null && val !== undefined && String(val).trim() !== '') ? val : '-';
        if (explicitCopy !== undefined) {
            el.setAttribute('data-raw-copy', explicitCopy);
        } else {
            el.removeAttribute('data-raw-copy');
        }
    }

    // Populate all fields
    setVal('dt_school_name', s.school_name);
    setVal('dt_npsn', s.npsn);
    setVal('dt_city', s.city);
    setVal('dt_school_type', s.school_type);
    setVal('dt_school_status', s.school_status);
    setVal('dt_headmaster_name', s.headmaster_name);
    setVal('dt_school_phone', s.school_phone);
    setVal('dt_school_email', s.school_email);

    setVal('dt_contact_name', s.contact_name);
    setVal('dt_position', s.position);
    setVal('dt_contact_phone', s.contact_phone);
    setVal('dt_contact_email', s.contact_email);

    const campusDisplay = (s.campus_code ? `[${s.campus_code}] ` : '') + (s.campus_name || '-') + (s.campus_ubsi ? ` (${s.campus_ubsi})` : '');
    setVal('dt_campus', campusDisplay, s.campus_name);
    setVal('dt_pmb_period', s.pmb_period);
    setVal('dt_status', s.status);
    setVal('dt_bjs_status', s.bjs_status + (s.bjs_verifier_name ? ` (diverifikasi oleh ${s.bjs_verifier_name} pada ${s.bjs_verified_at || '-'})` : ''), s.bjs_status);
    setVal('dt_pmb_count', (s.pmb_count || 0) + ' Mahasiswa', String(s.pmb_count || 0));

    // MoU
    let mouDisplay = '-';
    let mouCopy = '';
    if (s.mou_drive_link) {
        mouDisplay = s.mou_drive_link;
        mouCopy = s.mou_drive_link;
    } else if (s.mou_url) {
        mouDisplay = 'Berkas PDF Tersedia (' + (s.mou_file || 'MOU.pdf') + ')';
        mouCopy = s.mou_url;
    }
    setVal('dt_mou_text', mouDisplay, mouCopy);

    const driveLinkBtn = document.getElementById('schoolDetailDriveLink');
    if (driveLinkBtn) {
        if (s.mou_drive_link) {
            driveLinkBtn.href = s.mou_drive_link;
            driveLinkBtn.style.display = 'inline-flex';
        } else {
            driveLinkBtn.style.display = 'none';
        }
    }

    const pdfLinkBtn = document.getElementById('schoolDetailPdfLink');
    if (pdfLinkBtn) {
        if (s.mou_url) {
            pdfLinkBtn.href = s.mou_url;
            pdfLinkBtn.style.display = 'inline-flex';
        } else {
            pdfLinkBtn.style.display = 'none';
        }
    }

    setVal('dt_notes', s.notes);
    setVal('dt_creator_name', s.creator_name);
    setVal('dt_created_at', s.created_at);

    // WhatsApp quick chat button
    const waBtn = document.getElementById('schoolDetailWaBtn');
    if (waBtn) {
        if (s.contact_phone) {
            let cleanPhone = s.contact_phone.replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
            if (cleanPhone.length >= 8) {
                waBtn.href = 'https://wa.me/' + cleanPhone;
                waBtn.style.display = 'inline-flex';
            } else {
                waBtn.style.display = 'none';
            }
        } else {
            waBtn.style.display = 'none';
        }
    }

    // Edit link
    const editBtn = document.getElementById('schoolDetailEditBtn');
    if (editBtn) {
        editBtn.href = s.edit_url || '#';
    }

    // Store current school data for "Copy All"
    window.currentViewingSchoolData = s;

    dialog.showModal();
}

function copyFieldById(elId, fieldName, btn) {
    const el = document.getElementById(elId);
    if (!el) return;
    const text = el.getAttribute('data-raw-copy') || el.textContent.trim();
    if (!text || text === '-') {
        showCopyNotification('Field ' + fieldName + ' kosong (tidak ada data).', 'warning');
        return;
    }

    copyTextToClipboard(text, () => {
        const originalHtml = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = `<iconify-icon icon="solar:check-circle-bold"></iconify-icon> <span>Tersalin!</span>`;
        showCopyNotification(`✓ ${fieldName} berhasil disalin ke clipboard!`, 'success');

        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = originalHtml;
        }, 1800);
    });
}

function copyAllSchoolData() {
    const s = window.currentViewingSchoolData;
    if (!s) return;

    const lines = [
        'DETAIL DATA SEKOLAH - ONE CAMPUS SIXTY SCHOOL',
        '==============================================',
        'Nama Sekolah       : ' + (s.school_name || '-'),
        'NPSN               : ' + (s.npsn || '-'),
        'Kota / Kabupaten   : ' + (s.city || '-'),
        'Bentuk / Status    : ' + (s.school_type || 'SMA') + ' (' + (s.school_status || 'Negeri') + ')',
        'Nama Kepala Sekolah: ' + (s.headmaster_name || '-'),
        'No. Telp Sekolah   : ' + (s.school_phone || '-'),
        'Email Resmi Sekolah: ' + (s.school_email || '-'),
        '----------------------------------------------',
        'Nama PIC / Guru BK : ' + (s.contact_name || '-'),
        'Jabatan / Posisi   : ' + (s.position || 'Kontak Sekolah'),
        'No. HP / WA PIC    : ' + (s.contact_phone || '-'),
        'Email Kontak PIC   : ' + (s.contact_email || '-'),
        '----------------------------------------------',
        'Kampus Pengusul    : ' + (s.campus_name || '-') + (s.campus_code ? ' [' + s.campus_code + ']' : ''),
        'Periode PMB        : ' + (s.pmb_period || '-'),
        'Status Kerjasama   : ' + (s.status || 'Aktif'),
        'Status Beasiswa BJS: ' + (s.bjs_status || 'Belum Aktif'),
        'Perolehan Mhs PMB  : ' + (s.pmb_count || 0) + ' Mahasiswa',
        'Dokumen MoU        : ' + (s.mou_drive_link || (s.mou_file ? 'Berkas Lokal' : '-')),
        'Catatan            : ' + (s.notes || '-'),
        '==============================================',
        'Sumber: EventHub UBSI • Disalin pada ' + new Date().toLocaleString('id-ID'),
    ];

    const allText = lines.join('\n');
    copyTextToClipboard(allText, () => {
        const btn = document.getElementById('btnCopyAllSchoolData');
        const orig = btn.innerHTML;
        btn.style.background = '#059669';
        btn.style.borderColor = '#059669';
        btn.innerHTML = `<iconify-icon icon="solar:check-circle-bold"></iconify-icon> <span>Semua Data Tersalin!</span>`;
        showCopyNotification('✓ Seluruh data sekolah berhasil disalin ke clipboard!', 'success');

        setTimeout(() => {
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.innerHTML = orig;
        }, 2000);
    });
}

function copyTextToClipboard(text, onSuccess) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(onSuccess).catch(() => {
            fallbackCopy(text, onSuccess);
        });
    } else {
        fallbackCopy(text, onSuccess);
    }
}

function fallbackCopy(text, onSuccess) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    ta.style.top = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
        document.execCommand('copy');
        if (onSuccess) onSuccess();
    } catch (err) {
        console.error('Fallback copy failed', err);
    }
    document.body.removeChild(ta);
}

function showCopyNotification(msg, type) {
    const toast = document.getElementById('schoolCopyToast');
    const toastMsg = document.getElementById('schoolCopyToastMsg');
    if (!toast || !toastMsg) return;

    toastMsg.textContent = msg;
    toast.style.color = type === 'warning' ? '#f59e0b' : '#10b981';
    toast.style.display = 'inline-flex';

    if (window._copyToastTimer) clearTimeout(window._copyToastTimer);
    window._copyToastTimer = setTimeout(() => {
        toast.style.display = 'none';
    }, 2800);
}

// Global exposure & event delegation
window.openSchoolDetailModal = openSchoolDetailModal;
window.copyFieldById = copyFieldById;
window.copyAllSchoolData = copyAllSchoolData;

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-view-school');
    if (btn) {
        e.preventDefault();
        openSchoolDetailModal(btn);
    }
});
</script>

<script>
function showSchoolStudents(schoolId, schoolName) {
    const dialog = document.getElementById('dialog-school-students');
    const loading = document.getElementById('modalStudentLoading');
    const content = document.getElementById('modalStudentContent');
    const tableBody = document.getElementById('modalStudentTableBody');
    const nameEl = document.getElementById('modalStudentSchoolName');
    const periodEl = document.getElementById('modalStudentPeriodBadge');
    const totalEl = document.getElementById('modalStudentTotal');

    nameEl.textContent = 'Daftar Mahasiswa Baru — ' + schoolName;
    loading.style.display = 'block';
    loading.textContent = 'Memuat data mahasiswa baru...';
    content.style.display = 'none';
    tableBody.innerHTML = '';
    dialog.showModal();

    fetch('<?= site_url('one-campus/schools/') ?>' + schoolId + '/students')
        .then(res => res.json())
        .then(data => {
            loading.style.display = 'none';
            content.style.display = 'block';
            if (data.status === 'success' && data.students && data.students.length > 0) {
                periodEl.textContent = data.school.pmb_period || 'Periode PMB';
                totalEl.textContent = 'Total: ' + data.students.length + ' Mahasiswa Baru Terdaftar';
                let html = '';
                data.students.forEach((s, idx) => {
                    const cCode = s.campus_kode_kampus || s.campus_code || '';
                    const ubsiCode = s.campus_kode_ubsi || s.kode_ubsi || '';
                    const campusBadge = cCode ? `<span class="status status-aktif" style="font-size: 0.7rem; padding: 1px 6px; margin-right: 4px; font-weight: 700;">${cCode}</span>` : '';
                    const ubsiBadge = ubsiCode ? `<span style="font-size: 0.7rem; color: var(--text-muted, #64748b); margin-left: 3px;">(${ubsiCode})</span>` : '';
                    const campusLabel = s.campus_name ? `${campusBadge}${s.campus_name}${ubsiBadge}` : '-';

                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td><strong>${s.student_name || '-'}</strong></td>
                        <td><code>${s.registration_no || '-'}</code></td>
                        <td>${campusLabel}</td>
                        <td>${s.study_program || '-'}</td>
                        <td>${s.student_phone || '-'}</td>
                        <td><span class="status status-aktif" style="font-size: 0.74rem;">${s.status || 'Mahasiswa Baru'}</span></td>
                    </tr>`;
                });
                tableBody.innerHTML = html;
            } else {
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 18px;">Belum ada rincian data mahasiswa baru terdaftar untuk sekolah ini. Gunakan fitur "Upload Data PMB (Excel)" untuk mengisi.</td></tr>';
                totalEl.textContent = 'Total: 0 Mahasiswa';
            }
        })
        .catch(err => {
            loading.textContent = 'Gagal memuat data: ' + err;
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function setupSchoolNpsnLookup(formEl, lookupUrl) {
    if (!formEl) return;
    const npsnInput = formEl.querySelector('input[name="npsn"]');
    const nameInput = formEl.querySelector('input[name="school_name"]');
    const cityInput = formEl.querySelector('input[name="city"]');
    const typeSelect = formEl.querySelector('select[name="school_type"]');
    const statusSelect = formEl.querySelector('select[name="school_status"]');
    const dropdown = formEl.querySelector('.school-autocomplete-dropdown');
    const indicator = formEl.querySelector('.npsn-lookup-indicator');
    const hint = formEl.querySelector('.npsn-autofill-hint');

    if (!npsnInput || !nameInput) return;

    let debounceTimer = null;

    function fillForm(data) {
        if (!data) return;
        npsnInput.value = data.npsn || npsnInput.value;
        nameInput.value = data.school_name || data.name || nameInput.value;
        if (cityInput && data.city) cityInput.value = data.city;
        if (typeSelect && data.school_type) {
            for (let opt of typeSelect.options) {
                if (opt.value.toUpperCase() === data.school_type.toUpperCase()) {
                    typeSelect.value = opt.value;
                    break;
                }
            }
        }
        if (statusSelect && data.school_status) {
            for (let opt of statusSelect.options) {
                if (opt.value.toUpperCase() === data.school_status.toUpperCase()) {
                    statusSelect.value = opt.value;
                    break;
                }
            }
        }

        [npsnInput, nameInput, cityInput, typeSelect, statusSelect].forEach(el => {
            if (el) {
                el.style.transition = 'box-shadow 0.3s, border-color 0.3s';
                el.style.borderColor = '#10b981';
                el.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.25)';
                setTimeout(() => {
                    el.style.borderColor = '';
                    el.style.boxShadow = '';
                }, 1500);
            }
        });

        if (hint) {
            hint.innerHTML = `✅ <strong>${escapeHtml(data.school_name || data.name)}</strong> terisi otomatis dari Data Kemendikdasmen/Kemenag!`;
            hint.style.color = '#10b981';
        }

        if (dropdown) dropdown.style.display = 'none';
    }

    function doLookup(query) {
        query = (query || '').trim();
        if (query.length < 3) {
            if (dropdown) dropdown.style.display = 'none';
            if (indicator) indicator.style.display = 'none';
            return;
        }

        if (indicator) indicator.style.display = 'inline';

        fetch(lookupUrl + '?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(res => {
                if (indicator) indicator.style.display = 'none';
                if (res.status !== 'success' || !res.suggestions || res.suggestions.length === 0) {
                    if (dropdown) {
                        const isNumeric = /^\d+$/.test(query);
                        let emptyHtml = '';
                        if (isNumeric) {
                            emptyHtml = `
                                <div style="padding: 12px 14px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.78rem;">
                                    <div style="font-weight: 700; color: #dc2626; margin-bottom: 4px;">⚠️ NPSN "${escapeHtml(query)}" tidak ditemukan</div>
                                    <div style="font-size: 0.72rem; line-height: 1.4;">Format 8 digit NPSN di Indonesia diawali kode provinsi 10 s.d 70 (contoh: 20100104, 202xxxxx, 697xxxxx).</div>
                                </div>
                            `;
                        } else {
                            emptyHtml = `
                                <div style="padding: 12px 14px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.78rem;">
                                    Tidak ditemukan sekolah dengan kata kunci "<strong>${escapeHtml(query)}</strong>".
                                </div>
                            `;
                        }
                        dropdown.innerHTML = emptyHtml;
                        dropdown.style.display = 'block';
                    }
                    return;
                }

                if (res.exact_match && query.length === 8 && /^\d+$/.test(query)) {
                    fillForm(res.exact_match);
                }

                renderDropdown(res.suggestions, query);
            })
            .catch(() => {
                if (indicator) indicator.style.display = 'none';
            });
    }

    function highlightQuery(text, q) {
        if (!text || !q) return escapeHtml(text || '');
        const escText = escapeHtml(text);
        const escQ = escapeHtml(q).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return escText.replace(new RegExp(`(${escQ})`, 'gi'), '<mark style="background: #fef08a; color: #854d0e; padding: 0 2px; border-radius: 2px;">$1</mark>');
    }

    function renderDropdown(items, query) {
        if (!dropdown) return;
        dropdown.innerHTML = '';

        const header = document.createElement('div');
        header.style.padding = '6px 12px';
        header.style.fontSize = '0.7rem';
        header.style.fontWeight = '700';
        header.style.color = '#64748b';
        header.style.background = 'var(--surface-subtle, #f8fafc)';
        header.style.borderBottom = '1px solid var(--border-color, #e2e8f0)';
        header.style.textTransform = 'uppercase';
        header.style.letterSpacing = '0.04em';
        header.innerHTML = `Rekomendasi Sekolah (${items.length} hasil)`;
        dropdown.appendChild(header);

        items.forEach(item => {
            const div = document.createElement('div');
            div.style.padding = '8px 12px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid var(--border-color, #e2e8f0)';
            div.style.fontSize = '0.8rem';
            div.style.transition = 'background 0.15s';
            div.className = 'school-dropdown-item';

            const statusColor = (item.school_status === 'Negeri') ? '#0284c7' : '#d97706';
            const statusBg = (item.school_status === 'Negeri') ? '#e0f2fe' : '#fef3c7';

            div.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-family: monospace; font-weight: 800; background: #e0e7ff; color: #4338ca; padding: 1px 6px; border-radius: 4px; font-size: 0.74rem;">${highlightQuery(item.npsn, query)}</span>
                        <strong style="color: var(--text-main, #1e293b);">${highlightQuery(item.school_name || item.name, query)}</strong>
                    </div>
                    <span style="background: ${statusBg}; color: ${statusColor}; font-size: 0.68rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; white-space: nowrap;">${escapeHtml(item.school_type)} ${escapeHtml(item.school_status)}</span>
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted, #64748b); margin-top: 3px;">
                    🏛 ${escapeHtml(item.city || '-')}${item.district ? ' &bull; ' + escapeHtml(item.district) : ''}
                </div>
            `;

            div.addEventListener('mouseenter', () => {
                div.style.background = 'var(--surface-hover, #f1f5f9)';
            });
            div.addEventListener('mouseleave', () => {
                div.style.background = '';
            });

            div.addEventListener('click', (e) => {
                e.stopPropagation();
                fillForm(item);
            });

            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    }

    npsnInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const val = e.target.value.trim();
        debounceTimer = setTimeout(() => doLookup(val), 250);
    });

    npsnInput.addEventListener('blur', (e) => {
        const val = e.target.value.trim();
        if (val.length === 8 && /^\d+$/.test(val)) {
            doLookup(val);
        }
        setTimeout(() => {
            if (dropdown) dropdown.style.display = 'none';
        }, 300);
    });

    nameInput.addEventListener('input', (e) => {
        if (npsnInput.value.trim() !== '') return;
        clearTimeout(debounceTimer);
        const val = e.target.value.trim();
        if (val.length >= 3) {
            debounceTimer = setTimeout(() => doLookup(val), 350);
        }
    });

    document.addEventListener('click', (e) => {
        if (!formEl.contains(e.target)) {
            if (dropdown) dropdown.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const schoolForm = document.querySelector('.one-campus-school-form');
    if (schoolForm) {
        setupSchoolNpsnLookup(schoolForm, '<?= site_url('one-campus/schools/lookup-npsn') ?>');
    }
});
</script>

<style>
html.dark .school-autocomplete-dropdown {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
    box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.6) !important;
}
html.dark .school-dropdown-item {
    border-bottom-color: #334155 !important;
}
html.dark .school-dropdown-item strong {
    color: #f1f5f9 !important;
}
html.dark .school-dropdown-item:hover {
    background: #334155 !important;
}
</style>
