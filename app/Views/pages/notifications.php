<div class="toolbar">
    <div>
        <span class="eyebrow">In-app notification</span>
        <h2>Inbox sistem</h2>
    </div>
    <form action="<?= site_url('notifications/read-all') ?>" method="post" class="inline-form">
        <?= csrf_field() ?>
        <button class="ghost-button" type="submit">Tandai semua dibaca</button>
    </form>
</div>

<section class="panel">
    <div class="notification-list expanded">
        <?php foreach ($notifications as $notification): ?>
            <article class="notification-item <?= $notification['read_at'] === null ? 'is-unread' : '' ?>">
                <div>
                    <span class="status status-<?= esc($notification['type']) ?>"><?= esc($notification['type']) ?></span>
                    <h3><?= esc($notification['message']) ?></h3>
                    <small><?= $notification['read_at'] === null ? 'Belum dibaca' : 'Dibaca ' . esc($notification['read_at']) ?></small>
                </div>
                <button class="ghost-button" type="button">Buka</button>
            </article>
        <?php endforeach; ?>
    </div>
</section>
