<div class="page-grid">
    <section class="panel pic-task-panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Tugas PIC</span>
                <h2>Pemberitahuan tugas</h2>
            </div>
            <?php if (($newPicTaskCount ?? 0) > 0): ?>
                <span class="status status-diajukan"><?= esc((string) $newPicTaskCount) ?> tugas baru</span>
            <?php endif; ?>
        </div>
        <div class="pic-task-notice <?= ($newPicTaskCount ?? 0) > 0 ? 'has-new-task' : '' ?>">
            <div>
                <strong><?= ($newPicTaskCount ?? 0) > 0 ? 'Ada tugas baru untuk Anda' : 'Belum ada tugas baru' ?></strong>
                <p><?= ($newPicTaskCount ?? 0) > 0 ? 'Buka Task Manager untuk melihat detail dan mengirim hasil tugas.' : 'Tugas baru dari event akan diberitahukan di bagian ini.' ?></p>
            </div>
            <a class="<?= ($newPicTaskCount ?? 0) > 0 ? 'primary-button' : 'ghost-button' ?>" href="<?= site_url('task-manager' . (($newPicTaskCount ?? 0) > 0 ? '?ack=1' : '')) ?>">Buka Task Manager</a>
        </div>
    </section>

    <section class="metric-grid">
        <?php foreach ($stats as $stat): ?>
            <article class="metric-card tone-<?= esc($stat['tone']) ?>">
                <span><?= esc($stat['label']) ?></span>
                <strong><?= esc(number_format((float) $stat['value'], 0, ',', '.')) ?></strong>
                <small><?= esc($stat['trend']) ?> dari periode sebelumnya</small>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="panel wide">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Monitoring event</span>
                <h2>Status pelaksanaan</h2>
            </div>
            <a class="text-link" href="<?= site_url('events') ?>">Lihat semua</a>
        </div>
        <div class="event-list">
            <?php foreach (array_slice($events, 0, 3) as $event): ?>
                <article class="event-row dashboard-event-row">
                    <a class="event-row-main" href="<?= site_url('events/' . $event['id']) ?>">
                        <span class="date-pill"><?= esc($event['date_compact_label'] ?? date('d M', strtotime($event['date']))) ?></span>
                        <div>
                            <strong><?= esc($event['title']) ?></strong>
                            <small><?= esc($event['location']) ?>, <?= esc($event['time']) ?></small>
                        </div>
                    </a>
                    <span class="status status-<?= esc($event['status']) ?>"><?= esc($event['status']) ?></span>
                    <?php if ($isAdminSystem ?? false): ?>
                        <form action="<?= site_url('events/' . $event['id'] . '/delete') ?>" method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <button class="danger-button compact-button" type="submit">Hapus</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Workflow</span>
                <h2>Aktivitas terbaru</h2>
            </div>
        </div>
        <div class="timeline">
            <?php foreach ($activities as $activity): ?>
                <div class="timeline-item">
                    <span><?= esc($activity['time']) ?></span>
                    <div>
                        <strong><?= esc($activity['title']) ?></strong>
                        <small><?= esc($activity['meta']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel wide">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Funnel PRD</span>
                <h2>Alur event</h2>
            </div>
        </div>
        <div class="flow-strip">
            <span>Buat Event</span>
            <span>Generate Proposal</span>
            <span>Approval Admin</span>
            <span>Sync Kalender</span>
            <span>Laporan</span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Notifikasi</span>
                <h2>Perlu perhatian</h2>
            </div>
        </div>
        <div class="notification-list">
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?= $notification['read_at'] === null ? 'is-unread' : '' ?>">
                    <strong><?= esc($notification['type']) ?></strong>
                    <p><?= esc($notification['message']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
