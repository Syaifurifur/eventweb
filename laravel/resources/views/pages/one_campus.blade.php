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
                <a class="ghost-button compact-button" href="<?= site_url('one-campus/schools/export') . $campusQuery ?>">
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
        </div>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            <a class="primary-button compact-button" href="<?= site_url('one-campus/schools/export') . $campusQuery ?>">
                Download Hasil XLSX (Sixty School)
            </a>
        </div>
    </div>

    <details class="panel management-drawer one-campus-drawer" style="margin-bottom: 18px;" <?= $selectedProgram === 'school' ? 'open' : '' ?>>
        <summary>
            <div>
                <span class="eyebrow">Tambah Data Baru</span>
                <strong>Input Laporan One Campus Sixty School</strong>
            </div>
            <span class="ghost-button compact-button">Form Input School</span>
        </summary>
        <form class="drawer-form one-campus-form" action="<?= site_url('one-campus/schools') ?>" method="post">
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
                Nama Sekolah
                <input class="control" type="text" name="school_name" required>
            </label>
            <label>
                Nama Guru
                <input class="control" type="text" name="teacher_name" required>
            </label>
            <label>
                No Handphone
                <input class="control" type="text" name="phone" required>
            </label>
            <label>
                Jabatan
                <input class="control" type="text" name="position" required>
            </label>
            <label>
                Jumlah PMB dari Sekolah
                <input class="control" type="number" name="pmb_count" min="0" value="<?= esc(old('pmb_count', '0')) ?>">
            </label>
            <label class="span-2">
                Keterangan
                <textarea class="control" name="notes" rows="3"></textarea>
            </label>
            <button class="primary-button" type="submit">Simpan Data Sixty School</button>
        </form>
    </details>

    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari school, guru, kampus, jabatan, atau PMB" data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>
    <div class="table-wrap">
        <table class="data-table" data-paginated-table data-page-size="10">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kampus</th>
                    <th>Nama Sekolah</th>
                    <th>Nama Guru</th>
                    <th>No Handphone</th>
                    <th>Jabatan</th>
                    <th>Jumlah PMB</th>
                    <th>Keterangan</th>
                    <th>Aksi CRUD</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schoolReports as $index => $row): ?>
                    <?php
                    $schoolFormId = 'one-campus-school-update-' . $row['id'];
                    $schoolSearchText = implode(' ', [
                        $row['campus_name'] ?? '',
                        $row['school_name'] ?? '',
                        $row['teacher_name'] ?? '',
                        $row['phone'] ?? '',
                        $row['position'] ?? '',
                        (string) ($row['pmb_count'] ?? ''),
                        $row['notes'] ?? '',
                    ]);
                    ?>
                    <tr data-page-row data-search-text="<?= esc($schoolSearchText, 'attr') ?>">
                        <td><?= esc((string) ($index + 1)) ?></td>
                        <td><?= esc($row['campus_name'] ?? '-') ?></td>
                        <td><?= esc($row['school_name']) ?></td>
                        <td><?= esc($row['teacher_name']) ?></td>
                        <td><?= esc($row['phone']) ?></td>
                        <td><?= esc($row['position']) ?></td>
                        <td><?= esc((string) ($row['pmb_count'] ?? 0)) ?></td>
                        <td><?= esc($row['notes'] ?? '-') ?></td>
                        <td>
                            <details class="row-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <div class="row-edit-panel">
                                    <form id="<?= esc($schoolFormId) ?>" action="<?= site_url('one-campus/schools/' . $row['id'] . '/update') ?>" method="post" class="form-grid compact-edit-grid">
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
                                            Nama Sekolah
                                            <input class="control table-control" type="text" name="school_name" value="<?= esc($row['school_name'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Nama Guru
                                            <input class="control table-control" type="text" name="teacher_name" value="<?= esc($row['teacher_name'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            No Handphone
                                            <input class="control table-control" type="text" name="phone" value="<?= esc($row['phone'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Jabatan
                                            <input class="control table-control" type="text" name="position" value="<?= esc($row['position'], 'attr') ?>" required>
                                        </label>
                                        <label>
                                            Jumlah PMB
                                            <input class="control table-control" type="number" name="pmb_count" min="0" value="<?= esc((string) ($row['pmb_count'] ?? 0), 'attr') ?>">
                                        </label>
                                        <label class="span-2">
                                            Keterangan
                                            <textarea class="control table-control" name="notes" rows="3"><?= esc($row['notes'] ?? '') ?></textarea>
                                        </label>
                                    </form>
                                    <div class="button-row">
                                        <button class="primary-button compact-button" form="<?= esc($schoolFormId) ?>" type="submit">Simpan School</button>
                                        <form action="<?= site_url('one-campus/schools/' . $row['id'] . '/delete') ?>" method="post" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button class="danger-button compact-button" type="submit">Hapus School</button>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($schoolReports === []): ?>
                    <tr data-empty-row>
                        <td colspan="9"><span class="muted-note">Tidak ada rekap One Campus Sixty School untuk kampus ini.</span></td>
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
