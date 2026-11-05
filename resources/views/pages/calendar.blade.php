<div class="toolbar calendar-navigation-toolbar">
    <div>
        <span class="eyebrow">Sinkronisasi jadwal</span>
        <h2><?= esc($calendarMonth['label']) ?></h2>
    </div>
    <div class="button-row">
        <a class="ghost-button" href="<?= site_url('calendar?month=' . $calendarMonth['previous']) ?>">&lsaquo; Sebelumnya</a>
        <a class="ghost-button" href="<?= site_url('calendar?month=' . date('Y-m')) ?>">Bulan Ini</a>
        <a class="ghost-button" href="<?= site_url('calendar?month=' . $calendarMonth['next']) ?>">Berikutnya &rsaquo;</a>
    </div>
</div>

<div class="toolbar calendar-sync-toolbar">
    <div>
        <span class="eyebrow">Google Calendar</span>
        <h2>Sinkronisasi <?= esc($calendarMonth['label']) ?></h2>
    </div>
    <div class="button-row">
        <?php if (($googleCalendar['connected'] ?? false) === true): ?>
            <span class="status status-disetujui">Google connected</span>
            <form action="<?= site_url('google-calendar/import') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="month" value="<?= esc($calendarMonth['key']) ?>">
                <button class="ghost-button" type="submit">Ambil dari Google</button>
            </form>
        <?php else: ?>
            <a class="primary-button" href="<?= site_url('google-calendar/connect') ?>">Connect Google Calendar</a>
        <?php endif; ?>
        <?php if (($canDownloadGoogleCalendar ?? false) === true): ?>
            <form action="<?= site_url('google-calendar/export') ?>" method="get" class="inline-form calendar-export-form" data-calendar-export-form>
                <input class="control compact calendar-month-input" type="month" name="start_month" value="<?= esc($calendarMonth['key']) ?>" aria-label="Bulan awal export" required>
                <span class="calendar-range-separator">s/d</span>
                <input class="control compact calendar-month-input" type="month" name="end_month" value="<?= esc($calendarMonth['key']) ?>" aria-label="Bulan akhir export" required>
                <button class="ghost-button" type="submit">Download Excel</button>
                <button class="ghost-button" type="submit" name="category_scope" value="without_academic_non_academic">Download Tanpa Akademik/Non Akademik</button>
            </form>
        <?php endif; ?>
        <?php if (($googleCalendar['connected'] ?? false) === true): ?>
            <form action="<?= site_url('google-calendar/sync') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="month" value="<?= esc($calendarMonth['key']) ?>">
                <button class="primary-button" type="submit">Sync Bulan Terpilih</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (($googleCalendarConfigured ?? false) === false): ?>
    <div class="alert error">
        Google Calendar belum dikonfigurasi. Isi credential di .env, lalu daftarkan redirect URI: <?= esc(site_url('google-calendar/callback')) ?>.
    </div>
<?php endif; ?>

<section class="calendar-layout">
    <div class="panel calendar-panel">
        <div class="calendar-legend">
            <span><i class="legend-ubsi"></i> Event Kampus UBSI</span>
            <span><i class="legend-unm"></i> Event Kampus Universitas Nusa Mandiri</span>
            <span><i class="legend-cu"></i> Event Kampus Cyber University</span>
            <span><i class="legend-google"></i> Event Google lainnya</span>
        </div>
        <p class="calendar-scroll-hint">Geser kalender ke samping untuk melihat tanggal lainnya.</p>
        <div class="calendar-scroll" role="region" aria-label="Kalender <?= esc($calendarMonth['label']) ?>" tabindex="0">
            <div class="calendar-grid">
                <?php foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day): ?>
                    <strong class="calendar-head"><?= esc($day) ?></strong>
                <?php endforeach; ?>
                <?php foreach ($calendarMonth['cells'] as $cell): ?>
                    <?php
                    $firstLocalEvent = $cell['localEvents'][0] ?? null;
                    $detailUrl = $firstLocalEvent ? site_url('events/' . $firstLocalEvent['id']) : '';
                    ?>
                    <div
                        class="calendar-day <?= ($cell['localEvents'] || $cell['googleEvents']) ? 'has-event' : '' ?> <?= $cell['day'] === null ? 'is-empty' : '' ?>"
                        data-calendar-day
                        <?= $detailUrl !== '' ? 'data-calendar-detail-url="' . esc($detailUrl, 'attr') . '"' : '' ?>
                    >
                        <?php if ($cell['day'] !== null): ?>
                            <span class="calendar-date-number"><?= esc($cell['day']) ?></span>
                            <?php foreach ($cell['localEvents'] as $event): ?>
                                <a
                                    class="calendar-event local campus-<?= esc($event['calendar_campus'] ?? 'other') ?>"
                                    href="<?= site_url('events/' . $event['id']) ?>"
                                    data-calendar-event-link
                                    aria-label="Buka detail event <?= esc($event['title']) ?>"
                                    title="Buka detail <?= esc($event['title']) ?>"
                                ><?= esc($event['title']) ?></a>
                            <?php endforeach; ?>
                            <?php foreach ($cell['googleEvents'] as $event): ?>
                                <small class="calendar-event google campus-<?= esc($event['calendar_campus'] ?? 'other') ?>" title="<?= esc($event['summary']) ?>"><?= esc($event['summary']) ?></small>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
