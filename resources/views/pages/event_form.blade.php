<?php
$campuses = $campuses ?? [];
$eventData = $event ?? [];
$isEdit = ($formMode ?? 'create') === 'edit';
$formAction = $formAction ?? site_url('events');
$fieldValue = static function (string $key, string $fallback = '') use ($eventData) {
    return old($key, (string) ($eventData[$key] ?? $fallback));
};
$useDateRange = old('use_date_range', ! empty($eventData['event_end_date']) ? '1' : '') === '1';
$selectedCampusId = (string) old('campus_id', (string) ($eventData['campus_id'] ?? ''));
$selectedCampusName = '';
foreach ($campuses as $campus) {
    if ((string) $campus['id'] === $selectedCampusId) {
        $selectedCampusName = $campus['name'];
        break;
    }
}
$sessionUser = session('user') ?: [];
$picLabel = trim((string) (($sessionUser['name'] ?? '') . ' - ' . ($sessionUser['role'] ?? '')));
$picLabel = $picLabel !== '-' ? $picLabel : 'PIC mengikuti user login';
?>

<form class="form-layout" action="<?= esc($formAction) ?>" method="post">
    <?= csrf_field() ?>
    <section class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow"><?= $isEdit ? 'Update event' : 'Draft event' ?></span>
                <h2><?= $isEdit ? 'Modifikasi event' : 'Informasi utama' ?></h2>
            </div>
        </div>
        <div class="form-grid">
            <label>
                Judul event
                <input class="control" type="text" name="title" value="<?= esc($fieldValue('title', 'Seminar Nasional Data Driven Campus')) ?>" required>
            </label>
            <label>
                Kategori
                <select class="control" name="category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= esc($category['name']) ?>" <?= $fieldValue('category') === $category['name'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($categories === []): ?>
                    <small>Belum ada kategori. Admin Sistem perlu menambahkan kategori event terlebih dahulu.</small>
                <?php endif; ?>
            </label>
            <label class="span-2">
                Kampus pelaksana
                <div class="combo-field" data-campus-combobox>
                    <input class="control" type="search" list="campus-options" name="campus_search" placeholder="Cari dan pilih kampus pelaksana" value="<?= esc($selectedCampusName) ?>" autocomplete="off" required>
                    <input type="hidden" name="campus_id" value="<?= esc($selectedCampusId) ?>">
                    <datalist id="campus-options">
                        <?php foreach ($campuses as $campus): ?>
                            <option value="<?= esc($campus['name']) ?>" data-id="<?= esc($campus['id']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <?php if ($campuses === []): ?>
                    <small>Belum ada data kampus. Admin Sistem perlu menambahkan kampus terlebih dahulu.</small>
                <?php endif; ?>
            </label>
            <label>
                <span data-event-date-start-label><?= $useDateRange ? 'Tanggal Pelaksanaan Awal' : 'Tanggal Pelaksanaan' ?></span>
                <input class="control" type="date" name="event_date" value="<?= esc($fieldValue('event_date', '2026-06-10')) ?>">
            </label>
            <label class="check-row event-range-toggle">
                <input type="checkbox" name="use_date_range" value="1" data-event-date-range-toggle <?= $useDateRange ? 'checked' : '' ?>>
                <span>Event berlangsung lebih dari satu hari</span>
            </label>
            <label class="event-date-range-end" data-event-date-range-end>
                Tanggal Akhir Pelaksanaan
                <input class="control" type="date" name="event_end_date" value="<?= esc($fieldValue('event_end_date')) ?>">
            </label>
            <label>
                Jam
                <input class="control" type="time" name="event_time" value="<?= esc(substr($fieldValue('event_time', '09:00'), 0, 5)) ?>">
            </label>
            <label>
                Lokasi
                <input class="control" type="text" name="location" value="<?= esc($fieldValue('location', 'Auditorium Utama')) ?>">
            </label>
            <label>
                PIC
                <select class="control" disabled>
                    <option selected><?= esc($picLabel) ?></option>
                </select>
                <small>PIC otomatis mengikuti akun yang sedang login.</small>
            </label>
            <label class="span-2">
                Deskripsi
                <textarea class="control" name="description" rows="5"><?= esc($fieldValue('description', 'Kegiatan akademik untuk membahas pemanfaatan data dalam perencanaan program kampus.')) ?></textarea>
            </label>
            <label>
                Target peserta
                <input class="control" type="number" name="participants_count" min="0" value="<?= esc($fieldValue('participants_count', '120')) ?>">
            </label>
            <label>
                Anggaran
                <input class="control" type="number" name="budget" min="0" step="1000" value="<?= esc($fieldValue('budget', '12500000')) ?>">
            </label>
        </div>
    </section>

    <aside class="panel">
        <span class="eyebrow">Otomasi dokumen</span>
        <h2>Output awal</h2>
        <label class="check-row"><input type="checkbox" checked> Cek bentrok kalender</label>
        <label class="check-row"><input type="checkbox"> Kirim reminder email</label>
        <?php if (! $isEdit || in_array(($eventData['status'] ?? ''), ['draft', 'ditolak'], true)): ?>
            <button class="primary-button full proposal-submit-button" type="submit" name="submit_action" value="submit">Ajukan Event</button>
        <?php endif; ?>
        <button class="ghost-button full" type="submit" name="submit_action" value="draft"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Draft' ?></button>
    </aside>
</form>
