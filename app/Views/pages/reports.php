<div class="toolbar">
    <div>
        <span class="eyebrow">Post event</span>
        <h2>Data laporan event terlaksana</h2>
    </div>
    <?php if ($isAdminSystem ?? false): ?>
        <span class="status status-aktif">Mode monitoring admin</span>
    <?php else: ?>
        <span class="status">Mode lihat</span>
    <?php endif; ?>
</div>

<section class="panel">
    <?php
        $pagination = $reportPagination ?? [
            'current' => 1,
            'perPage' => 15,
            'total' => 0,
            'totalPages' => 1,
            'from' => 0,
            'to' => 0,
            'query' => [],
        ];
        $reportPageUrl = static function (int $page) use ($pagination): string {
            $query = $pagination['query'] ?? [];
            $query['page'] = $page;

            return site_url('reports?' . http_build_query($query));
        };
        $crmPagination = $crmPerformancePagination ?? [
            'current' => 1,
            'perPage' => 5,
            'total' => 0,
            'totalPages' => 1,
            'from' => 0,
            'to' => 0,
            'rankStart' => 1,
            'query' => [],
        ];
        $crmPageUrl = static function (int $page) use ($crmPagination): string {
            $query = $crmPagination['query'] ?? [];
            $query['crm_page'] = $page;

            return site_url('reports?' . http_build_query($query));
        };
        $exportQuery = array_filter([
            'campus' => $selectedReportCampus ?? '',
            'month' => $selectedReportMonth ?? '',
            'sort' => $selectedReportSort ?? '',
            'category_scope' => $selectedReportCategoryScope ?? '',
        ], static fn (string $value): bool => $value !== '');
        $filteredExportUrl = site_url('reports/export' . ($exportQuery !== [] ? '?' . http_build_query($exportQuery) : ''));
        $crmExportQuery = array_filter([
            'campus' => $selectedReportCampus ?? '',
        ], static fn (string $value): bool => $value !== '');
        $filteredCrmExportUrl = site_url('reports/crm-export' . ($crmExportQuery !== [] ? '?' . http_build_query($crmExportQuery) : ''));
    ?>
    <form class="report-filter-bar" action="<?= site_url('reports') ?>" method="get">
        <label>
            Kampus
            <select class="control" name="campus">
                <option value="">Semua kampus</option>
                <?php foreach (($reportCampusOptions ?? []) as $campus): ?>
                    <option value="<?= esc($campus) ?>" <?= ($selectedReportCampus ?? '') === $campus ? 'selected' : '' ?>><?= esc($campus) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Bulan pelaksanaan
            <input class="control" type="month" name="month" value="<?= esc($selectedReportMonth ?? '') ?>">
        </label>
        <label>
            Urutkan
            <select class="control" name="sort">
                <option value="month_desc" <?= ($selectedReportSort ?? '') === 'month_desc' ? 'selected' : '' ?>>Bulan terbaru</option>
                <option value="month_asc" <?= ($selectedReportSort ?? '') === 'month_asc' ? 'selected' : '' ?>>Bulan terlama</option>
                <option value="campus_asc" <?= ($selectedReportSort ?? '') === 'campus_asc' ? 'selected' : '' ?>>Nama kampus A-Z</option>
                <option value="campus_desc" <?= ($selectedReportSort ?? '') === 'campus_desc' ? 'selected' : '' ?>>Nama kampus Z-A</option>
            </select>
        </label>
        <div class="report-filter-actions">
            <button class="primary-button compact-button" type="submit">Terapkan</button>
            <a class="ghost-button compact-button" href="<?= esc($filteredExportUrl) ?>">Download Excel Filter</a>
            <a class="ghost-button compact-button" href="<?= site_url('reports/export') ?>">Download Semua</a>
            <a class="ghost-button compact-button" href="<?= esc($filteredCrmExportUrl) ?>">Download Hasil CRM</a>
            <a class="ghost-button compact-button" href="<?= site_url('reports') ?>">Reset</a>
        </div>
    </form>

    <section class="crm-report-panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">CRM Event</span>
                <h2>Keberhasilan follow up</h2>
            </div>
            <span class="status">Urutan skor terbaik</span>
        </div>
        <div class="report-table-summary">
            <span><?= esc($crmPagination['from']) ?>-<?= esc($crmPagination['to']) ?> dari <?= esc($crmPagination['total']) ?> performa CRM</span>
            <span>Label Closing menjadi nilai tertinggi</span>
        </div>
        <div class="crm-report-grid">
            <?php foreach (($crmPerformance ?? []) as $index => $item): ?>
                <article class="crm-report-card tone-<?= esc($item['tone']) ?>">
                    <div class="crm-report-score">
                        <strong><?= esc((string) $item['score']) ?>%</strong>
                        <span>Keberhasilan</span>
                    </div>
                    <div class="crm-report-main">
                        <a class="table-title" href="<?= site_url('crm?event_id=' . urlencode((string) $item['event_id'])) ?>"><?= esc($item['event_code']) ?> - <?= esc($item['title']) ?></a>
                        <small><?= esc($item['campus']) ?> - <?= esc($item['date_label'] ?? (! empty($item['date']) ? date('d M Y', strtotime($item['date'])) : '-')) ?></small>
                        <div class="crm-report-badges">
                            <span class="crm-badge crm-badge-green">Closing <?= esc((string) $item['closing']) ?></span>
                            <span class="crm-badge crm-badge-yellow">Tahap 2 respon <?= esc((string) $item['stage2_responded']) ?></span>
                            <span class="crm-badge crm-badge-red">Tahap 2 tidak respon <?= esc((string) $item['stage2_no_response']) ?></span>
                            <span class="crm-badge crm-badge-red">Menolak <?= esc((string) $item['rejected']) ?></span>
                            <span class="crm-badge">Fresh <?= esc((string) $item['fresh']) ?></span>
                        </div>
                    </div>
                    <div class="crm-report-rank">
                        <span class="status status-calendar">#<?= esc((string) ((int) ($crmPagination['rankStart'] ?? 1) + $index)) ?></span>
                        <span class="status"><?= esc((string) $item['total']) ?> data CRM</span>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (($crmPerformance ?? []) === []): ?>
                <div class="notification-item">
                    <strong>Belum ada performa CRM</strong>
                    <p>Upload data CRM dari event untuk melihat persentase keberhasilan follow up di sini.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php if (($crmPagination['totalPages'] ?? 1) > 1): ?>
            <nav class="pagination-bar" aria-label="Navigasi halaman keberhasilan CRM">
                <a
                    class="ghost-button compact-button <?= ($crmPagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                    href="<?= ($crmPagination['current'] ?? 1) <= 1 ? '#' : esc($crmPageUrl((int) $crmPagination['current'] - 1)) ?>"
                >Sebelumnya</a>
                <div class="pagination-pages">
                    <?php for ($page = 1; $page <= (int) $crmPagination['totalPages']; $page++): ?>
                        <?php if ($page === 1 || $page === (int) $crmPagination['totalPages'] || abs($page - (int) $crmPagination['current']) <= 2): ?>
                            <a
                                class="ghost-button compact-button <?= $page === (int) $crmPagination['current'] ? 'is-active' : '' ?>"
                                href="<?= esc($crmPageUrl($page)) ?>"
                            ><?= esc($page) ?></a>
                        <?php elseif (abs($page - (int) $crmPagination['current']) === 3): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <a
                    class="ghost-button compact-button <?= ($crmPagination['current'] ?? 1) >= ($crmPagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                    href="<?= ($crmPagination['current'] ?? 1) >= ($crmPagination['totalPages'] ?? 1) ? '#' : esc($crmPageUrl((int) $crmPagination['current'] + 1)) ?>"
                >Berikutnya</a>
            </nav>
        <?php endif; ?>
        <div class="crm-report-log-section">
            <div class="panel-header compact-header">
                <div>
                    <span class="eyebrow">Log CRM</span>
                    <h2>Perubahan data terbaru</h2>
                </div>
                <span class="status"><?= esc((string) count($crmReportLogs ?? [])) ?> log</span>
            </div>
            <div class="table-wrap">
                <table class="data-table compact-admin-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Event</th>
                            <th>Siswa</th>
                            <th>Label</th>
                            <th>Log</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($crmReportLogs ?? []) as $log): ?>
                            <tr>
                                <td><?= esc(! empty($log['created_at']) ? date('d M Y, H.i', strtotime($log['created_at'])) : '-') ?></td>
                                <td>
                                    <strong><?= esc($log['event_code'] ?: '-') ?></strong>
                                    <small class="report-link-meta"><?= esc($log['campus_name'] ?: '-') ?></small>
                                </td>
                                <td><?= esc($log['student_name'] ?: '-') ?></td>
                                <td><?= esc($log['new_label'] ?: '-') ?></td>
                                <td>
                                    <?= esc($log['summary'] ?: 'Data CRM diperbarui.') ?>
                                    <?php if (! empty($log['new_rejection_reason'])): ?>
                                        <small class="report-link-meta">Alasan: <?= esc($log['new_rejection_reason']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($log['actor_name'] ?: 'User CRM') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (($crmReportLogs ?? []) === []): ?>
                            <tr>
                                <td colspan="6"><span class="muted-note">Belum ada log perubahan CRM sesuai filter.</span></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="report-table-summary">
        <span><?= esc($pagination['from']) ?>-<?= esc($pagination['to']) ?> dari <?= esc($pagination['total']) ?> laporan event terlaksana</span>
        <span>Data mengikuti event yang sudah berstatus selesai</span>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Report</th>
                    <th>Event</th>
                    <th>Kategori Event</th>
                    <th>Kampus</th>
                    <th>Tanggal Pelaksanaan</th>
                    <th>Status realisasi</th>
                    <th>Skor</th>
                    <th>Catatan</th>
                    <th>Laporan</th>
                    <th>Proposal</th>
                    <th>Link Dokumentasi Foto</th>
                    <th>Link Dokumentasi Video</th>
                    <th>Data CRM</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <?php
                    $event = $reportEventMap[$report['event_id']] ?? null;
                    $eventTitle = $event['title'] ?? $report['event_id'];
                    $eventCategory = $event['category'] ?? '-';
                    $eventCampus = $event['campus'] ?? '-';
                    $eventDate = $event['date_label'] ?? (! empty($event['date']) ? date('d M Y', strtotime($event['date'])) : '-');
                    $eventDbId = (int) ($event['db_id'] ?? 0);
                    $crmScore = $reportCrmScores[$report['event_id']] ?? ['score' => 0, 'total' => 0, 'tone' => 'red'];
                    $crmScoreClass = match ($crmScore['tone'] ?? 'red') {
                        'green' => 'status-aktif',
                        'amber' => 'status-diajukan',
                        default => 'status-ditolak',
                    };
                    $checklistLinks = $reportChecklistLinks[$report['id']] ?? [];
                    $linkColumns = [
                        'laporan' => 'Buka laporan',
                        'proposal' => 'Buka proposal',
                        'foto' => 'Buka foto',
                        'video' => 'Buka video',
                    ];
                    ?>
                    <tr>
                        <td><strong><?= esc($report['id']) ?></strong></td>
                        <td><?= esc($eventTitle) ?></td>
                        <td><?= esc($eventCategory) ?></td>
                        <td><?= esc($eventCampus) ?></td>
                        <td><?= esc($eventDate) ?></td>
                        <td><?= esc($report['realization_status']) ?></td>
                        <td>
                            <span class="status <?= esc($crmScoreClass) ?>"><?= esc((string) ($crmScore['score'] ?? 0)) ?>%</span>
                            <small class="report-link-meta"><?= esc((string) ($crmScore['total'] ?? 0)) ?> data CRM</small>
                        </td>
                        <td><?= esc($report['notes'] !== '' ? $report['notes'] : '-') ?></td>
                        <?php foreach ($linkColumns as $key => $label): ?>
                            <?php $linkItem = $checklistLinks[$key] ?? null; ?>
                            <td>
                                <?php if (! empty($linkItem['link'])): ?>
                                    <?php
                                    $rawLink = (string) $linkItem['link'];
                                    $rawLinkIsExternal = preg_match('/^https?:\/\//i', $rawLink) === 1;
                                    $documentType = $key === 'laporan' ? 'report' : ($key === 'proposal' ? 'proposal' : null);
                                    $href = $documentType !== null && ! $rawLinkIsExternal && $event !== null
                                        ? site_url('events/' . ($event['id'] ?? '') . '/documents/' . $documentType . '/file')
                                        : $rawLink;
                                    ?>
                                    <a class="text-link" href="<?= esc($href) ?>" target="_blank" rel="noopener"><?= esc($label) ?></a>
                                    <small class="report-link-meta"><?= esc($linkItem['status'] ?? '') ?></small>
                                <?php else: ?>
                                    <span class="muted-note">Belum ada</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <?php if ($eventDbId > 0): ?>
                                <a class="primary-button compact-button" href="<?= site_url('crm?event_id=' . urlencode((string) $eventDbId)) ?>">Lihat Data CRM</a>
                            <?php else: ?>
                                <span class="muted-note">Tidak tersedia</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (($reports ?? []) === []): ?>
                    <tr>
                        <td colspan="13">
                            <span class="muted-note">Belum ada laporan dari event yang sudah terlaksana sesuai filter.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <nav class="pagination-bar" aria-label="Navigasi halaman laporan">
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) <= 1 ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) <= 1 ? '#' : esc($reportPageUrl((int) $pagination['current'] - 1)) ?>"
            >Sebelumnya</a>
            <div class="pagination-pages">
                <?php for ($page = 1; $page <= (int) $pagination['totalPages']; $page++): ?>
                    <?php if ($page === 1 || $page === (int) $pagination['totalPages'] || abs($page - (int) $pagination['current']) <= 2): ?>
                        <a
                            class="ghost-button compact-button <?= $page === (int) $pagination['current'] ? 'is-active' : '' ?>"
                            href="<?= esc($reportPageUrl($page)) ?>"
                        ><?= esc($page) ?></a>
                    <?php elseif (abs($page - (int) $pagination['current']) === 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <a
                class="ghost-button compact-button <?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? 'is-disabled' : '' ?>"
                href="<?= ($pagination['current'] ?? 1) >= ($pagination['totalPages'] ?? 1) ? '#' : esc($reportPageUrl((int) $pagination['current'] + 1)) ?>"
            >Berikutnya</a>
        </nav>
    <?php endif; ?>
</section>
