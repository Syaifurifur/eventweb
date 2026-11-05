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

$schoolDataForModal = [
    'id' => (int) ($school['id'] ?? 0),
    'school_name' => (string) ($school['school_name'] ?? ''),
    'npsn' => (string) ($school['npsn'] ?? ''),
    'city' => (string) ($school['city'] ?? ''),
    'school_type' => (string) ($school['school_type'] ?? 'SMA'),
    'school_status' => (string) ($school['school_status'] ?? 'Negeri'),
    'headmaster_name' => (string) ($school['headmaster_name'] ?? ''),
    'school_phone' => (string) ($school['school_phone'] ?? ''),
    'school_email' => (string) ($school['school_email'] ?? ''),
    'contact_name' => (string) ($school['contact_name'] ?? ($school['teacher_name'] ?? '')),
    'position' => (string) ($school['position'] ?? 'Kontak Sekolah'),
    'contact_phone' => (string) ($school['contact_phone'] ?? ($school['phone'] ?? '')),
    'contact_email' => (string) ($school['contact_email'] ?? ''),
    'campus_name' => (string) ($school['campus_name'] ?? 'Semua Kampus'),
    'campus_code' => (string) ($school['campus_kode_kampus'] ?? ($school['campus_code'] ?? '')),
    'campus_ubsi' => (string) ($school['campus_kode_ubsi'] ?? ($school['kode_ubsi'] ?? '')),
    'pmb_period' => (string) ($school['pmb_period'] ?? 'September 2026 - September 2027'),
    'status' => (string) ($school['status'] ?? 'Aktif'),
    'bjs_status' => (string) ($school['bjs_status'] ?? 'Belum Aktif'),
    'bjs_verifier_name' => (string) ($school['bjs_verifier_name'] ?? ''),
    'bjs_verified_at' => (string) ($school['bjs_verified_at'] ?? ''),
    'pmb_count' => (int) ($school['pmb_count'] ?? 0),
    'mou_file' => (string) ($school['mou_file'] ?? ''),
    'mou_drive_link' => (string) ($school['mou_drive_link'] ?? ''),
    'mou_url' => ! empty($school['mou_file']) ? site_url('one-campus/schools/' . $school['id'] . '/mou') : '',
    'edit_url' => site_url('one-campus/schools/' . $school['id'] . '/edit'),
    'notes' => (string) ($school['notes'] ?? ''),
    'creator_name' => (string) ($school['creator_name'] ?? 'Admin'),
    'created_at' => ! empty($school['created_at']) ? date('d M Y, H:i', strtotime($school['created_at'])) . ' WIB' : '-',
];
?>

<div class="toolbar" style="margin-bottom: 20px;">
    <div>
        <span class="eyebrow">One Campus Sixty School &bull; Edit Data Sekolah Mitra</span>
        <h2>Edit Data Sekolah Penerima BJS</h2>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="ghost-button compact-button btn-view-school"
                data-school='<?= esc(json_encode($schoolDataForModal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), 'attr') ?>'
                onclick="openSchoolDetailModal(this)"
                style="display: inline-flex; align-items: center; gap: 5px; font-weight: 600; color: #0284c7; border-color: #7dd3fc; background: rgba(2,132,199,0.06);"
                title="Lihat Data Lengkap &amp; Salin Tiap Field">
            <iconify-icon icon="solar:eye-outline" class="text-base"></iconify-icon>
            Lihat Data Sekolah &amp; Salin
        </button>
        <a class="ghost-button compact-button" href="<?= $backUrl ?>">
            &larr; Kembali ke Rekap Sekolah
        </a>
    </div>
</div>

<section class="panel" style="border-top: 3px solid #0d9488; padding: 22px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main, #1e293b);">Data Sekolah Penerima Beasiswa</h3>
            <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: var(--text-muted, #64748b);">
                Perbarui rincian sekolah, kontak PIC, klaster tahun PMB, serta dokumen MOU kerjasama.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="ghost-button compact-button btn-view-school"
                    data-school='<?= esc(json_encode($schoolDataForModal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), 'attr') ?>'
                    onclick="openSchoolDetailModal(this)"
                    style="display: inline-flex; align-items: center; gap: 5px; font-weight: 600; color: #0284c7; border-color: #7dd3fc; background: rgba(2,132,199,0.06);"
                    title="Lihat Data Lengkap &amp; Salin Tiap Field">
                <iconify-icon icon="solar:eye-outline" class="text-sm"></iconify-icon>
                Lihat &amp; Salin Field
            </button>
            <a href="<?= $backUrl ?>" class="compact-button" style="background: #10b981; color: #ffffff; border: none; border-radius: 6px; padding: 7px 16px; font-size: 0.84rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                + Kembali
            </a>
        </div>
    </div>

    <!-- CARD STATUS VERIFIKASI BJS OLEH ADMIN -->
    <?php $isBjsActive = ($school['bjs_status'] ?? '') === 'Aktif'; ?>
    <div style="background: <?= $isBjsActive ? '#ecfdf5' : '#fffbeb' ?>; border: 1px solid <?= $isBjsActive ? '#a7f3d0' : '#fde68a' ?>; border-radius: 10px; padding: 16px 20px; margin-bottom: 22px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
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

