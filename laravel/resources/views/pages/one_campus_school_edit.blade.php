<?php
$school = $school ?? [];
$campuses = $campuses ?? [];
$availablePmbPeriods = $availablePmbPeriods ?? [];
$gdriveSettings = $gdriveSettings ?? [];

$schoolId = (int) ($school['id'] ?? 0);
$currentPmb = $school['pmb_period'] ?? 'September 2026 - September 2027';
$backUrl = site_url('one-campus/schools?pmb_period=' . urlencode($currentPmb));
$hasMouFile = ! empty($school['mou_file']);
$hasDriveLink = ! empty($school['mou_drive_link']);
?>

<div class="toolbar" style="margin-bottom: 20px;">
    <div>
        <span class="eyebrow">One Campus Sixty School &bull; Edit Data Sekolah Mitra</span>
        <h2>Edit Data Sekolah Penerima BJS</h2>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <a class="ghost-button compact-button" href="<?= $backUrl ?>">
            &larr; Kembali ke Rekap Sekolah
        </a>
    </div>
</div>

<section class="panel" style="border-top: 3px solid #0d9488; padding: 22px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main, #1e293b);">Data Sekolah Penerima Beasiswa</h3>
            <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: var(--text-muted, #64748b);">
                Perbarui rincian sekolah, kontak PIC, klaster tahun PMB, serta dokumen MOU kerjasama.
            </p>
        </div>
        <a href="<?= $backUrl ?>" class="compact-button" style="background: #10b981; color: #ffffff; border: none; border-radius: 6px; padding: 7px 16px; font-size: 0.84rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
            + Kembali
        </a>
    </div>

    <form action="<?= site_url('one-campus/schools/' . $schoolId . '/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Baris 1: NPSN, Nama Sekolah, Kota, Jenis Skl, Status Skl -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">NPSN</span>
                <input class="control" type="text" name="npsn" value="<?= esc($school['npsn'] ?? '', 'attr') ?>" placeholder="Nomor Pokok Sekolah">
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Nama Sekolah <span style="color: #ef4444;">*</span></span>
                <input class="control" type="text" name="school_name" value="<?= esc($school['school_name'] ?? '', 'attr') ?>" placeholder="Nama Sekolah Lengkap" required>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Kota</span>
                <input class="control" type="text" name="city" value="<?= esc($school['city'] ?? '', 'attr') ?>" placeholder="Kota / Kabupaten">
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Jenis Skl</span>
                <select class="control" name="school_type">
                    <option value="SMA" <?= ($school['school_type'] ?? '') === 'SMA' ? 'selected' : '' ?>>SMA</option>
                    <option value="SMK" <?= ($school['school_type'] ?? '') === 'SMK' ? 'selected' : '' ?>>SMK</option>
                    <option value="MA" <?= ($school['school_type'] ?? '') === 'MA' ? 'selected' : '' ?>>MA</option>
                    <option value="Lainnya" <?= ($school['school_type'] ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Status Skl</span>
                <select class="control" name="school_status">
                    <option value="Negeri" <?= ($school['school_status'] ?? '') === 'Negeri' ? 'selected' : '' ?>>Negeri</option>
                    <option value="Swasta" <?= ($school['school_status'] ?? '') === 'Swasta' ? 'selected' : '' ?>>Swasta</option>
                </select>
            </label>
        </div>

        <!-- Baris 2: Nama Kepala Sekolah, Telp Sekolah, Email Sekolah -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Nama Kepala Sekolah</span>
                <input class="control" type="text" name="headmaster_name" value="<?= esc($school['headmaster_name'] ?? '', 'attr') ?>" placeholder="Nama Kepala Sekolah">
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Telp Sekolah</span>
                <input class="control" type="text" name="school_phone" value="<?= esc($school['school_phone'] ?? '', 'attr') ?>" placeholder="No. Telp Resmi Sekolah">
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Email Sekolah</span>
                <input class="control" type="email" name="school_email" value="<?= esc($school['school_email'] ?? '', 'attr') ?>" placeholder="official@sekolah.sch.id">
            </label>
        </div>

        <!-- Baris 3: Nama Kontak, Telp Kontak, Email Kontak -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Nama Kontak (PIC) <span style="color: #ef4444;">*</span></span>
                <input class="control" type="text" name="contact_name" value="<?= esc($school['contact_name'] ?? ($school['teacher_name'] ?? ''), 'attr') ?>" placeholder="Nama Guru / PIC BK" required>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Telp Kontak <span style="color: #ef4444;">*</span></span>
                <input class="control" type="text" name="contact_phone" value="<?= esc($school['contact_phone'] ?? ($school['phone'] ?? ''), 'attr') ?>" placeholder="No. HP / WA PIC" required>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Email Kontak</span>
                <input class="control" type="email" name="contact_email" value="<?= esc($school['contact_email'] ?? '', 'attr') ?>" placeholder="pic@sekolah.com">
            </label>
        </div>

        <!-- Baris 4: Status Skl, Tahun PMB, Kampus Pelaksana, Perolehan PMB -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Status</span>
                <select class="control" name="status">
                    <option value="Aktif" <?= ($school['status'] ?? 'Aktif') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Nonaktif" <?= ($school['status'] ?? '') === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Tahun PMB</span>
                <select class="control" name="pmb_period">
                    <?php foreach ($availablePmbPeriods as $pKey => $pLabel): ?>
                        <option value="<?= esc($pKey) ?>" <?= $currentPmb === $pKey ? 'selected' : '' ?>>
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
                        <option value="<?= esc($campus['id']) ?>" <?= (string) ($school['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                            <?= esc($campus['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Perolehan Siswa PMB</span>
                <input class="control" type="number" name="pmb_count" min="0" value="<?= esc((string) ($school['pmb_count'] ?? 0), 'attr') ?>">
            </label>
        </div>

        <!-- Baris 5: Media Upload MOU & Google Drive -->
        <div style="background: var(--bg-hover, #f8fafc); border: 1px dashed var(--border-color, #cbd5e1); border-radius: 8px; padding: 16px 18px; margin-bottom: 16px;">
            <div style="font-weight: 700; font-size: 0.9rem; margin-bottom: 12px; color: var(--text-main, #1e293b); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>&#128196;</span>
                    <span>Dokumen MOU (PDF) &amp; Sinkronisasi Google Drive</span>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <?php if ($hasMouFile): ?>
                        <a href="<?= site_url('one-campus/schools/' . $schoolId . '/mou') ?>" target="_blank" class="ghost-button compact-button" style="color: #ef4444; border-color: #fca5a5; display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem;">
                            &#128196; Lihat PDF MOU Saat Ini
                        </a>
                    <?php endif; ?>
                    <?php if ($hasDriveLink): ?>
                        <a href="<?= esc($school['mou_drive_link']) ?>" target="_blank" rel="noopener noreferrer" class="ghost-button compact-button" style="color: #0284c7; border-color: #7dd3fc; display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem;">
                            &#9729; Buka di Google Drive &nearr;
                        </a>
                    <?php elseif ($hasMouFile && ! empty($gdriveSettings['is_configured'])): ?>
                        <button type="submit" form="syncDriveForm" class="ghost-button compact-button" style="color: #0d9488; border-color: #5eead4; display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem;" title="Kirim file MOU saat ini ke Google Drive">
                            &#9729; Kirim ke Google Drive
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                <label>
                    <span style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">
                        <?= $hasMouFile ? 'Ganti File MOU (.pdf, maks. 15MB)' : 'Upload File MOU (.pdf, maks. 15MB)' ?>
                    </span>
                    <input class="control" type="file" name="mou_file" accept=".pdf,application/pdf">
                    <span style="font-size: 0.76rem; color: var(--text-muted, #64748b); display: block; margin-top: 3px;">
                        <?= $hasMouFile ? 'Kosongkan jika tidak ingin mengganti file PDF saat ini.' : 'Unggah dokumen MOU resmi untuk diarsip ke Google Drive Admin.' ?>
                    </span>
                </label>
                <label>
                    <span style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Link Google Drive Dokumen MOU (Opsional)</span>
                    <input class="control" type="url" name="mou_drive_link" value="<?= esc($school['mou_drive_link'] ?? '', 'attr') ?>" placeholder="https://drive.google.com/file/d/.../view?usp=sharing">
                    <span style="font-size: 0.76rem; color: var(--text-muted, #64748b); display: block; margin-top: 3px;">
                        Target Folder Admin: <strong><?= esc($gdriveSettings['folder_name'] ?? 'MOU BJS Sekolah') ?></strong> (<?= esc($gdriveSettings['admin_email'] ?? '') ?>)
                    </span>
                </label>
            </div>
        </div>

        <!-- Baris 6: Keterangan -->
        <label style="display: block; margin-bottom: 22px;">
            <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Keterangan / Catatan Tambahan</span>
            <textarea class="control" name="notes" rows="3" placeholder="Catatan mengenai kuota beasiswa, tanggal penandatanganan MOU, dll."><?= esc($school['notes'] ?? '') ?></textarea>
        </label>

        <!-- Tombol Aksi Simpan & Hapus -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-top: 14px; border-top: 1px solid var(--border-color, #e2e8f0);">
            <div style="display: flex; gap: 10px; align-items: center;">
                <button class="primary-button" type="submit" style="background: #00b4d8; background: linear-gradient(135deg, #0d9488, #0ea5e9); border: none; color: #ffffff; padding: 10px 24px; font-weight: 700; border-radius: 6px; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                    Simpan Perubahan Sekolah
                </button>
                <a class="ghost-button" href="<?= $backUrl ?>" style="padding: 10px 18px;">
                    Batal
                </a>
            </div>
        </div>
    </form>

    <!-- Tombol Hapus Terpisah -->
    <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
        <?php if ($hasMouFile && ! $hasDriveLink && ! empty($gdriveSettings['is_configured'])): ?>
            <form id="syncDriveForm" action="<?= site_url('one-campus/schools/' . $schoolId . '/sync-drive') ?>" method="post" style="display: none;">
                <?= csrf_field() ?>
            </form>
        <?php endif; ?>
        <form action="<?= site_url('one-campus/schools/' . $schoolId . '/delete') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sekolah <?= esc($school['school_name'] ?? '', 'attr') ?>? Data yang dihapus tidak dapat dikembalikan.');">
            <?= csrf_field() ?>
            <button class="danger-button compact-button" type="submit" style="font-size: 0.8rem; padding: 6px 14px;">
                Hapus Data Sekolah Ini
            </button>
        </form>
    </div>

    <!-- Rincian Mahasiswa Baru PMB Terdaftar dari Sekolah Ini -->
    <div style="margin-top: 24px; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 18px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.1rem;">&#127891;</span>
                <div>
                    <h3 style="margin: 0; font-size: 0.96rem; font-weight: 700; color: var(--text-main, #1e293b);">
                        Rincian Mahasiswa Baru PMB (<?= count($students ?? []) ?> Mahasiswa Terdaftar)
                    </h3>
                    <span style="font-size: 0.76rem; color: var(--text-muted, #64748b);">
                        Data hasil import Excel PMB untuk tahun <?= esc($school['pmb_period'] ?? '') ?>
                    </span>
                </div>
            </div>
            <a href="<?= site_url('one-campus/pmb-template') ?>" class="ghost-button compact-button" style="font-size: 0.76rem; color: #0284c7; border-color: #7dd3fc; display: inline-flex; align-items: center; gap: 4px;">
                <span>&#128229;</span> Unduh Format Excel PMB
            </a>
        </div>

        <div class="table-wrap" style="max-height: 350px; overflow-y: auto;">
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
                        <th>Tanggal Input</th>
                        <th style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($students)): ?>
                        <?php foreach ($students as $stIdx => $student): ?>
                            <tr>
                                <td><?= $stIdx + 1 ?></td>
                                <td><strong><?= esc($student['student_name']) ?></strong></td>
                                <td><code><?= esc($student['registration_no'] ?: '-') ?></code></td>
                                <td>
                                    <?php
                                    $cCode = $student['campus_kode_kampus'] ?? ($student['campus_code'] ?? '');
                                    $ubsiCode = $student['campus_kode_ubsi'] ?? ($student['kode_ubsi'] ?? '');
                                    ?>
                                    <?php if (! empty($cCode)): ?>
                                        <span class="status status-aktif" style="font-size: 0.7rem; padding: 1px 6px; margin-right: 4px; font-weight: 700;"><?= esc($cCode) ?></span>
                                    <?php endif; ?>
                                    <?= esc($student['campus_name'] ?? '-') ?>
                                    <?php if (! empty($ubsiCode)): ?>
                                        <span style="font-size: 0.7rem; color: var(--text-muted, #64748b);"> (<?= esc($ubsiCode) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($student['study_program'] ?: '-') ?></td>
                                <td><?= esc($student['student_phone'] ?: '-') ?></td>
                                <td><span class="status status-aktif" style="font-size: 0.74rem;"><?= esc($student['status'] ?: 'Mahasiswa Baru') ?></span></td>
                                <td style="font-size: 0.76rem; color: var(--text-muted, #64748b);"><?= esc(substr((string) ($student['created_at'] ?? '-'), 0, 16)) ?></td>
                                <td>
                                    <form action="<?= site_url('one-campus/students/' . $student['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus data mahasiswa <?= esc($student['student_name'], 'attr') ?>?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="ghost-button compact-button" style="color: #ef4444; border-color: #fca5a5; padding: 2px 6px; font-size: 0.74rem;" title="Hapus Mahasiswa">&#10005;</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted, #64748b); padding: 18px;">
                                Belum ada rincian data mahasiswa baru untuk sekolah ini. Anda dapat mengunggah file Excel PMB di menu One Campus Sixty School.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

