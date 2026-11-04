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

<!-- CARD STATUS VERIFIKASI BJS OLEH ADMIN -->
<?php $isBjsActive = ($school['bjs_status'] ?? '') === 'Aktif'; ?>
<div style="background: <?= $isBjsActive ? 'rgba(16, 185, 129, 0.12)' : 'rgba(245, 158, 11, 0.12)' ?>; border: 1px solid <?= $isBjsActive ? 'rgba(16, 185, 129, 0.4)' : 'rgba(245, 158, 11, 0.4)' ?>; border-radius: 10px; padding: 16px 20px; margin-bottom: 22px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
    <div style="display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $isBjsActive ? '#10b981' : '#f59e0b' ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; shrink-0;">
            <?= $isBjsActive ? '&#10003;' : '&#9203;' ?>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted, #64748b);">
                    Program Beasiswa Juara Sekolah (BJS)
                </span>
                <span class="status <?= $isBjsActive ? 'status-selesai' : 'status-menunggu' ?>" style="font-size: 0.8rem; font-weight: 800; padding: 3px 10px;">
                    <?= $isBjsActive ? '✓ BJS AKTIF &amp; TERVERIFIKASI' : '⏳ BJS BELUM AKTIF (MENUNGGU VERIFIKASI ADMIN)' ?>
                </span>
            </div>
            <div style="margin-top: 4px; font-size: 0.84rem; color: var(--text-main, #1e293b);">
                <?php if ($isBjsActive): ?>
                    Sekolah ini <strong>resmi aktif</strong> sebagai mitra penerima Beasiswa Juara Sekolah (BJS).
                    <?php if (! empty($school['bjs_verified_at'])): ?>
                        <span style="color: var(--text-muted, #64748b);">
                            &bull; Diverifikasi oleh: <strong><?= esc($school['bjs_verifier_name'] ?? 'Admin') ?></strong>
                            pada <?= esc(date('d M Y H:i', strtotime($school['bjs_verified_at']))) ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    Sekolah ini terdaftar di Sixty School namun <strong>belum diverifikasi aktif</strong> menerima kuota Beasiswa Juara Sekolah (BJS). Admin dapat menekan tombol verifikasi di samping untuk mengaktifkannya.
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Verifikasi BJS Cepat -->
    <form action="<?= site_url('one-campus/schools/' . $schoolId . '/verify-bjs') ?>" method="post" style="margin: 0;">
        <?= csrf_field() ?>
        <?php if ($isBjsActive): ?>
            <input type="hidden" name="action" value="unverify">
            <button type="submit" class="ghost-button" style="color: #ef4444; border-color: #fca5a5; font-weight: 600; padding: 8px 16px; font-size: 0.82rem;" onclick="return confirm('Batalkan verifikasi BJS untuk sekolah ini? Status akan kembali menjadi Belum Aktif.');">
                ✕ Batalkan Verifikasi BJS
            </button>
        <?php else: ?>
            <input type="hidden" name="action" value="verify">
            <button type="submit" class="primary-button" style="background: #059669; border-color: #059669; color: #ffffff; font-weight: 700; padding: 9px 18px; font-size: 0.86rem; box-shadow: 0 4px 12px rgba(5,150,105,0.25); display: inline-flex; align-items: center; gap: 6px;" onclick="return confirm('Verifikasi dan aktifkan status penerima Beasiswa Juara Sekolah (BJS) untuk <?= esc($school['school_name'], 'attr') ?>?');">
                <span>&#10003;</span> Verifikasi &amp; Aktifkan BJS
            </button>
        <?php endif; ?>
    </form>
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

        <!-- Baris 1: NPSN (dengan rekomendasi AI & auto-fill database nasional), Nama Sekolah, Kota, Jenis Skl, Status Skl -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <div style="position: relative;">
                <label>
                    <span style="display: flex; align-items: center; justify-content: space-between; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">
                        <span>NPSN</span>
                        <span class="npsn-lookup-indicator" style="font-size: 0.72rem; font-weight: 700; color: #2563eb; display: none;">⚡ Mencari...</span>
                    </span>
                    <input class="control school-npsn-input" type="text" name="npsn" value="<?= esc($school['npsn'] ?? '', 'attr') ?>" placeholder="Ketik 8 digit NPSN..." autocomplete="off">
                </label>
                <div class="school-autocomplete-dropdown" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; min-width: 340px; max-width: 440px; z-index: 1000; max-height: 280px; overflow-y: auto; background: var(--surface, #ffffff); border: 1px solid var(--border-color, #cbd5e1); border-radius: 12px; box-shadow: 0 12px 28px -6px rgba(15,23,42,0.25);"></div>
                <span class="npsn-autofill-hint" style="display: block; font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                    💡 Rekomendasi 42.856 sekolah se-Indonesia
                </span>
            </div>
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

        <!-- Baris 4: Status Skl, Status BJS, Tahun PMB, Kampus Pelaksana, Perolehan PMB -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Status Sekolah</span>
                <select class="control" name="status">
                    <option value="Aktif" <?= ($school['status'] ?? 'Aktif') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Nonaktif" <?= ($school['status'] ?? '') === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </label>
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px;">Status BJS</span>
                <select class="control" name="bjs_status">
                    <option value="Belum Aktif" <?= ($school['bjs_status'] ?? '') !== 'Aktif' ? 'selected' : '' ?>>⏳ Belum Aktif</option>
                    <option value="Aktif" <?= ($school['bjs_status'] ?? '') === 'Aktif' ? 'selected' : '' ?>>✓ Aktif (Terverifikasi)</option>
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

<script>
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
                        dropdown.innerHTML = '<div style="padding: 12px; text-align: center; color: var(--text-muted, #64748b); font-size: 0.78rem;">Tidak ditemukan sekolah yang cocok</div>';
                        dropdown.style.display = 'block';
                    }
                    return;
                }

                if (res.exact_match && query.length === 8 && /^\d+$/.test(query)) {
                    fillForm(res.exact_match);
                }

                renderDropdown(res.suggestions);
            })
            .catch(() => {
                if (indicator) indicator.style.display = 'none';
            });
    }

    function renderDropdown(items) {
        if (!dropdown) return;
        dropdown.innerHTML = '';
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
                        <span style="font-family: monospace; font-weight: 800; background: #e0e7ff; color: #4338ca; padding: 1px 6px; border-radius: 4px; font-size: 0.74rem;">${escapeHtml(item.npsn)}</span>
                        <strong style="color: var(--text-main, #1e293b);">${escapeHtml(item.school_name || item.name)}</strong>
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
    const editForm = document.querySelector('form[action*="/update"]');
    if (editForm) {
        setupSchoolNpsnLookup(editForm, '<?= site_url('one-campus/schools/lookup-npsn') ?>');
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

