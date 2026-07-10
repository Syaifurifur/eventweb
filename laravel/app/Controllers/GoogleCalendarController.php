<?php

namespace App\Controllers;

use App\Libraries\CalendarExcelExporter;
use App\Libraries\GoogleCalendarService;
use App\Models\CalendarIntegrationModel;
use App\Models\EventModel;
use App\Models\GoogleCalendarEventModel;
use App\Models\NotificationModel;

class GoogleCalendarController extends BaseController
{
    private GoogleCalendarService $google;

    public function __construct()
    {
        parent::__construct();
        $this->google = new GoogleCalendarService();
    }

    public function connect()
    {
        if (! $this->google->isConfigured()) {
            return redirect()
                ->to(site_url('calendar'))
                ->with('error', 'Google Calendar belum dikonfigurasi. Isi GOOGLE_CALENDAR_CLIENT_ID dan GOOGLE_CALENDAR_CLIENT_SECRET di .env.');
        }

        return redirect()->to($this->google->authorizationUrl((int) session('user.id')));
    }

    public function callback()
    {
        $state = (string) $this->request->getGet('state');
        $expectedState = (string) session('google_oauth_state');
        $code = (string) $this->request->getGet('code');

        if ($state === '' || ! hash_equals($expectedState, $state)) {
            return redirect()->to(site_url('calendar'))->with('error', 'State OAuth Google tidak valid.');
        }

        if ($code === '') {
            $error = (string) $this->request->getGet('error') ?: 'Google tidak mengirim authorization code.';

            return redirect()->to(site_url('calendar'))->with('error', $error);
        }

        try {
            $this->google->handleCallback((int) session('user.id'), $code);
            session()->forget(['google_oauth_state', 'google_oauth_user_id']);

            return redirect()->to(site_url('calendar'))->with('success', 'Google Calendar berhasil terhubung.');
        } catch (\Throwable $error) {
            return redirect()->to(site_url('calendar'))->with('error', 'Gagal menghubungkan Google Calendar: ' . $error->getMessage());
        }
    }

    public function syncEvent(string $code)
    {
        $eventModel = new EventModel();
        $event = $eventModel->where('code', $code)->first();

        if (! $event) {
            return redirect()->back()->with('error', 'Event tidak ditemukan.');
        }

        if (! in_array($event['status'], ['disetujui', 'selesai'], true)) {
            return redirect()->back()->with('error', 'Hanya event yang sudah disetujui atau selesai yang bisa disinkronkan.');
        }

        try {
            $calendarIntegration = $this->calendarIntegrationForEvent((int) $event['id']);
            $googleEvent = $this->google->syncEvent($event, (int) session('user.id'), $calendarIntegration['external_event_id'] ?? null);
            $this->upsertCalendarIntegration((int) $event['id'], $googleEvent['id'] ?? null, $googleEvent['htmlLink'] ?? null);

            (new NotificationModel())->insert([
                'user_id' => (int) $event['created_by'],
                'type' => 'calendar',
                'message' => $event['title'] . ' berhasil disinkronkan ke Google Calendar.',
            ]);

            return redirect()->back()->with('success', 'Event berhasil disinkronkan ke Google Calendar.');
        } catch (\Throwable $error) {
            $this->markCalendarFailed((int) $event['id']);

            return redirect()->back()->with('error', 'Sync Google Calendar gagal: ' . $error->getMessage());
        }
    }

    public function syncApproved()
    {
        $month = $this->selectedCalendarMonth();
        $events = $this->approvedEventsForMonth($month);

        $synced = 0;
        $failed = 0;

        foreach ($events as $event) {
            try {
                $calendarIntegration = $this->calendarIntegrationForEvent((int) $event['id']);
                $googleEvent = $this->google->syncEvent($event, (int) session('user.id'), $calendarIntegration['external_event_id'] ?? null);
                $this->upsertCalendarIntegration((int) $event['id'], $googleEvent['id'] ?? null, $googleEvent['htmlLink'] ?? null);
                $synced++;
            } catch (\Throwable $error) {
                $this->markCalendarFailed((int) $event['id']);
                $failed++;
            }
        }

        try {
            $imported = $this->importGoogleEventsForMonth($month);
        } catch (\Throwable $error) {
            $imported = 0;
            $failed++;
        }

        if ($failed > 0) {
            return redirect()->to(site_url('calendar?month=' . $month))->with('error', "Sync selesai sebagian: {$synced} event lokal berhasil, data Google Calendar diperbarui, {$failed} proses gagal.");
        }

        return redirect()->to(site_url('calendar?month=' . $month))->with('success', "{$synced} event lokal terkirim dan data Google Calendar diperbarui.");
    }

