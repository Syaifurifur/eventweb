<div class="toolbar">
    <div>
        <span class="eyebrow">In-app notification</span>
        <h2>Inbox sistem</h2>
    </div>
    <?php if (! empty($notifications)): ?>
        <form action="<?= site_url('notifications/read-all') ?>" method="post" class="inline-form">
            <?= csrf_field() ?>
            <button class="ghost-button" type="submit">
                <iconify-icon icon="solar:check-read-outline" class="inline-block align-middle me-1"></iconify-icon>
                Tandai semua dibaca
            </button>
        </form>
    <?php endif; ?>
</div>

<section class="panel">
    <?php if (empty($notifications)): ?>
        <div style="padding: 48px 24px; text-align: center; color: var(--muted, #64748b);">
            <iconify-icon icon="solar:bell-off-outline" style="font-size: 48px; opacity: 0.5; display: block; margin: 0 auto 12px;"></iconify-icon>
            <h4 style="margin: 0 0 6px; font-size: 16px; font-weight: 600;">Belum Ada Notifikasi</h4>
            <p style="margin: 0; font-size: 13.5px;">Inbox notifikasi sistem Anda saat ini masih kosong.</p>
        </div>
    <?php else: ?>
        <div class="notification-list expanded">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $targetUrl = match ($notification['type']) {
                    'sixty-school' => site_url('one-campus/schools'),
                    'checklist' => site_url('task-manager'),
                    'approval' => site_url('approvals'),
                    'user' => site_url('users'),
                    default => site_url('dashboard'),
                };
                $typeLabel = match ($notification['type']) {
                    'sixty-school' => 'Sixty School',
                    'checklist' => 'Task PIC',
                    'approval' => 'Persetujuan',
                    'user' => 'Pengguna',
                    default => ucfirst((string) $notification['type']),
                };
                $timeText = ! empty($notification['created_at']) ? date('d M Y, H:i', strtotime($notification['created_at'])) . ' WIB' : '';
                ?>
                <article class="notification-item <?= $notification['read_at'] === null ? 'is-unread' : '' ?>">
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                            <span class="status status-<?= esc($notification['type']) ?>"><?= esc($typeLabel) ?></span>
                            <?php if ($notification['read_at'] === null): ?>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #ef4444; background: rgba(239, 68, 68, 0.12); padding: 2px 7px; border-radius: 999px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                    Baru
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 style="margin: 0 0 6px; font-size: 14.5px; line-height: 1.45;"><?= esc($notification['message']) ?></h3>
                        <small style="color: var(--muted, #64748b); font-size: 12px; display: block;">
                            <?= $timeText ? esc($timeText) . ' &bull; ' : '' ?>
                            <?= $notification['read_at'] === null ? 'Belum dibaca' : 'Dibaca ' . esc(date('d M Y H:i', strtotime($notification['read_at']))) ?>
                        </small>
                    </div>
                    <div>
                        <a href="<?= $targetUrl ?>" class="ghost-button" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            Buka
                            <iconify-icon icon="solar:arrow-right-outline" class="text-xs"></iconify-icon>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
