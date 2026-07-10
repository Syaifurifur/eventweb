<?php
$events = $events ?? [];
$users = $users ?? [];
$selectedEventId = (string) old('event_id');
$selectedPhase = (string) (old('phase') ?: 'Pra Event');
$selectedUserId = (string) old('pic_user_id');
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">Task Manager</span>
        <h2>Buat Tugas Baru</h2>
    </div>
    <a class="ghost-button" href="<?= site_url('task-manager') ?>">Kembali</a>
</div>

<section class="panel task-form-panel">
    <?php if ($events === []): ?>
        <div class="task-empty-state">
            <strong>Belum ada event yang bisa diberi tugas</strong>
            <p>Tugas hanya dapat dibuat untuk event yang Anda kelola.</p>
        </div>
    <?php else: ?>
        <form action="<?= site_url('task-manager/tasks') ?>" method="post" class="task-create-form">
            <?= csrf_field() ?>

            <label>
                Event
                <select class="control" name="event_id" required>
                    <option value="">Pilih event</option>
                    <?php foreach ($events as $event): ?>
                        <?php
                        $eventId = (string) ($event['id'] ?? '');
                        $dateLabel = ! empty($event['event_date']) ? date('d M Y', strtotime((string) $event['event_date'])) : 'Tanpa tanggal';
                        ?>
                        <option value="<?= esc($eventId) ?>" <?= $selectedEventId === $eventId ? 'selected' : '' ?>>
                            <?= esc(($event['code'] ?? '-') . ' - ' . ($event['title'] ?? '-') . ' (' . $dateLabel . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Nama tugas
                <input class="control" type="text" name="name" value="<?= esc(old('name') ?? '') ?>" placeholder="Contoh: Siapkan materi narasumber" maxlength="160" required>
            </label>

            <label>
                Kepada siapa tugas diberikan
                <select class="control" name="pic_user_id" required>
                    <option value="">Pilih user</option>
                    <?php foreach ($users as $user): ?>
                        <?php $userId = (string) ($user['id'] ?? ''); ?>
                        <option value="<?= esc($userId) ?>" <?= $selectedUserId === $userId ? 'selected' : '' ?>>
                            <?= esc(($user['name'] ?? '-') . ' - ' . ($user['role'] ?? '-')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Fase
                <select class="control" name="phase" required>
                    <?php foreach (['Pra Event', 'Event', 'Pasca Event'] as $phase): ?>
                        <option value="<?= esc($phase) ?>" <?= $selectedPhase === $phase ? 'selected' : '' ?>><?= esc($phase) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Deadline
                <input class="control" type="date" name="deadline" value="<?= esc(old('deadline') ?? '') ?>">
            </label>

            <label class="task-create-description">
                Deskripsi tugas
                <textarea class="control" name="notes" rows="6" placeholder="Tuliskan instruksi, target output, atau catatan pengerjaan"><?= esc(old('notes') ?? '') ?></textarea>
            </label>

            <label class="task-create-material">
                Link bahan tugas
                <input class="control" type="url" name="material_drive_link" value="<?= esc(old('material_drive_link') ?? '') ?>" placeholder="https://drive.google.com/...">
            </label>

            <div class="button-row task-create-actions">
                <button class="primary-button" type="submit">Kirim Tugas</button>
                <a class="ghost-button" href="<?= site_url('task-manager') ?>">Batal</a>
            </div>
        </form>
    <?php endif; ?>
</section>