    public function importMonth()
    {
        $month = $this->selectedCalendarMonth();

        try {
            $imported = $this->importGoogleEventsForMonth($month);

            return redirect()->to(site_url('calendar?month=' . $month))->with('success', "{$imported} data event Google Calendar berhasil disinkronkan.");
        } catch (\Throwable $error) {
            return redirect()->to(site_url('calendar?month=' . $month))->with('error', 'Gagal mengambil event Google Calendar: ' . $error->getMessage());
        }
    }

    public function exportMonth()
    {
        try {
            $range = $this->selectedCalendarRange();
            $categoryScope = $this->selectedCalendarCategoryScope();
            $userId = (int) session('user.id');
            $exportUserId = session('user.role') === 'Admin Sistem'
                ? $this->google->adminDownloadTokenUserId($userId)
                : $userId;

            if (! $this->google->isConfigured()) {
                return redirect()->to(site_url('calendar?month=' . $range['start']))->with('error', 'Google Calendar belum dikonfigurasi.');
            }

            if ($exportUserId === null) {
                return redirect()->to(site_url('calendar?month=' . $range['start']))->with('error', 'Belum ada koneksi Google Calendar dari Admin Sistem aktif.');
            }

            $eventsByMonth = [];
            $model = new GoogleCalendarEventModel();
            foreach ($range['months'] as $month) {
                $this->importGoogleEventsForMonth($month, $exportUserId);
                $eventsByMonth[$month] = $this->groupGoogleEventsForCampusExport(
                    $model->eventsForMonth($exportUserId, $month),
                    $categoryScope
                );
            }

            $export = (new CalendarExcelExporter())->exportRange($eventsByMonth, $range['start'], $range['end']);
            $filename = $this->calendarExportFilename($export['filename'], $categoryScope);

            return $this->response
                ->setHeader('Content-Type', $export['mime'])
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'max-age=0')
                ->setBody($export['content']);
        } catch (\Throwable $error) {
            $month = $this->selectedCalendarMonth();

            return redirect()->to(site_url('calendar?month=' . $month))->with('error', 'Gagal download Excel Google Calendar: ' . $error->getMessage());
        }
    }

    private function upsertCalendarIntegration(int $eventId, ?string $externalId, ?string $htmlLink): void
    {
        $model = new CalendarIntegrationModel();
        $existing = $this->calendarIntegrationForEvent($eventId);
        if ($existing && $externalId === null) {
            $externalId = $existing['external_event_id'] ?? null;
        }
        if ($existing && $htmlLink === null) {
            $htmlLink = $existing['external_link'] ?? null;
        }

        $data = [
            'event_id' => $eventId,
            'provider' => 'google_calendar',
            'external_event_id' => $externalId,
            'external_link' => $htmlLink,
            'sync_status' => 'synced',
            'last_synced_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $model->update($existing['id'], $data);
            $this->deleteDuplicateCalendarIntegrations($eventId, (int) $existing['id']);
            return;
        }

        $insertId = (int) $model->insert($data, true);
        $this->deleteDuplicateCalendarIntegrations($eventId, $insertId);
    }

    private function deleteDuplicateCalendarIntegrations(int $eventId, int $keepId): void
    {
        (new CalendarIntegrationModel())
            ->where('event_id', $eventId)
            ->where('provider', 'google_calendar')
            ->where('id !=', $keepId)
            ->delete();
    }

    private function calendarIntegrationForEvent(int $eventId): ?array
    {
        return (new CalendarIntegrationModel())
            ->where('event_id', $eventId)
            ->where('provider', 'google_calendar')
            ->first();
    }

    private function markCalendarFailed(int $eventId): void
    {
        $model = new CalendarIntegrationModel();
        $existing = $this->calendarIntegrationForEvent($eventId);
        $data = [
            'event_id' => $eventId,
            'provider' => 'google_calendar',
            'sync_status' => 'failed',
        ];

        if ($existing) {
            $model->update($existing['id'], $data);
            return;
        }

        $model->insert($data);
    }

    private function importGoogleEventsForMonth(string $month, ?int $googleUserId = null): int
    {
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $start = new \DateTimeImmutable($month . '-01 00:00:00', $timezone);
        $end = $start->modify('first day of next month');
        $userId = $googleUserId ?? (int) session('user.id');
        $items = $this->google->listEvents($userId, $start, $end);
        $model = new GoogleCalendarEventModel();
        $seenGoogleEventIds = [];
        $count = 0;

        foreach ($items as $item) {
            if (empty($item['id'])) {
                continue;
            }

            $seenGoogleEventIds[] = (string) $item['id'];
            $startValue = $item['start']['dateTime'] ?? (($item['start']['date'] ?? null) . ' 00:00:00');
            $endValue = $item['end']['dateTime'] ?? (($item['end']['date'] ?? null) . ' 00:00:00');
            $startAt = new \DateTimeImmutable($startValue, $timezone);
            $endAt = new \DateTimeImmutable($endValue, $timezone);
            $existing = $model
                ->where('user_id', $userId)
                ->where('google_event_id', $item['id'])
                ->first();
            $data = [
                'user_id' => $userId,
                'google_event_id' => $item['id'],
                'summary' => $item['summary'] ?? '(Tanpa judul)',
                'description' => $item['description'] ?? null,
                'location' => $item['location'] ?? null,
                'start_at' => $startAt->format('Y-m-d H:i:s'),
                'end_at' => $endAt->format('Y-m-d H:i:s'),
                'is_all_day' => isset($item['start']['date']) ? 1 : 0,
                'html_link' => $item['htmlLink'] ?? null,
                'status' => $item['status'] ?? 'confirmed',
                'raw_payload' => json_encode($item, JSON_UNESCAPED_SLASHES),
            ];

            if ($existing) {
                $model->update($existing['id'], $data);
            } else {
                $model->insert($data);
            }

            $linkedEvent = $this->linkedEventFromGoogleDescription((string) ($item['description'] ?? ''));
            if ($linkedEvent !== null) {
                $this->upsertCalendarIntegration((int) $linkedEvent['id'], (string) $item['id'], $item['htmlLink'] ?? null);
            }

            $count++;
        }

        $model->deleteMissingForRange($userId, $start, $end, $seenGoogleEventIds);

        return $count;
    }

    private function approvedEventsForMonth(string $month): array
    {
        $range = $this->calendarMonthRange($month);

        return (new EventModel())
            ->whereIn('status', ['disetujui', 'selesai'])
            ->where('event_date <=', $range['end'])
            ->groupStart()
                ->where('event_end_date >=', $range['start'])
                ->orGroupStart()
                    ->where('event_end_date', null)
                    ->where('event_date >=', $range['start'])
                ->groupEnd()
                ->orGroupStart()
                    ->where('event_end_date', '')
                    ->where('event_date >=', $range['start'])
                ->groupEnd()
            ->groupEnd()
            ->orderBy('event_date', 'ASC')
            ->findAll();
    }

    private function calendarMonthRange(string $month): array
    {
        $start = new \DateTimeImmutable($month . '-01');

        return [
            'start' => $start->format('Y-m-d'),
            'end' => $start->modify('last day of this month')->format('Y-m-d'),
        ];
    }

    private function groupGoogleEventsForCampusExport(array $events, string $categoryScope = ''): array
    {
        $grouped = [
            'UBSI' => [],
            'UNM' => [],
            'CU' => [],
        ];

        foreach ($events as $event) {
            $event = $this->googleEventWithLinkedCampus($event);
            if (! $this->calendarCategoryScopeMatches($event, $categoryScope)) {
                continue;
            }

            foreach ($this->campusesForGoogleEventExport($event) as $campus) {
                $grouped[$campus][] = $event;
            }
        }

        return $grouped;
    }

    private function googleEventWithLinkedCampus(array $event): array
    {
        $linkedEvent = $this->linkedEventFromGoogleDescription((string) ($event['description'] ?? ''));
        if ($linkedEvent !== null && ! empty($linkedEvent['campus_name'])) {
            $event['campus_name'] = $linkedEvent['campus_name'];
        }
        if ($linkedEvent !== null && ! empty($linkedEvent['category'])) {
            $event['category'] = $linkedEvent['category'];
        }

        return $event;
    }

    private function calendarCategoryScopeMatches(array $event, string $scope): bool
    {
        if ($scope === '') {
            return true;
        }

        $isNonAcademic = $this->isNonAcademicCalendarEvent($event);
        $isAcademic = ! $isNonAcademic && $this->isAcademicCalendarEvent($event);

        return match ($scope) {
            'academic' => $isAcademic,
            'non_academic' => $isNonAcademic,
            'without_academic_non_academic' => ! $isAcademic && ! $isNonAcademic,
            default => true,
        };
    }

    private function isAcademicCalendarEvent(array $event): bool
    {
        $categoryKey = $this->normalizedCalendarCategoryKey((string) ($event['category'] ?? ''));
        if ($categoryKey !== '') {
            return in_array($categoryKey, ['akademik', 'academic'], true);
        }

        return preg_match('/(^|[^a-z0-9])(akademik|academic)([^a-z0-9]|$)/i', $this->calendarCategorySearchText($event)) === 1;
    }

    private function isNonAcademicCalendarEvent(array $event): bool
    {
        $categoryKey = $this->normalizedCalendarCategoryKey((string) ($event['category'] ?? ''));
        if ($categoryKey !== '') {
            return in_array($categoryKey, ['nonakademik', 'nonacademic'], true);
        }

        return preg_match('/(^|[^a-z0-9])non[\s\-_]*(akademik|academic)([^a-z0-9]|$)/i', $this->calendarCategorySearchText($event)) === 1;
    }

    private function calendarCategorySearchText(array $event): string
    {
        return implode(' ', [
            (string) ($event['summary'] ?? ''),
            (string) ($event['location'] ?? ''),
            (string) ($event['description'] ?? ''),
        ]);
    }

    private function normalizedCalendarCategoryKey(string $category): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $category));
    }

    private function campusesForGoogleEventExport(array $event): array
    {
        $text = implode(' ', [
            $event['summary'] ?? '',
            $event['location'] ?? '',
            $event['description'] ?? '',
        ]);

        $linkedEvent = $this->linkedEventFromGoogleDescription((string) ($event['description'] ?? ''));
        if ($linkedEvent !== null) {
            $text .= ' ' . implode(' ', [
                $linkedEvent['title'] ?? '',
                $linkedEvent['category'] ?? '',
                $linkedEvent['campus_name'] ?? '',
                $linkedEvent['description'] ?? '',
            ]);
        }

        $aliases = [
            'UBSI' => ['UBSI', 'BSI', 'BINA SARANA INFORMATIKA'],
            'UNM' => ['UNM', 'NUSA MANDIRI'],
            'CU' => ['CU', 'CYBER UNIVERSITY', 'CYBER UNIV', 'CYBER'],
        ];

        $matched = [];
        foreach ($aliases as $campus => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->containsCampusKeyword($text, $pattern)) {
                    $matched[] = $campus;
                    break;
                }
            }
        }

        return array_values(array_unique($matched ?: ['UBSI']));
    }

    private function linkedEventFromGoogleDescription(string $description): ?array
    {
        if (preg_match('/EventHub code:\s*([A-Z]+-\d+)/i', $description, $matches) !== 1) {
            return null;
        }

        return (new EventModel())->findByCode(strtoupper($matches[1]));
    }

    private function containsCampusKeyword(string $text, string $keyword): bool
    {
        $pattern = '/(^|[^A-Z0-9])' . preg_quote($keyword, '/') . '([^A-Z0-9]|$)/i';

        return preg_match($pattern, $text) === 1;
    }

    private function selectedCalendarRange(): array
    {
        $start = (string) (
            $this->request->getGet('start_month')
            ?: $this->request->getGet('month_start')
            ?: $this->request->getGet('month')
            ?: date('Y-m')
        );
        $end = (string) (
            $this->request->getGet('end_month')
            ?: $this->request->getGet('month_end')
            ?: $start
        );

        if (preg_match('/^\d{4}-\d{2}$/', $start) !== 1 || preg_match('/^\d{4}-\d{2}$/', $end) !== 1) {
            throw new \InvalidArgumentException('Range bulan tidak valid.');
        }

        $startDate = new \DateTimeImmutable($start . '-01');
        $endDate = new \DateTimeImmutable($end . '-01');
        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('Bulan akhir tidak boleh lebih awal dari bulan awal.');
        }

        $months = [];
        $cursor = $startDate;
        while ($cursor <= $endDate) {
            $months[] = $cursor->format('Y-m');
            $cursor = $cursor->modify('first day of next month');
        }

        if (count($months) > 12) {
            throw new \InvalidArgumentException('Range download maksimal 12 bulan.');
        }

        return [
            'start' => $startDate->format('Y-m'),
            'end' => $endDate->format('Y-m'),
            'months' => $months,
        ];
    }

    private function selectedCalendarCategoryScope(): string
    {
        $scope = trim((string) $this->request->getGet('category_scope'));
        $allowed = ['academic', 'non_academic', 'without_academic_non_academic'];

        return in_array($scope, $allowed, true) ? $scope : '';
    }

    private function calendarExportFilename(string $filename, string $categoryScope): string
    {
        $suffix = match ($categoryScope) {
            'academic' => '-kategori-akademik',
            'non_academic' => '-kategori-non-akademik',
            'without_academic_non_academic' => '-tanpa-kategori-akademik-non-akademik',
            default => '',
        };

        if ($suffix === '') {
            return $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $base = $extension !== '' ? substr($filename, 0, -strlen('.' . $extension)) : $filename;

        return $base . $suffix . ($extension !== '' ? '.' . $extension : '');
    }

    private function selectedCalendarMonth(): string
    {
        $month = (string) ($this->request->getPost('month') ?: $this->request->getGet('month'));

        if (preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return $month;
        }

        return date('Y-m');
    }
}
