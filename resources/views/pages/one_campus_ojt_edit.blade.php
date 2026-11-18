<?php
$ojt = $ojt ?? [];
$campuses = $campuses ?? [];
$ojtId = (int) ($ojt['id'] ?? 0);
$backUrl = site_url('one-campus/ojt');
$dateValue = static function ($value): string {
    $text = trim((string) $value);
    if ($text === '') {
        return '';
    }
    return strlen($text) >= 10 ? substr($text, 0, 10) : $text;
};
?>

<div class="toolbar" style="margin-bottom: 18px;">
    <div>
        <span class="eyebrow">One Campus Five OJT &bull; Modifikasi Data</span>
        <h2>Kelola Data Peserta OJT: <?= esc($ojt['student_name'] ?? '-') ?></h2>
    </div>
    <div class="toolbar-actions">
        <a class="ghost-button compact-button" href="<?= esc($backUrl) ?>">
            &larr; Kembali ke Daftar Five OJT
        </a>
    </div>
</div>

<section class="panel" style="border-top: 3px solid #3b82f6;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div>
            <span class="eyebrow" style="color: #3b82f6;">Formulir Modifikasi</span>
            <h3>Perbarui Data Peserta One Campus Five OJT</h3>
            <p style="margin: 4px 0 0 0; font-size: 0.86rem; color: var(--text-muted, #64748b);">
                Ubah rincian kampus, data mahasiswa OJT, asal sekolah/kampus, jurusan, kontak, serta periode pelaksanaan OJT.
            </p>
        </div>
    </div>

    <form action="<?= site_url('one-campus/ojt/' . $ojtId . '/update') ?>" method="post">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Kampus</span>
                <select class="control" name="campus_id">
                    <option value="">Pilih kampus</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($ojt['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                            <?= esc($campus['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Nama Anak OJT <span style="color: #ef4444;">*</span></span>
                <input class="control" type="text" name="student_name" value="<?= esc($ojt['student_name'] ?? '', 'attr') ?>" required maxlength="160">
            </label>

            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Asal Sekolah/Kampus <span style="color: #ef4444;">*</span></span>
                <input class="control" type="text" name="origin_school" value="<?= esc($ojt['origin_school'] ?? '', 'attr') ?>" required maxlength="180">
            </label>

            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Jurusan</span>
                <input class="control" type="text" name="major" value="<?= esc($ojt['major'] ?? '', 'attr') ?>" maxlength="120">
            </label>

            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">No Handphone</span>
                <input class="control" type="text" name="phone" value="<?= esc($ojt['phone'] ?? '', 'attr') ?>" maxlength="40">
            </label>

            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Bulan Awal OJT <span style="color: #ef4444;">*</span></span>
                <input class="control" type="date" name="ojt_start_month" value="<?= esc($dateValue($ojt['ojt_start_month'] ?? ''), 'attr') ?>" required>
            </label>

            <label>
                <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Bulan Akhir OJT <span style="color: #ef4444;">*</span></span>
                <input class="control" type="date" name="ojt_end_month" value="<?= esc($dateValue($ojt['ojt_end_month'] ?? ''), 'attr') ?>" required>
            </label>
        </div>

        <label style="display: block; margin-bottom: 20px;">
            <span style="display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 6px;">Keterangan</span>
            <textarea class="control" name="notes" rows="4" maxlength="2000" placeholder="Catatan tambahan mengenai peserta OJT..."><?= esc($ojt['notes'] ?? '') ?></textarea>
        </label>

        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border-color, #e2e8f0);">
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <button class="primary-button" type="submit">
                    &#128190; Simpan Perubahan OJT
                </button>
                <a class="ghost-button" href="<?= esc($backUrl) ?>">
                    Batal
                </a>
            </div>
        </div>
    </form>

    <div style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--border-color, #e2e8f0); display: flex; justify-content: flex-end;">
        <form action="<?= site_url('one-campus/ojt/' . $ojtId . '/delete') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data OJT atas nama <?= esc($ojt['student_name'] ?? '', 'js') ?>?')">
            <?= csrf_field() ?>
            <button class="danger-button compact-button" type="submit">
                &#128465; Hapus Data OJT
            </button>
        </form>
    </div>
</section>
