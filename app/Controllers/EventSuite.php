<?php

namespace App\Controllers;

use App\Models\CalendarIntegrationModel;
use App\Libraries\GoogleCalendarService;
use App\Libraries\GeminiDocumentService;
use App\Models\CampusModel;
use App\Models\CrmLeadModel;
use App\Models\CrmUploadBatchModel;
use App\Models\CrmUserModel;
use App\Models\EventCategoryModel;
use App\Models\EventChecklistItemModel;
use App\Models\EventChecklistTemplateModel;
use App\Models\EventModel;
use App\Models\EventQuestionnaireModel;
use App\Models\EventQuestionnaireResponseModel;
use App\Models\GoogleCalendarEventModel;
use App\Models\NotificationModel;
use App\Models\OneCampusBannerReportModel;
use App\Models\OneCampusOjtReportModel;
use App\Models\OneCampusSchoolReportModel;
use App\Models\ProposalModel;
use App\Models\ReportModel;
use App\Models\RoleModel;
use App\Models\UserModel;

class EventSuite extends BaseController
{
    private array $eventCodeCache = [];

    public function dashboard(): string
    {
        $data = $this->applicationData();
        $newPicTaskCount = (new NotificationModel())
            ->where('user_id', (int) session('user.id'))
            ->where('type', 'checklist')
            ->where('read_at', null)
            ->countAllResults();
        $dmerDashboard = $this->buildDmerDashboardData();

        return $this->render('pages/dashboard', array_merge([
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'stats' => $data['stats'],
            'events' => $data['events'],
            'activities' => $data['activities'],
            'notifications' => $data['notifications'],
            'newPicTaskCount' => $newPicTaskCount,
            'isAdminSystem' => session('user.role') === 'Admin Sistem',
        ], $dmerDashboard));
    }

    private function buildDmerDashboardData(): array
    {
        $allCampuses = (new CampusModel())->orderBy('name', 'ASC')->findAll();
        $rawEventsAll = (new EventModel())
            ->withCreator()
            ->orderBy('event_date', 'ASC')
            ->findAll();
        $rawEvents = array_values(array_filter($rawEventsAll, static function (array $ev): bool {
            return strtolower((string) ($ev['status'] ?? '')) !== 'ditolak';
        }));

        $allSchoolReports = (new OneCampusSchoolReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $allOjtReports = (new OneCampusOjtReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $allBannerReports = (new OneCampusBannerReportModel())->withCampus()->orderBy('installed_at', 'DESC')->findAll();

        $availableYears = [date('Y') => true, '2026' => true, '2025' => true];
        foreach ($rawEvents as $ev) {
            $year = substr((string) ($ev['event_date'] ?? ''), 0, 4);
            if (preg_match('/^\d{4}$/', $year) === 1) {
                $availableYears[$year] = true;
            }
        }
        $yearList = array_map('strval', array_keys($availableYears));
        rsort($yearList, SORT_STRING);

        $requestedYear = trim((string) $this->request->getGet('dmer_year'));
        if ($requestedYear === '') {
            $selectedYear = in_array('2026', $yearList, true) ? '2026' : (string) ($yearList[0] ?? date('Y'));
        } elseif ($requestedYear === 'all' || preg_match('/^\d{4}$/', $requestedYear) === 1) {
            $selectedYear = $requestedYear;
        } else {
            $selectedYear = '2026';
        }

        $monthLabels = $this->shortMonthLabels();
        $fullMonthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $requestedMonth = trim((string) $this->request->getGet('dmer_month'));
        $selectedMonth = isset($monthLabels[$requestedMonth]) ? $requestedMonth : 'all';

        // Filter events for the selected period
        $filteredEvents = array_values(array_filter($rawEvents, static function (array $ev) use ($selectedYear, $selectedMonth): bool {
            $date = (string) ($ev['event_date'] ?? '');
            if ($selectedYear !== 'all' && substr($date, 0, 4) !== $selectedYear) {
                return false;
            }
            if ($selectedMonth !== 'all' && substr($date, 5, 2) !== $selectedMonth) {
                return false;
            }
            return true;
        }));

        // Filter school reports for the selected period
        $filteredSchoolReports = array_values(array_filter($allSchoolReports, static function (array $row) use ($selectedYear, $selectedMonth): bool {
            $date = (string) ($row['created_at'] ?? '');
            if ($selectedYear !== 'all' && substr($date, 0, 4) !== $selectedYear) {
                return false;
            }
            if ($selectedMonth !== 'all' && substr($date, 5, 2) !== $selectedMonth) {
                return false;
            }
            return true;
        }));

        // Filter banner reports for the selected period
        $filteredBannerReports = array_values(array_filter($allBannerReports, static function (array $row) use ($selectedYear, $selectedMonth): bool {
            $date = (string) (($row['installed_at'] ?? '') !== '' ? $row['installed_at'] : ($row['created_at'] ?? ''));
            if ($selectedYear !== 'all' && substr($date, 0, 4) !== $selectedYear) {
                return false;
            }
            if ($selectedMonth !== 'all' && substr($date, 5, 2) !== $selectedMonth) {
                return false;
            }
            return true;
        }));

        // Filter OJT reports for the selected period
        if ($selectedYear !== 'all' && $selectedMonth !== 'all') {
            $filteredOjtReports = $this->filterOjtReportsActiveInMonth($allOjtReports, $selectedYear, $selectedMonth);
        } elseif ($selectedYear !== 'all') {
            $filteredOjtReports = array_values(array_filter($allOjtReports, function (array $row) use ($selectedYear): bool {
                $startYear = substr((string) ($row['ojt_start_month'] ?? ''), 0, 4);
                $endYear = substr((string) ($row['ojt_end_month'] ?? ''), 0, 4);
                $createdYear = substr((string) ($row['created_at'] ?? ''), 0, 4);
                if ($startYear !== '' && $endYear !== '') {
                    return $startYear <= $selectedYear && $endYear >= $selectedYear;
                }
                return $createdYear === $selectedYear || str_contains((string) ($row['ojt_period'] ?? ''), $selectedYear);
            }));
        } elseif ($selectedMonth !== 'all') {
            $filteredOjtReports = array_values(array_filter($allOjtReports, function (array $row) use ($selectedMonth, $yearList): bool {
                foreach ($yearList as $yr) {
                    if ($this->isOjtReportActiveInMonth($row, $yr . '-' . $selectedMonth)) {
                        return true;
                    }
                }
                return substr((string) ($row['created_at'] ?? ''), 5, 2) === $selectedMonth;
            }));
        } else {
            $filteredOjtReports = $allOjtReports;
        }

        $eventsByCampus = [];
        foreach ($filteredEvents as $ev) {
            $eventsByCampus[(int) ($ev['campus_id'] ?? 0)][] = $ev;
        }

        $schoolCountByCampus = [];
        foreach ($filteredSchoolReports as $row) {
            $cid = (int) ($row['campus_id'] ?? 0);
            $schoolCountByCampus[$cid] = ($schoolCountByCampus[$cid] ?? 0) + 1;
        }

        $ojtCountByCampus = [];
        foreach ($filteredOjtReports as $row) {
            $cid = (int) ($row['campus_id'] ?? 0);
            $ojtCountByCampus[$cid] = ($ojtCountByCampus[$cid] ?? 0) + 1;
        }

        $bannerCountByCampus = [];
        foreach ($filteredBannerReports as $row) {
            $cid = (int) ($row['campus_id'] ?? 0);
            $bannerCountByCampus[$cid] = ($bannerCountByCampus[$cid] ?? 0) + 1;
        }

        $dmerCampuses = [];
        $index = 1;
        foreach ($allCampuses as $campus) {
            $cid = (int) ($campus['id'] ?? 0);
            if ($cid <= 0) {
                continue;
            }
            $campusName = trim((string) preg_replace('/^Kampus\s+/i', '', (string) ($campus['name'] ?? 'Tanpa Kampus')));
            $campusEvents = $eventsByCampus[$cid] ?? [];
            $octhCount = count(array_filter($campusEvents, fn (array $ev): bool => $this->isOpenhouseCategory((string) ($ev['category_name'] ?? $ev['category'] ?? ''))));
            $octeCount = count(array_filter($campusEvents, fn (array $ev): bool => $this->isOneCampusTwoEventCategory((string) ($ev['category_name'] ?? $ev['category'] ?? ''))));

            $dmerCampuses[] = [
                'no' => $index,
                'campus_id' => $cid,
                'kampus' => $campusName !== '' ? $campusName : (string) ($campus['name'] ?? 'Kampus ' . $index),
                'octh' => $octhCount,
                'octe' => $octeCount,
                'octs' => (int) ($schoolCountByCampus[$cid] ?? 0),
                'ocfo' => (int) ($ojtCountByCampus[$cid] ?? 0),
                'ocfs' => (int) ($bannerCountByCampus[$cid] ?? 0),
            ];
            $index++;
        }

        $roleName = (string) session('user.role');
        $normalizedRole = strtolower($this->normalizedRoleName($roleName));
        $canViewAllDmerTabs = in_array($normalizedRole, ['admin sistem', 'super admin', 'admin', 'dmer'], true)
            || $this->isGlobalMarkomCampus();

        $userCampusId = (int) session('user.campus_id');
        if ($userCampusId <= 0 && (int) session('crmUser.campus_id') > 0) {
            $userCampusId = (int) session('crmUser.campus_id');
        }
        if ($userCampusId <= 0 && (int) session('user.id') > 0) {
            $userRow = (new UserModel())->find((int) session('user.id'));
            if ($userRow && (int) ($userRow['campus_id'] ?? 0) > 0) {
                $userCampusId = (int) $userRow['campus_id'];
            }
        }
        if ($userCampusId <= 0 && ! $canViewAllDmerTabs) {
            foreach ($rawEvents as $ev) {
                if ((int) ($ev['created_by'] ?? 0) === (int) session('user.id') && (int) ($ev['campus_id'] ?? 0) > 0) {
                    $userCampusId = (int) $ev['campus_id'];
                    break;
                }
            }
        }

        $defaultCampusNo = $dmerCampuses[0]['no'] ?? 1;
        $userCampusName = $dmerCampuses[0]['kampus'] ?? 'Kampus';
        if ($userCampusId > 0) {
            foreach ($dmerCampuses as $row) {
                if ((int) $row['campus_id'] === $userCampusId) {
                    $defaultCampusNo = (int) $row['no'];
                    $userCampusName = (string) $row['kampus'];
                    break;
                }
            }
        }

        $requestedCampusNo = (int) $this->request->getGet('dmer_campus');
        if ($canViewAllDmerTabs && $requestedCampusNo > 0) {
            foreach ($dmerCampuses as $row) {
                if ((int) $row['no'] === $requestedCampusNo) {
                    $defaultCampusNo = $requestedCampusNo;
                    break;
                }
            }
        }

        if ($selectedYear === 'all' && $selectedMonth === 'all') {
            $periodLabel = 'Semua Periode (Akumulasi Real)';
        } elseif ($selectedYear !== 'all' && $selectedMonth === 'all') {
            $periodLabel = 'Tahun ' . $selectedYear . ' (Semua Bulan)';
        } elseif ($selectedYear === 'all' && $selectedMonth !== 'all') {
            $periodLabel = 'Bulan ' . ($fullMonthNames[$selectedMonth] ?? $selectedMonth) . ' (Semua Tahun)';
        } else {
            $periodLabel = ($fullMonthNames[$selectedMonth] ?? $selectedMonth) . ' ' . $selectedYear;
        }

        return [
            'dmerCampuses' => $dmerCampuses,
            'canViewAllDmerTabs' => $canViewAllDmerTabs,
            'dmerDefaultCampusNo' => $defaultCampusNo,
            'dmerUserCampusName' => $userCampusName,
            'dmerSelectedYear' => $selectedYear,
            'dmerSelectedMonth' => $selectedMonth,
            'dmerYearOptions' => $yearList,
            'dmerMonthOptions' => $fullMonthNames,
            'dmerPeriodLabel' => $periodLabel,
        ];
    }

    public function crmRecommendationReport(): string
    {
        $roleName = (string) session('user.role');
        $normalizedRole = strtolower($this->normalizedRoleName($roleName));
        $canViewAllCrmCampuses = in_array($normalizedRole, ['admin sistem', 'super admin', 'admin', 'dmer'], true)
            || $this->isGlobalMarkomCampus();

        $userCampusId = (int) session('user.campus_id');
        if ($userCampusId <= 0 && (int) session('crmUser.campus_id') > 0) {
            $userCampusId = (int) session('crmUser.campus_id');
        }
        if ($userCampusId <= 0 && (int) session('user.id') > 0) {
            $userRow = (new UserModel())->find((int) session('user.id'));
            if ($userRow && (int) ($userRow['campus_id'] ?? 0) > 0) {
                $userCampusId = (int) $userRow['campus_id'];
            }
        }

        $userCampusFullName = '';
        if ($userCampusId > 0) {
            $campusRow = (new CampusModel())->find($userCampusId);
            if ($campusRow && ! empty($campusRow['name'])) {
                $userCampusFullName = strtoupper(trim((string) $campusRow['name']));
            }
        }
        if ($userCampusFullName === '' && ! $canViewAllCrmCampuses && (int) session('user.id') > 0) {
            $userEvents = (new EventModel())->withCreator()->orderBy('event_date', 'DESC')->findAll();
            foreach ($userEvents as $ev) {
                if ((int) ($ev['created_by'] ?? 0) === (int) session('user.id') && ! empty($ev['campus_name'])) {
                    $userCampusFullName = strtoupper(trim((string) $ev['campus_name']));
                    break;
                }
            }
        }
        if ($userCampusFullName === '') {
            $userCampusFullName = 'UBSI KAMPUS YOGYAKARTA';
        }

        return $this->render('pages/crm_report', [
            'title' => 'Laporan & Rekomendasi Hasil CRM',
            'active' => 'crm-report',
            'canViewAllCrmCampuses' => $canViewAllCrmCampuses,
            'userCampusFullName' => $userCampusFullName,
            'requestedCampusParam' => trim((string) $this->request->getGet('campus')),
        ]);
    }

    public function taskManager(): string
    {
        $activeTab = $this->request->getGet('tab') === 'selesai' ? 'selesai' : 'berjalan';
        $search = trim((string) $this->request->getGet('q'));
        $search = mb_substr($search, 0, 120);
        $campusScope = $this->currentEventCampusScope();
        $isTaskAdmin = session('user.role') === 'Admin Sistem';
        $checklistModel = new EventChecklistItemModel();
        $tasks = $isTaskAdmin
            ? $checklistModel->allAssignedTasks($campusScope !== null ? $campusScope : null)
            : $checklistModel->allAssignedToUser(
                (int) session('user.id'),
                $campusScope !== null ? $campusScope : null
            );

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $tasks = array_values(array_filter($tasks, static function (array $task) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($task['event_title'] ?? ''),
                    (string) ($task['event_code'] ?? ''),
                    (string) ($task['name'] ?? ''),
                    (string) ($task['notes'] ?? ''),
                    (string) ($task['pic_name'] ?? ''),
                ]));

                return str_contains($haystack, $needle);
            }));
        }

        $runningTasks = array_values(array_filter(
            $tasks,
            static fn (array $task): bool => ($task['status'] ?? 'pending') !== 'done'
        ));
        $completedTasks = array_values(array_filter(
            $tasks,
            static fn (array $task): bool => ($task['status'] ?? '') === 'done'
        ));
        $taskCounts = [
            'running' => count($runningTasks),
            'completed' => count($completedTasks),
        ];
        $activeTasks = $activeTab === 'selesai' ? $completedTasks : $runningTasks;
        $perPage = 5;
        $totalTasks = count($activeTasks);
        $totalPages = max(1, (int) ceil($totalTasks / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedTasks = array_slice($activeTasks, $offset, $perPage);

        if ($activeTab === 'selesai') {
            $completedTasks = $paginatedTasks;
        } else {
            $runningTasks = $paginatedTasks;
        }

        if ($this->request->getGet('ack') === '1') {
            (new NotificationModel())
                ->where('user_id', (int) session('user.id'))
                ->where('type', 'checklist')
                ->where('read_at', null)
                ->set(['read_at' => date('Y-m-d H:i:s')])
                ->update();
        }

        return $this->render('pages/task_manager', [
            'title' => 'Task Manager',
            'active' => 'task-manager',
            'activeTab' => $activeTab,
            'search' => $search,
            'runningTasks' => $runningTasks,
            'completedTasks' => $completedTasks,
            'taskCounts' => $taskCounts,
            'isTaskAdmin' => $isTaskAdmin,
            'taskPagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $totalTasks,
                'totalPages' => $totalPages,
                'from' => $totalTasks === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $totalTasks),
            ],
        ]);
    }

    public function newTask(): string
    {
        return $this->render('pages/task_form', [
            'title' => 'Buat Tugas',
            'active' => 'task-manager',
            'events' => $this->manageableTaskEvents(),
            'users' => (new UserModel())
                ->where('status', 'Aktif')
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function storeTask()
    {
        $eventId = (int) $this->request->getPost('event_id');
        $name = trim((string) $this->request->getPost('name'));
        $phase = (string) $this->request->getPost('phase');
        $picUserId = (int) $this->request->getPost('pic_user_id');
        $deadline = trim((string) $this->request->getPost('deadline'));
        $notes = trim((string) $this->request->getPost('notes'));
        $materialDriveLink = trim((string) $this->request->getPost('material_drive_link'));
        $validPhases = ['Pra Event', 'Event', 'Pasca Event'];

        if ($eventId <= 0 || $name === '' || ! in_array($phase, $validPhases, true) || $picUserId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Event, nama tugas, fase, dan penerima tugas wajib diisi.');
        }

        if ($materialDriveLink !== '' && ! $this->isSafeExternalLink($materialDriveLink)) {
            return redirect()->back()->withInput()->with('error', 'Link bahan tugas wajib berupa URL http/https yang valid.');
        }

        $event = (new EventModel())->find($eventId);
        if (! $event || ! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('task-manager'))->with('error', 'Event tidak ditemukan atau Anda tidak memiliki izin membuat tugas untuk event ini.');
        }

        $user = (new UserModel())
            ->where('status', 'Aktif')
            ->where('id', $picUserId)
            ->first();
        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'User penerima tugas tidak valid atau belum aktif.');
        }

        $itemId = (new EventChecklistItemModel())->insert([
            'event_id' => $event['id'],
            'template_id' => null,
            'name' => $name,
            'phase' => $phase,
            'pic_user_id' => $picUserId,
            'is_assigned' => 1,
            'assigned_at' => date('Y-m-d H:i:s'),
            'deadline' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline) === 1 ? $deadline : null,
            'status' => 'pending',
            'notes' => $notes !== '' ? $notes : null,
            'material_drive_link' => $materialDriveLink !== '' ? $materialDriveLink : null,
        ], true);

        if (! $itemId) {
            return redirect()->back()->withInput()->with('error', 'Tugas belum berhasil disimpan. Coba periksa data tugas.');
        }

        (new NotificationModel())->insert([
            'user_id' => $picUserId,
            'type' => 'checklist',
            'message' => $name . ' ditugaskan untuk event ' . $event['title'] . '.',
        ]);

        $this->updateEventProgressFromChecklist((int) $event['id']);

        return redirect()->to(site_url('task-manager'))->with('success', 'Tugas berhasil dibuat dan dikirim ke ' . $user['name'] . '.');
    }

    public function events(): string
    {
        $data = $this->applicationData();
        $eventFilters = $this->selectedEventFilters();
        $eventState = $this->eventState($data['events'], $eventFilters);

        return $this->render('pages/events', [
            'title' => 'Event',
            'active' => 'events',
            'events' => $eventState['events'],
            'eventPagination' => $eventState['pagination'],
            'eventFilters' => $eventFilters,
            'categories' => $data['categories'],
            'isAdminSystem' => session('user.role') === 'Admin Sistem',
        ]);
    }

    public function exportEvents()
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('events'))->with('error', 'Hanya Admin Sistem yang dapat download data event.');
        }

        $data = $this->applicationData();
        $events = array_values(array_filter($data['events'], static function (array $event): bool {
            return in_array((string) ($event['status'] ?? ''), ['disetujui', 'selesai'], true);
        }));

        usort($events, static function (array $first, array $second): int {
            return strcmp((string) ($second['date'] ?? ''), (string) ($first['date'] ?? ''));
        });

        $filename = 'daftar-event-disetujui-selesai-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->eventsExcelHtml($events));
    }

    public function downloadEventImportTemplate()
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('events'))->with('error', 'Hanya Admin Sistem yang dapat download template import event.');
        }

        $campus = (new CampusModel())->orderBy('id', 'ASC')->first();
        $category = (new EventCategoryModel())->orderBy('name', 'ASC')->first();
        $user = (new UserModel())->where('status', 'Aktif')->orderBy('id', 'ASC')->first();
        $currentUserId = (int) session('user.id');
        $rows = [
            array_keys($this->eventImportColumns()),
            [
                '',
                'Seminar Contoh Upload Event',
                (string) ($category['name'] ?? 'Seminar'),
                (string) ($campus['name'] ?? 'Kampus Jakarta'),
                '2026-06-15',
                '',
                '09:00',
                'Aula Kampus',
                'disetujui',
                (string) ($user['id'] ?? $currentUserId),
                '120',
                '5000000',
                '75',
                'Contoh baris template import event.',
                (string) $currentUserId,
                date('Y-m-d H:i:s'),
                '',
            ],
        ];

        $filename = 'template-import-event.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->xlsxFromRows($rows));
    }

    public function importEvents()
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('events'))->with('error', 'Hanya Admin Sistem yang dapat upload data event.');
        }

        $file = $this->request->getFile('event_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('events'))->with('error', 'File rekap event belum dipilih.');
        }

        $extension = strtolower($file->getClientExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            return redirect()->to(site_url('events'))->with('error', 'Format file harus Excel .xlsx atau CSV.');
        }

        try {
            $rows = $extension === 'xlsx'
                ? $this->readEventXlsxRows($file->getTempName())
                : $this->readEventCsvRows($file->getTempName());
        } catch (\Throwable $error) {
            log_message('error', 'Gagal membaca file import event: ' . $error->getMessage());

            return redirect()->to(site_url('events'))->with('error', 'File Excel tidak bisa dibaca. Pastikan format file .xlsx valid dan gunakan template terbaru.');
        }

        if ($rows === []) {
            return redirect()->to(site_url('events'))->with('error', 'File tidak berisi data event yang bisa dibaca.');
        }

        $prepared = $this->prepareEventImportRows($rows);
        if ($prepared['errors'] !== []) {
            return redirect()
                ->to(site_url('events'))
                ->with('error', 'Import dibatalkan. ' . implode(' ', array_slice($prepared['errors'], 0, 8)));
        }

        $eventModel = new EventModel();
        $eventModel->skipValidation(true);
        $proposalModel = new ProposalModel();
        $db = db_connect();
        $inserted = 0;
        $db->transStart();

        foreach ($prepared['rows'] as $payload) {
            $eventId = (int) $eventModel->insert($payload, true);
            $proposalModel->insert([
                'code' => $this->nextCode('PRP', 'proposals'),
                'event_id' => $eventId,
                'status' => $this->proposalStatusForEventStatus((string) $payload['status']),
                'content' => 'Proposal otomatis dibuat dari import data event.',
            ]);
            $this->ensureEventChecklist($eventId, (int) $payload['created_by']);
            if ((string) $payload['status'] === 'selesai') {
                $this->ensureReportRecord($eventId);
            }
            $inserted++;
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->to(site_url('events'))->with('error', 'Import event gagal disimpan ke database.');
        }

        return redirect()->to(site_url('events'))->with('success', $inserted . ' event berhasil diimport ke database.');
    }

    public function newEvent(): string
    {
        $data = $this->applicationData();
        $eventDefaults = [];
        $campusScope = $this->currentEventCampusScope();
        if ($campusScope !== null && $campusScope > 0) {
            $eventDefaults['campus_id'] = $campusScope;
        }

        return $this->render('pages/event_form', [
            'title' => 'Buat Event',
            'active' => 'events',
            'categories' => $data['categoryOptions'],
            'users' => $data['users'],
            'campuses' => $data['campuses'],
            'event' => $eventDefaults,
        ]);
    }

    public function storeEvent()
    {
        $rules = [
            'title' => 'required|min_length[5]|max_length[180]',
            'category' => 'required|max_length[80]',
            'campus_id' => 'required|is_natural_no_zero',
            'event_date' => 'required|valid_date',
            'event_end_date' => 'permit_empty|valid_date',
            'event_time' => 'required',
            'location' => 'required|max_length[180]',
            'description' => 'permit_empty|max_length[3000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $campusId = (int) $this->request->getPost('campus_id');
        $campus = (new CampusModel())->find($campusId);
        if (! $campus) {
            return redirect()->back()->withInput()->with('error', 'Kampus pelaksana tidak valid. Pilih kampus dari daftar yang tersedia.');
        }

        if (! $this->canUseEventCampus($campusId)) {
            return redirect()->back()->withInput()->with('error', 'Anda hanya dapat membuat event untuk kampus pelaksana sendiri.');
        }

        $category = $this->normalizedCategoryName((string) $this->request->getPost('category'));
        if (! (new EventCategoryModel())->where('name', $category)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kategori event tidak valid. Pilih kategori dari daftar yang dibuat admin.');
        }

        $eventDateRange = $this->eventDateRangeInput();
        if ($eventDateRange['error'] !== '') {
            return redirect()->back()->withInput()->with('error', $eventDateRange['error']);
        }

        $eventModel = new EventModel();
        $code = $this->nextCode('EVT', 'events');
        $userId = (int) session('user.id');
        $submitAction = (string) $this->request->getPost('submit_action');
        $status = $submitAction === 'submit' ? 'diajukan' : 'draft';

        $eventId = $eventModel->insert([
            'code' => $code,
            'title' => $this->request->getPost('title'),
            'category' => $category,
            'campus_id' => $campusId,
            'event_date' => $eventDateRange['start'],
            'event_end_date' => $eventDateRange['end'],
            'event_time' => $this->request->getPost('event_time'),
            'location' => $this->request->getPost('location'),
            'status' => $status,
            'created_by' => $userId,
            'participants_count' => (int) $this->request->getPost('participants_count'),
            'budget' => (float) $this->request->getPost('budget'),
            'progress' => $status === 'diajukan' ? 40 : 20,
            'description' => $this->request->getPost('description'),
        ], true);

        (new ProposalModel())->insert([
            'code' => $this->nextCode('PRP', 'proposals'),
            'event_id' => $eventId,
            'status' => $status === 'diajukan' ? 'review admin' : 'draft',
            'content' => 'Proposal otomatis dibuat dari data event.',
        ]);

        $this->ensureEventChecklist((int) $eventId, $userId);
        $calendarSync = $this->syncNewEventToGoogleCalendar((int) $eventId, $userId);
        $this->notifyAdmins($code . ' menunggu approval admin.');

        $message = 'Event berhasil disimpan.';
        if ($calendarSync['status'] === 'synced') {
            $message .= ' Event otomatis masuk ke Google Calendar.';
        } elseif ($calendarSync['status'] === 'pending_connect') {
            $message .= ' Google Calendar belum terhubung, sync otomatis menunggu koneksi.';
        } else {
            $message .= ' Sync Google Calendar otomatis belum berhasil: ' . $calendarSync['message'];
        }

        return redirect()->to(site_url('events/' . $code))->with('success', $message);
    }

    public function editEvent(string $code)
    {
        $event = (new EventModel())->where('code', $code)->first();

        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat mengubah event.');
        }

        $data = $this->applicationData();

        return $this->render('pages/event_form', [
            'title' => 'Update Event',
            'active' => 'events',
            'categories' => $data['categoryOptions'],
            'users' => $data['users'],
            'campuses' => $data['campuses'],
            'event' => $event,
            'formMode' => 'edit',
            'formAction' => site_url('events/' . $code . '/update'),
        ]);
    }

    public function updateEvent(string $code)
    {
        $eventModel = new EventModel();
        $event = $eventModel->where('code', $code)->first();

        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat mengubah event.');
        }

        $rules = [
            'title' => 'required|min_length[5]|max_length[180]',
            'category' => 'required|max_length[80]',
            'campus_id' => 'required|is_natural_no_zero',
            'event_date' => 'required|valid_date',
            'event_end_date' => 'permit_empty|valid_date',
            'event_time' => 'required',
            'location' => 'required|max_length[180]',
            'description' => 'permit_empty|max_length[3000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $campusId = (int) $this->request->getPost('campus_id');
        if (! (new CampusModel())->find($campusId)) {
            return redirect()->back()->withInput()->with('error', 'Kampus pelaksana tidak valid. Pilih kampus dari daftar yang tersedia.');
        }

        if (! $this->canUseEventCampus($campusId)) {
            return redirect()->back()->withInput()->with('error', 'Anda hanya dapat menyimpan event untuk kampus pelaksana sendiri.');
        }

        $category = $this->normalizedCategoryName((string) $this->request->getPost('category'));
        if (! (new EventCategoryModel())->where('name', $category)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kategori event tidak valid. Pilih kategori dari daftar yang dibuat admin.');
        }

        $eventDateRange = $this->eventDateRangeInput();
        if ($eventDateRange['error'] !== '') {
            return redirect()->back()->withInput()->with('error', $eventDateRange['error']);
        }

        $updates = [
            'title' => $this->request->getPost('title'),
            'category' => $category,
            'campus_id' => $campusId,
            'event_date' => $eventDateRange['start'],
            'event_end_date' => $eventDateRange['end'],
            'event_time' => $this->request->getPost('event_time'),
            'location' => $this->request->getPost('location'),
            'participants_count' => (int) $this->request->getPost('participants_count'),
            'budget' => (float) $this->request->getPost('budget'),
            'description' => $this->request->getPost('description'),
        ];

        $submitAction = (string) $this->request->getPost('submit_action');
        if ($submitAction === 'submit' && in_array(($event['status'] ?? ''), ['draft', 'ditolak'], true)) {
            $updates['status'] = 'diajukan';
            $updates['progress'] = max((int) ($event['progress'] ?? 0), 40);
            $updates['rejected_reason'] = null;
        }

        $eventModel->update($event['id'], $updates);
        $calendarSync = $this->syncNewEventToGoogleCalendar((int) $event['id'], (int) session('user.id'));

        if (($updates['status'] ?? null) === 'diajukan') {
            $proposal = (new ProposalModel())->where('event_id', $event['id'])->first();
            if ($proposal) {
                (new ProposalModel())->update($proposal['id'], ['status' => 'review admin']);
            }

            $this->notifyAdmins($event['code'] . ' menunggu approval admin.');
        }

        $message = 'Event berhasil diperbarui.';
        if ($calendarSync['status'] === 'synced') {
            $message .= ' Google Calendar ikut diperbarui.';
        } elseif ($calendarSync['status'] === 'pending_connect') {
            $message .= ' Google Calendar belum terhubung, sync menunggu koneksi.';
        } else {
            $message .= ' Sync Google Calendar belum berhasil: ' . $calendarSync['message'];
        }

        return redirect()->to(site_url('events/' . $code))->with('success', $message);
    }

    public function eventDetail(string $code): string
    {
        $this->completePastApprovedEvents();
        $event = (new EventModel())->findByCode($code);

        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        $this->ensureEventChecklist((int) $event['id'], (int) $event['created_by']);
        $checklistItems = (new EventChecklistItemModel())->forEvent((int) $event['id']);
        $checklistDocumentUploads = $this->checklistDocumentUploads($checklistItems);
        $this->syncChecklistUploadsToDocumentRecords((int) $event['id'], $checklistDocumentUploads);
        $this->updateEventProgressFromChecklist((int) $event['id']);
        $event = (new EventModel())->findByCode($code) ?: $event;
        $normalizedEvent = $this->normalizeEvent($event);
        $canUploadReportDocument = $this->canSubmitPostEventDocuments($normalizedEvent);
        $report = $canUploadReportDocument
            ? $this->normalizeReport($this->ensureCompletedEventReport((int) $event['id']))
            : $this->documentByEvent(new ReportModel(), (int) $event['id']);
        $questionnaire = $this->ensureEventQuestionnaire($normalizedEvent);
        $questionnaireResponses = (new EventQuestionnaireResponseModel())
            ->where('event_id', (int) $event['id'])
            ->orderBy('submitted_at', 'DESC')
            ->findAll();

        return $this->render('pages/event_detail', [
            'title' => $event['title'],
            'active' => 'events',
            'event' => $normalizedEvent,
            'proposal' => $this->documentByEvent(new ProposalModel(), (int) $event['id']),
            'report' => $report,
            'checklistDocumentUploads' => $checklistDocumentUploads,
            'checklistGroups' => $this->groupChecklistItems($checklistItems),
            'checklistSummary' => $this->checklistSummary($checklistItems),
            'checklistUsers' => (new UserModel())->orderBy('name', 'ASC')->findAll(),
            'canManageChecklist' => $this->canManageEventChecklist($event),
            'canSubmitDraftEvent' => ($event['status'] ?? '') === 'draft' && $this->canManageEventChecklist($event),
            'canReportChecklist' => $this->canReportAnyChecklist($checklistItems),
            'canUploadReportDocument' => $canUploadReportDocument,
            'calendarIntegration' => (new CalendarIntegrationModel())->where('event_id', $event['id'])->where('provider', 'google_calendar')->first(),
            'googleCalendar' => (new GoogleCalendarService())->tokenStatus((int) session('user.id')),
            'questionnaire' => $questionnaire,
            'questionnaireResponses' => $questionnaireResponses,
        ]);
    }

    private function ensureEventQuestionnaire(array $event): array
    {
        $eventId = (int) ($event['db_id'] ?? $event['id'] ?? 0);
        $eventCode = (string) ($event['code'] ?? $event['id'] ?? '');
        $model = new EventQuestionnaireModel();
        $row = $model->where('event_id', $eventId)->first();

        $defaultQuestions = [
            [
                'id' => 'q_minat_prodi',
                'label' => 'Program Studi / Jurusan yang Diminati di UBSI',
                'type' => 'select',
                'required' => true,
                'placeholder' => 'Pilih program studi minat Anda',
                'options' => [
                    'Sistem Informasi',
                    'Teknologi Informasi',
                    'Rekayasa Perangkat Lunak',
                    'Ilmu Komunikasi',
                    'Manajemen',
                    'Akuntansi',
                    'Belum Menentukan / Ingin Konsultasi',
                ],
            ],
            [
                'id' => 'q_rencana_kuliah',
                'label' => 'Rencana Melanjutkan Kuliah Setelah Lulus',
                'type' => 'radio',
                'required' => true,
                'placeholder' => '',
                'options' => [
                    'Ya, Tahun Ini / Gelombang Terdekat',
                    'Ya, Masih Mencari Informasi Beasiswa / Biaya',
                    'Kuliah Sambil Kerja (Kelas Karyawan)',
                    'Masih Dipertimbangkan Bersama Orang Tua',
                ],
            ],
            [
                'id' => 'q_kesan_pertanyaan',
                'label' => 'Pertanyaan atau Kesan & Pesan Mengikuti Event Ini',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => 'Tuliskan kesan, pesan, atau hal yang ingin ditanyakan kepada tim PMB...',
                'options' => [],
            ],
        ];

        if (! $row) {
            $insertData = [
                'event_id' => $eventId,
                'event_code' => $eventCode,
                'title' => 'Form Kuesioner & Presensi Peserta - ' . (string) ($event['title'] ?? $eventCode),
                'description' => 'Silakan lengkapi data diri dan pertanyaan kuesioner kegiatan di bawah ini.',
                'is_active' => 1,
                'questions_json' => json_encode($defaultQuestions, JSON_UNESCAPED_UNICODE),
                'crm_batch_id' => null,
                'created_by' => is_numeric($event['created_by'] ?? null) ? (int) $event['created_by'] : ((int) session('user.id') ?: 1),
            ];
            $id = $model->insert($insertData, true);
            $row = $model->find($id) ?: array_merge(['id' => $id], $insertData);
        }

        $questions = json_decode((string) ($row['questions_json'] ?? '[]'), true);
        if (! is_array($questions)) {
            $questions = $defaultQuestions;
        }

        $row['questions'] = array_values($questions);

        return $row;
    }

    public function saveEventQuestionnaire(string $code)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event || ! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat mengubah form kuesioner.');
        }

        $questionnaire = $this->ensureEventQuestionnaire($this->normalizeEvent($event));
        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));
        $isActive = (int) ($this->request->getPost('is_active') ? 1 : 0);
        $rawQuestionsJson = (string) $this->request->getPost('questions_json');

        if ($title === '') {
            $title = 'Form Kuesioner & Daftar Hadir - ' . (string) $event['title'];
        }

        $decoded = json_decode($rawQuestionsJson, true);
        $cleanQuestions = [];
        if (is_array($decoded)) {
            foreach (array_values($decoded) as $idx => $q) {
                if (! is_array($q)) {
                    continue;
                }
                $label = trim((string) ($q['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $type = strtolower(trim((string) ($q['type'] ?? 'text')));
                if (! in_array($type, ['text', 'textarea', 'select', 'radio', 'checkbox'], true)) {
                    $type = 'text';
                }
                $rawOptions = $q['options'] ?? [];
                if (is_string($rawOptions)) {
                    $rawOptions = preg_split('/\r\n|\r|\n|,/', $rawOptions) ?: [];
                }
                $options = [];
                if (is_array($rawOptions)) {
                    foreach ($rawOptions as $opt) {
                        $optStr = trim((string) $opt);
                        if ($optStr !== '') {
                            $options[] = $optStr;
                        }
                    }
                }
                $cleanQuestions[] = [
                    'id' => ! empty($q['id']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $q['id']) : ('q_custom_' . ($idx + 1)),
                    'label' => mb_substr($label, 0, 240),
                    'type' => $type,
                    'required' => ! empty($q['required']),
                    'placeholder' => mb_substr(trim((string) ($q['placeholder'] ?? '')), 0, 180),
                    'options' => $options,
                ];
            }
        }

        (new EventQuestionnaireModel())->update((int) $questionnaire['id'], [
            'title' => mb_substr($title, 0, 200),
            'description' => mb_substr($description, 0, 1500),
            'is_active' => $isActive,
            'questions_json' => json_encode($cleanQuestions, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->to(site_url('events/' . $code) . '#event-questionnaire-section')
            ->with('success', 'Form Kuesioner Event, Link Responden & QR Code berhasil disimpan dan siap dibagikan.');
    }

    public function publicQuestionnaire(string $code)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Form Kuesioner Event tidak ditemukan.');
        }

        $normalizedEvent = $this->normalizeEvent($event);
        $questionnaire = $this->ensureEventQuestionnaire($normalizedEvent);

        return view('pages/public_questionnaire', [
            'title' => $questionnaire['title'] ?? ('Kuesioner - ' . $normalizedEvent['title']),
            'event' => $normalizedEvent,
            'questionnaire' => $questionnaire,
            'submitted' => (string) $this->request->getGet('submitted') === '1',
        ]);
    }

    public function submitPublicQuestionnaire(string $code)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Form Kuesioner Event tidak ditemukan.');
        }

        $normalizedEvent = $this->normalizeEvent($event);
        $questionnaire = $this->ensureEventQuestionnaire($normalizedEvent);

        if ((int) ($questionnaire['is_active'] ?? 1) !== 1) {
            return redirect()->back()->withInput()->with('error', 'Mohon maaf, pengisian kuesioner untuk event ini sedang ditutup.');
        }

        $studentName = trim((string) $this->request->getPost('student_name'));
        $email = trim((string) $this->request->getPost('email'));
        $schoolOrigin = trim((string) $this->request->getPost('school_origin'));
        $rawWhatsapp = trim((string) $this->request->getPost('whatsapp'));
        $whatsapp = $this->normalizeQuestionnaireWhatsapp($rawWhatsapp);

        $participantStatus = trim((string) $this->request->getPost('participant_status'));
        if (! in_array($participantStatus, ['Siswa', 'Umum', 'Guru'], true)) {
            $participantStatus = 'Siswa';
        }
        $studentClass = trim((string) $this->request->getPost('student_class'));

        if ($studentName === '' || $schoolOrigin === '' || $whatsapp === '' || $email === '') {
            return redirect()->back()->withInput()->with('error', 'Nama Lengkap, Email Aktif, Asal Sekolah, dan Nomor WhatsApp wajib diisi.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Format alamat email tidak valid.');
        }

        if ($participantStatus === 'Siswa') {
            if (! in_array($studentClass, ['X', 'XI', 'XII'], true)) {
                return redirect()->back()->withInput()->with('error', 'Untuk status peserta Siswa, mohon pilih kelas (X, XI, atau XII).');
            }
        } else {
            $studentClass = null;
        }

        $questions = is_array($questionnaire['questions'] ?? null) ? $questionnaire['questions'] : [];
        $submittedAnswers = $this->request->getPost('custom_answers') ?: $this->request->getPost('answers');
        $submittedAnswers = is_array($submittedAnswers) ? $submittedAnswers : [];

        $savedAnswers = [];
        $notesParts = [];

        foreach ($questions as $idx => $q) {
            $qId = (string) ($q['id'] ?? ('q_' . ($idx + 1)));
            $qLabel = (string) ($q['label'] ?? ('Pertanyaan ' . ($idx + 1)));
            $isRequired = ! empty($q['required']);
            $rawVal = $submittedAnswers[$qId] ?? '';

            if (is_array($rawVal)) {
                $cleanVals = array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $rawVal), static fn ($v) => $v !== ''));
                $answerText = implode(', ', $cleanVals);
            } else {
                $answerText = trim((string) $rawVal);
            }

            if ($isRequired && $answerText === '') {
                return redirect()->back()->withInput()->with('error', 'Pertanyaan wajib diisi: "' . $qLabel . '".');
            }

            $savedAnswers[] = [
                'id' => $qId,
                'label' => $qLabel,
                'answer' => $answerText,
            ];

            if ($answerText !== '') {
                $notesParts[] = $qLabel . ': ' . $answerText;
            }
        }

        $batchModel = new CrmUploadBatchModel();
        $batchId = (int) ($questionnaire['crm_batch_id'] ?? 0);
        $batch = $batchId > 0 ? $batchModel->find($batchId) : null;

        if (! $batch) {
            $batchCode = 'QST-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $event['code'])) . '-' . (int) $event['id'];
            $existingBatch = $batchModel->where('batch_code', $batchCode)->first();
            if ($existingBatch) {
                $batchId = (int) $existingBatch['id'];
            } else {
                $batchId = (int) $batchModel->insert([
                    'batch_code' => $batchCode,
                    'file_name' => 'Kuesioner QR: ' . mb_substr((string) $event['title'], 0, 180),
                    'event_id' => (int) $event['id'],
                    'total_rows' => 0,
                    'uploaded_by' => null,
                    'uploaded_at' => date('Y-m-d H:i:s'),
                ], true);
            }

            (new EventQuestionnaireModel())->update((int) $questionnaire['id'], [
                'crm_batch_id' => $batchId,
            ]);
        }

        $prospectStatus = $this->evaluateQuestionnaireProspectStatus($savedAnswers, $participantStatus, $studentClass ?? '');
        $prospectPrefix = $prospectStatus === 'priority'
            ? '[⭐ PROSPEK PRIORITAS (Chat di Awal)]'
            : '[🕒 FOLLOW UP LANJUTAN (Setelah Prospek Selesai)]';

        $statusSummary = $participantStatus === 'Siswa' ? ('Siswa Kelas ' . $studentClass) : $participantStatus;
        $notesSummary = $prospectPrefix . ' [' . $statusSummary . ' | Email: ' . $email . '] [Kuesioner QR Event ' . $event['code'] . ']';
        if ($notesParts !== []) {
            $notesSummary .= ' ' . implode(' | ', $notesParts);
        }
        $notesSummary = mb_substr($notesSummary, 0, 1950);

        $leadModel = new CrmLeadModel();
        $existingLead = $leadModel
            ->where('event_id', (int) $event['id'])
            ->where('whatsapp', $whatsapp)
            ->first();

        if ($existingLead) {
            $crmLeadId = (int) $existingLead['id'];
            $leadModel->update($crmLeadId, [
                'student_name' => mb_substr($studentName, 0, 160),
                'email' => mb_substr($email, 0, 160),
                'participant_status' => mb_substr($participantStatus, 0, 60),
                'student_class' => $studentClass !== null ? mb_substr($studentClass, 0, 30) : null,
                'school_origin' => mb_substr($schoolOrigin, 0, 180),
                'prospect_status' => $prospectStatus,
                'label' => $prospectStatus === 'priority' ? 'Minat/Tertarik' : ($existingLead['label'] ?? null),
                'notes' => $notesSummary,
            ]);
        } else {
            $crmLeadId = (int) $leadModel->insert([
                'batch_id' => $batchId > 0 ? $batchId : null,
                'event_id' => (int) $event['id'],
                'student_name' => mb_substr($studentName, 0, 160),
                'email' => mb_substr($email, 0, 160),
                'participant_status' => mb_substr($participantStatus, 0, 60),
                'student_class' => $studentClass !== null ? mb_substr($studentClass, 0, 30) : null,
                'school_origin' => mb_substr($schoolOrigin, 0, 180),
                'whatsapp' => $whatsapp,
                'stage' => 'Tahap 1',
                'prospect_status' => $prospectStatus,
                'label' => $prospectStatus === 'priority' ? 'Minat/Tertarik' : null,
                'agent_id' => null,
                'notes' => $notesSummary,
                'rejection_reason' => null,
                'responded_at' => null,
            ], true);
        }

        if ($batchId > 0) {
            $totalLeadsInBatch = $leadModel->where('batch_id', $batchId)->countAllResults();
            $batchModel->update($batchId, [
                'total_rows' => max(1, (int) $totalLeadsInBatch),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]);
        }

        (new EventQuestionnaireResponseModel())->insert([
            'questionnaire_id' => (int) $questionnaire['id'],
            'event_id' => (int) $event['id'],
            'crm_lead_id' => $crmLeadId > 0 ? $crmLeadId : null,
            'prospect_status' => $prospectStatus,
            'student_name' => mb_substr($studentName, 0, 160),
            'email' => mb_substr($email, 0, 160),
            'participant_status' => mb_substr($participantStatus, 0, 60),
            'student_class' => $studentClass !== null ? mb_substr($studentClass, 0, 30) : null,
            'school_origin' => mb_substr($schoolOrigin, 0, 180),
            'whatsapp' => $whatsapp,
            'answers_json' => json_encode($savedAnswers, JSON_UNESCAPED_UNICODE),
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('kuesioner/' . $code) . '?submitted=1')
            ->with('success', 'Terima kasih, ' . $studentName . '! Jawaban kuesioner Anda telah berhasil disimpan.');
    }

    public function evaluateQuestionnaireProspectStatus(array $savedAnswers, string $participantStatus = '', string $studentClass = ''): string
    {
        $priorityScore = 0;

        // Kelas XII adalah target utama penerimaan mahasiswa baru tahun ini
        if (strtoupper($studentClass) === 'XII') {
            $priorityScore += 3;
        } elseif ($participantStatus === 'Siswa') {
            $priorityScore += 1;
        }

        $highKeywords = [
            'daftar', 'mendaftar', 'pendaftaran', 'biaya', 'beasiswa', 'potongan',
            'gelombang', 'syarat', 'formulir', 'tes', 'jadwal', 'pmb', 'registrasi',
            'brosur', 'sangat minat', 'tertarik', 'mau kuliah', 'ingin masuk', 'tahun ini',
            'segera', 'karyawan', 'sambil kerja'
        ];

        $lowKeywords = [
            'tidak berminat', 'belum berminat', 'hanya hadir', 'tidak kuliah', 'belum tahu',
            'ragu', 'tidak tertarik', 'belum ada', 'tidak ada', 'hanya ikut'
        ];

        foreach ($savedAnswers as $key => $item) {
            if (is_array($item)) {
                $lbl = mb_strtolower(trim((string) ($item['label'] ?? $item['question'] ?? $key)));
                $ans = mb_strtolower(trim((string) ($item['answer'] ?? $item['value'] ?? '')));
            } else {
                $lbl = mb_strtolower(trim((string) $key));
                $ans = mb_strtolower(trim((string) $item));
            }

            if ($ans === '') {
                continue;
            }

            // Check if specific answer indicates high or low intent
            foreach ($highKeywords as $kw) {
                if (str_contains($ans, $kw)) {
                    $priorityScore += 2;
                }
            }

            foreach ($lowKeywords as $lkw) {
                if (str_contains($ans, $lkw)) {
                    $priorityScore -= 2;
                }
            }

            // Specific check for study plan
            if (str_contains($lbl, 'rencana') || str_contains($lbl, 'kuliah') || str_contains($lbl, 'setelah lulus')) {
                if (
                    str_contains($ans, 'tahun ini')
                    || str_contains($ans, 'gelombang')
                    || str_contains($ans, 'segera')
                    || str_contains($ans, 'karyawan')
                    || str_contains($ans, 'sambil kerja')
                    || str_contains($ans, 'beasiswa')
                    || (str_starts_with($ans, 'ya') && ! str_contains($ans, 'tidak'))
                ) {
                    $priorityScore += 3;
                } elseif (str_contains($ans, 'ragu') || str_contains($ans, 'belum') || str_contains($ans, 'tidak')) {
                    $priorityScore -= 2;
                }
            }

            // Specific check for study program selection
            if (str_contains($lbl, 'prodi') || str_contains($lbl, 'jurusan')) {
                if ($ans !== '' && ! str_contains($ans, 'belum') && ! str_contains($ans, 'tidak ada')) {
                    $priorityScore += 2;
                }
            }
        }

        return $priorityScore > 0 ? 'priority' : 'nurture';
    }

    public function exportQuestionnaireResponses(string $code)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event || ! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        $normalizedEvent = $this->normalizeEvent($event);
        $questionnaire = $this->ensureEventQuestionnaire($normalizedEvent);
        $questions = is_array($questionnaire['questions'] ?? null) ? $questionnaire['questions'] : [];
        $responses = (new EventQuestionnaireResponseModel())
            ->where('event_id', (int) $event['id'])
            ->orderBy('submitted_at', 'DESC')
            ->findAll();

        $header = [
            'No',
            'Nama Lengkap',
            'Email',
            'Status Peserta',
            'Kelas (Siswa)',
            'Asal Sekolah / Instansi',
            'No. WhatsApp',
            'Klasifikasi Prospek CRM',
        ];
        foreach ($questions as $q) {
            $header[] = (string) ($q['label'] ?? 'Pertanyaan');
        }
        $header[] = 'ID Lead CRM';
        $header[] = 'Waktu Mengisi';

        $rows = [$header];

        foreach (array_values($responses) as $idx => $resp) {
            $ansList = json_decode((string) ($resp['answers_json'] ?? '[]'), true);
            $ansMap = [];
            if (is_array($ansList)) {
                foreach ($ansList as $item) {
                    if (is_array($item)) {
                        $keyId = (string) ($item['id'] ?? '');
                        $keyLabel = (string) ($item['label'] ?? '');
                        $val = (string) ($item['answer'] ?? '');
                        if ($keyId !== '') {
                            $ansMap[$keyId] = $val;
                        }
                        if ($keyLabel !== '') {
                            $ansMap[$keyLabel] = $val;
                        }
                    }
                }
            }

            $isPriority = ($resp['prospect_status'] ?? '') === 'priority';
            $prospectLabel = $isPriority ? 'Prospek Prioritas (Chat di Awal)' : 'Follow-up Lanjutan (Setelah Prospek)';

            $row = [
                (string) ($idx + 1),
                (string) ($resp['student_name'] ?? '-'),
                (string) ($resp['email'] ?? '-'),
                (string) ($resp['participant_status'] ?? 'Siswa'),
                (string) ($resp['student_class'] ? ('Kelas ' . $resp['student_class']) : '-'),
                (string) ($resp['school_origin'] ?? '-'),
                (string) ($resp['whatsapp'] ?? '-'),
                $prospectLabel,
            ];

            foreach ($questions as $q) {
                $qId = (string) ($q['id'] ?? '');
                $qLabel = (string) ($q['label'] ?? '');
                $row[] = (string) ($ansMap[$qId] ?? $ansMap[$qLabel] ?? '-');
            }

            $row[] = ! empty($resp['crm_lead_id']) ? ('CRM #' . (int) $resp['crm_lead_id']) : 'Tersinkron';
            $row[] = (string) ($resp['submitted_at'] ?? '-');

            $rows[] = $row;
        }

        $filename = 'hasil-kuesioner-crm-' . strtolower((string) $event['code']) . '-' . date('Ymd-His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->xlsxFromRows($rows, 'Hasil Kuesioner'));
    }

    private function normalizeQuestionnaireWhatsapp(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return strlen($digits) >= 9 ? substr($digits, 0, 25) : '';
    }

    public function deleteEvent(string $code)
    {
        $eventModel = new EventModel();
        $event = $eventModel->where('code', $code)->first();

        if (! $event) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Event tidak ditemukan atau sudah dihapus.');
        }

        if (! $this->canDeleteEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Anda tidak memiliki izin untuk menghapus event ini.');
        }

        $calendarIntegration = (new CalendarIntegrationModel())
            ->where('event_id', $event['id'])
            ->where('provider', 'google_calendar')
            ->first();
        $externalEventId = trim((string) ($calendarIntegration['external_event_id'] ?? ''));
        $calendarWarning = '';

        if ($externalEventId !== '') {
            try {
                $google = new GoogleCalendarService();
                if ($google->isConfigured() && ($google->tokenStatus((int) session('user.id'))['connected'] ?? false)) {
                    $google->deleteEvent((int) session('user.id'), $externalEventId);
                }
            } catch (\Throwable $error) {
                $calendarWarning = ' Namun event Google Calendar belum bisa dihapus otomatis: ' . $error->getMessage();
            }

            (new GoogleCalendarEventModel())->deleteByGoogleEventId($externalEventId);
        }

        (new CalendarIntegrationModel())
            ->where('event_id', $event['id'])
            ->where('provider', 'google_calendar')
            ->delete();

        $eventModel->delete($event['id']);

        return redirect()->to(site_url('dashboard'))->with('success', 'Event ' . $code . ' berhasil dihapus dari dashboard dan kalender.' . $calendarWarning);
    }

    public function submitDraftEvent(string $code)
    {
        $eventModel = new EventModel();
        $event = $eventModel->where('code', $code)->first();

        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat mengajukan event.');
        }

        if (($event['status'] ?? '') !== 'draft') {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya event berstatus draft yang dapat diajukan.');
        }

        $eventModel->update($event['id'], [
            'status' => 'diajukan',
            'progress' => max((int) ($event['progress'] ?? 0), 40),
            'rejected_reason' => null,
        ]);

        $proposal = (new ProposalModel())->where('event_id', $event['id'])->first();
        if ($proposal) {
            (new ProposalModel())->update($proposal['id'], ['status' => 'review admin']);
        }

        $this->notifyAdmins($event['code'] . ' menunggu approval admin.');

        return redirect()->to(site_url('events/' . $code))->with('success', 'Event berhasil diajukan ke Admin Sistem.');
    }

    public function updateChecklist(string $code)
    {
        $event = (new EventModel())->where('code', $code)->first();

        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat mengubah checklist.');
        }

        $this->ensureEventChecklist((int) $event['id'], (int) $event['created_by']);
        $submittedItems = $this->request->getPost('items');
        $submittedItems = is_array($submittedItems) ? $submittedItems : [];
        $doneItems = $this->request->getPost('done');
        $doneItems = is_array($doneItems) ? array_map('intval', $doneItems) : [];
        $assignedItems = $this->request->getPost('assigned');
        $assignedItems = is_array($assignedItems) ? array_map('intval', $assignedItems) : [];
        $validUserIds = array_map(
            static fn (array $user): int => (int) $user['id'],
            (new UserModel())->findAll()
        );
        $model = new EventChecklistItemModel();
        $existing = $model->where('event_id', $event['id'])->findAll();

        foreach ($existing as $item) {
            $itemId = (int) $item['id'];
            $payload = is_array($submittedItems[$itemId] ?? null) ? $submittedItems[$itemId] : [];
            $picUserId = (int) ($payload['pic_user_id'] ?? 0);
            $deadline = trim((string) ($payload['deadline'] ?? ''));
            $notes = trim((string) ($payload['notes'] ?? ''));
            $materialDriveLink = trim((string) ($payload['material_drive_link'] ?? ''));
            if ($materialDriveLink !== '' && ! $this->isSafeExternalLink($materialDriveLink)) {
                return redirect()->to(site_url('events/' . $code))->with('error', 'Link bahan event untuk ' . $item['name'] . ' wajib berupa URL http/https yang valid.');
            }

            $isDone = in_array($itemId, $doneItems, true);
            $isAssigned = in_array($itemId, $assignedItems, true) && in_array($picUserId, $validUserIds, true);
            $status = $isDone ? 'done' : ((($item['status'] ?? '') === 'submitted') ? 'submitted' : 'pending');
            $wasAssigned = (int) ($item['is_assigned'] ?? 0) === 1;
            $picChanged = (int) ($item['pic_user_id'] ?? 0) !== $picUserId;

            $model->update($itemId, [
                'pic_user_id' => in_array($picUserId, $validUserIds, true) ? $picUserId : null,
                'is_assigned' => $isAssigned ? 1 : 0,
                'assigned_at' => $isAssigned ? ($item['assigned_at'] ?: date('Y-m-d H:i:s')) : null,
                'deadline' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline) === 1 ? $deadline : null,
                'status' => $status,
                'notes' => $notes !== '' ? $notes : null,
                'material_drive_link' => $materialDriveLink !== '' ? $materialDriveLink : null,
                'completed_at' => $isDone ? ($item['completed_at'] ?: date('Y-m-d H:i:s')) : null,
            ]);

            if ($isAssigned && (! $wasAssigned || $picChanged)) {
                (new NotificationModel())->insert([
                    'user_id' => $picUserId,
                    'type' => 'checklist',
                    'message' => $item['name'] . ' ditugaskan untuk event ' . $event['title'] . '.',
                ]);
            }
        }

        $this->updateEventProgressFromChecklist((int) $event['id']);

        return redirect()->to(site_url('events/' . $code))->with('success', 'Checklist kelengkapan berhasil diperbarui.');
    }

    public function storeChecklistItem(string $code)
    {
        $event = (new EventModel())->where('code', $code)->first();
        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat menetapkan checklist.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $phase = (string) $this->request->getPost('phase');
        $picUserId = (int) $this->request->getPost('pic_user_id');
        $deadline = trim((string) $this->request->getPost('deadline'));
        $notes = trim((string) $this->request->getPost('notes'));
        $materialDriveLink = trim((string) $this->request->getPost('material_drive_link'));
        $validPhases = ['Pra Event', 'Event', 'Pasca Event'];

        if ($name === '' || ! in_array($phase, $validPhases, true)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Nama dan fase checklist wajib valid.');
        }

        if ($materialDriveLink !== '' && ! $this->isSafeExternalLink($materialDriveLink)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Link bahan event wajib berupa URL http/https yang valid.');
        }

        $user = $picUserId > 0 ? (new UserModel())->find($picUserId) : null;

        (new EventChecklistItemModel())->insert([
            'event_id' => $event['id'],
            'template_id' => null,
            'name' => $name,
            'phase' => $phase,
            'pic_user_id' => $user ? $picUserId : null,
            'is_assigned' => $user && (bool) $this->request->getPost('is_assigned') ? 1 : 0,
            'assigned_at' => $user && (bool) $this->request->getPost('is_assigned') ? date('Y-m-d H:i:s') : null,
            'deadline' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline) === 1 ? $deadline : null,
            'status' => 'pending',
            'notes' => $notes !== '' ? $notes : null,
            'material_drive_link' => $materialDriveLink !== '' ? $materialDriveLink : null,
        ]);

        if ($user && (bool) $this->request->getPost('is_assigned')) {
            (new NotificationModel())->insert([
                'user_id' => $picUserId,
                'type' => 'checklist',
                'message' => $name . ' ditugaskan untuk event ' . $event['title'] . '.',
            ]);
        }

        return redirect()->to(site_url('events/' . $code))->with('success', 'Item checklist berhasil ditambahkan.');
    }

    public function deleteChecklistItem(string $code, int $itemId)
    {
        $event = (new EventModel())->where('code', $code)->first();
        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Hanya Admin Sistem atau pembuat event yang dapat menghapus checklist.');
        }

        $item = (new EventChecklistItemModel())->where('event_id', $event['id'])->where('id', $itemId)->first();
        if (! $item) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Item checklist tidak ditemukan.');
        }

        (new EventChecklistItemModel())->delete($itemId);
        $this->updateEventProgressFromChecklist((int) $event['id']);

        return redirect()->to(site_url('events/' . $code))->with('success', 'Item checklist berhasil dihapus.');
    }

    public function reportChecklistTask(int $itemId)
    {
        $model = new EventChecklistItemModel();
        $item = $model->find($itemId);
        if (! $item) {
            return redirect()->to(site_url('task-manager'))->with('error', 'Tugas checklist tidak ditemukan.');
        }

        $event = (new EventModel())->find((int) $item['event_id']);
        if (! $event) {
            return redirect()->to(site_url('task-manager'))->with('error', 'Event tugas tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('task-manager'))->with('error', 'Event tugas tidak ditemukan.');
        }

        if (! $this->canReportChecklistItem($item, $event)) {
            return redirect()->to(site_url('task-manager'))->with('error', 'Anda bukan PIC untuk tugas checklist ini.');
        }

        $driveLink = trim((string) $this->request->getPost('drive_link'));
        $isDone = (bool) $this->request->getPost('is_done');

        if ($isDone && ! $this->isSafeExternalLink($driveLink)) {
            return redirect()->back()->with('error', 'Link hasil wajib berupa URL http/https yang valid saat tugas ditandai terpenuhi.');
        }

        $model->update($itemId, [
            'drive_link' => $driveLink !== '' ? $driveLink : null,
            'submitted_by' => (int) session('user.id'),
            'submitted_at' => date('Y-m-d H:i:s'),
            'status' => $isDone ? 'done' : 'submitted',
            'completed_at' => $isDone ? date('Y-m-d H:i:s') : null,
        ]);

        $this->updateEventProgressFromChecklist((int) $item['event_id']);

        return redirect()->back()->with('success', 'Laporan hasil checklist berhasil disimpan.');
    }

    public function calendar(): string
    {
        $data = $this->applicationData();
        $month = $this->selectedCalendarMonth();
        $calendarState = $this->calendarState($month, $data['events']);
        $googleCalendarService = new GoogleCalendarService();
        $userId = (int) session('user.id');
        $googleCalendar = $googleCalendarService->tokenStatus($userId);
        $isAdminSystem = session('user.role') === 'Admin Sistem';

        return $this->render('pages/calendar', [
            'title' => 'Kalender',
            'active' => 'calendar',
            'events' => $data['events'],
            'calendarMonth' => $calendarState,
            'visibleEvents' => $calendarState['visibleEvents'],
            'googleEvents' => $calendarState['googleEvents'],
            'calendarIntegrations' => $data['calendarIntegrations'],
            'googleCalendar' => $googleCalendar,
            'googleCalendarConfigured' => $googleCalendarService->isConfigured(),
            'canDownloadGoogleCalendar' => ($googleCalendar['connected'] ?? false)
                || ($isAdminSystem && $googleCalendarService->adminDownloadTokenUserId($userId) !== null),
        ]);
    }

    public function approvals(): string
    {
        $data = $this->applicationData();
        $approvalState = $this->approvalState($data['events']);

        return $this->render('pages/approvals', [
            'title' => 'Approval',
            'active' => 'approvals',
            'events' => $approvalState['events'],
            'approvalPagination' => $approvalState['pagination'],
            'approvalStatusCounts' => $approvalState['statusCounts'],
        ]);
    }

    public function approveEvent(string $code)
    {
        $event = (new EventModel())->where('code', $code)->first();

        if (! $event) {
            return redirect()->back()->with('error', 'Event tidak ditemukan.');
        }

        (new EventModel())->update($event['id'], [
            'status' => 'disetujui',
            'progress' => max((int) $event['progress'], 70),
            'approved_by' => (int) session('user.id'),
            'approved_at' => date('Y-m-d H:i:s'),
            'rejected_reason' => null,
        ]);
        $this->updateEventProgressFromChecklist((int) $event['id']);

        $calendarIntegration = (new CalendarIntegrationModel())->where('event_id', $event['id'])->where('provider', 'google_calendar')->first();
        if (($calendarIntegration['sync_status'] ?? '') !== 'synced') {
            $this->upsertCalendarIntegration((int) $event['id'], null, null, 'pending_sync');
        }

        (new NotificationModel())->insert([
            'user_id' => $event['created_by'],
            'type' => 'approval',
            'message' => $event['title'] . ' disetujui dan masuk kalender.',
        ]);

        return redirect()->to(site_url('approvals'))->with('success', 'Event berhasil disetujui.');
    }

    public function rejectEvent(string $code)
    {
        $event = (new EventModel())->where('code', $code)->first();

        if (! $event) {
            return redirect()->back()->with('error', 'Event tidak ditemukan.');
        }

        (new EventModel())->update($event['id'], [
            'status' => 'ditolak',
            'rejected_reason' => $this->request->getPost('rejected_reason') ?: 'Perlu revisi data event.',
        ]);

        (new NotificationModel())->insert([
            'user_id' => $event['created_by'],
            'type' => 'approval',
            'message' => $event['title'] . ' ditolak dan perlu revisi.',
        ]);

        return redirect()->to(site_url('approvals'))->with('success', 'Event berhasil ditolak.');
    }

    public function documents(string $type = 'all'): string
    {
        $data = $this->applicationData();
        $isAdminSystem = session('user.role') === 'Admin Sistem';
        $queryType = trim((string) $this->request->getGet('type'));
        $type = $this->normalizeManagedDocumentType($queryType !== '' ? $queryType : $type) ?? 'all';
        $reports = array_map(fn (array $item) => $this->normalizeReport($item), (new ReportModel())->findAll());
        $documentState = $this->eventDocumentState($data['events'], $data['proposals'], $reports, $type);

        return $this->render('pages/documents', [
            'title' => 'Dokumen Event',
            'active' => 'documents',
            'documentRows' => $documentState['rows'],
            'documentNotifications' => $documentState['notifications'],
            'documentPagination' => $documentState['pagination'],
            'documentStats' => $documentState['stats'] ?? [],
            'documentFilters' => $documentState['filters'] ?? [],
            'documentCampusOptions' => $documentState['campusOptions'],
            'selectedDocumentCampus' => $documentState['selectedCampus'],
            'selectedDocumentType' => $type,
            'documentStructureFields' => $this->documentStructureFields($type),
            'isAdminSystem' => $isAdminSystem,
        ]);
    }

    public function openDocumentNotification()
    {
        $key = trim((string) $this->request->getGet('key'));
        $code = trim((string) $this->request->getGet('event'));
        $type = $this->normalizeManagedDocumentType((string) $this->request->getGet('type'));

        if ($key === '' || $code === '' || ! in_array($type, ['proposal', 'report'], true)) {
            return redirect()->to(site_url('documents'))->with('error', 'Notifikasi dokumen tidak valid.');
        }

        $event = (new EventModel())->findByCode($code);
        if (! $event || ! $this->canViewEvent($event)) {
            return redirect()->to(site_url('documents'))->with('error', 'Event dokumen tidak ditemukan.');
        }

        $this->dismissDocumentNotification($key);
        $documentLink = $this->documentLinkForNotification((int) $event['id'], $type);
        if ($documentLink === '') {
            return redirect()->to(site_url('documents'))->with('error', 'Dokumen tidak ditemukan.');
        }

        if ($this->isExternalDocumentLink($documentLink)) {
            return redirect()->to($documentLink);
        }

        return redirect()->to(site_url('events/' . $code . '/documents/' . $type . '/file'));
    }

    public function generateDocuments(string $type = 'proposal'): string
    {
        $data = $this->applicationData();
        $isAdminSystem = session('user.role') === 'Admin Sistem';
        $type = $this->normalizeDocumentType($type) ?? 'proposal';

        return $this->render('pages/generate_documents', [
            'title' => 'Generate Otomatis',
            'active' => 'generate-documents',
            'events' => $data['events'],
            'proposals' => $data['proposals'],
            'selectedDocumentType' => $type,
            'documentStructureFields' => $this->documentStructureFields($type),
            'isAdminSystem' => $isAdminSystem,
            'geminiConfigured' => (new GeminiDocumentService())->isConfigured(),
            'geminiModel' => (new GeminiDocumentService())->model(),
        ]);
    }

    public function uploadDocumentTemplate(string $code, string $type)
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('documents'))->with('error', 'Hanya Admin Sistem yang dapat upload template Proposal.');
        }

        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeDocumentType($type);
        if ($type === null) {
            return redirect()->to(site_url('documents'))->with('error', 'Jenis dokumen tidak valid.');
        }

        $file = $this->request->getFile('template_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('documents'))->with('error', 'Template belum dipilih atau gagal diupload.');
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, ['pdf', 'doc', 'docx'], true)) {
            return redirect()->to(site_url('documents'))->with('error', 'Template yang didukung saat ini: .pdf, .doc, dan .docx.');
        }

        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'document_templates';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $fileName = $type . '-' . $code . '-' . date('YmdHis') . '.' . $extension;
        $file->move($targetDir, $fileName, true);

        $document = $this->ensureDocumentRecord($type, (int) $event['id']);
        $this->documentModel($type)->update($document['id'], [
            'template_name' => $file->getClientName(),
            'template_path' => 'uploads/document_templates/' . $fileName,
            'status' => 'template siap',
            'ai_notes' => 'Template diupload oleh ' . (session('user.name') ?: 'user') . '.',
        ]);

        return redirect()->to(site_url('documents'))->with('success', strtoupper($type) . ' template berhasil diupload.');
    }

    public function generateDocument(string $code, string $type)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeDocumentType($type);
        if ($type === null) {
            return redirect()->to(site_url('documents'))->with('error', 'Jenis dokumen tidak valid.');
        }

        $document = $this->ensureDocumentRecord($type, (int) $event['id']);
        $template = $this->documentTemplateContent($type, $document);
        $service = new GeminiDocumentService();
        $structure = $this->submittedDocumentStructure($type);
        $requiredStructureCount = count($this->documentStructureFields($type));
        if (count($structure) < $requiredStructureCount) {
            return redirect()->to(site_url('generate-documents/' . $type))->withInput()->with('error', 'Ringkasan kegiatan wajib diisi sebelum generate otomatis.');
        }

        try {
            $content = $service->generateDocument($type, $event, $template, $structure);
        } catch (\Throwable $exception) {
            $this->documentModel($type)->update($document['id'], [
                'status' => 'gagal ai',
                'ai_model' => $service->model(),
                'ai_notes' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('generate-documents/' . $type))->with('error', 'Generate AI gagal: ' . $exception->getMessage());
        }

        $generatedPath = $this->writeGeneratedDocument($type, $code, $content);
        $this->documentModel($type)->update($document['id'], [
            'content' => $content,
            'generated_path' => $generatedPath,
            'generated_at' => date('Y-m-d H:i:s'),
            'ai_model' => $service->model(),
            'ai_notes' => 'Dokumen Word dibuat dengan bantuan Gemini API dari struktur isian user.',
            'status' => 'siap review ai',
        ]);

        return redirect()->to(site_url('generate-documents/' . $code . '/' . $type . '/preview'))->with('success', strtoupper($type) . ' berhasil dibuat dengan AI.');
    }

    public function updateDocumentLink(string $code, string $type)
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('documents'))->with('error', 'Hanya Admin Sistem yang dapat mengelola dokumen dari halaman Dokumen.');
        }

        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeManagedDocumentType($type);
        if (! in_array($type, ['proposal', 'report'], true)) {
            return redirect()->to(site_url('documents'))->with('error', 'Jenis dokumen tidak valid.');
        }

        $newDocumentFile = null;
        try {
            $newDocumentFile = $this->uploadedDocumentFromRequest('document_file', $event, $type);
        } catch (\RuntimeException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        if ($type === 'report') {
            $report = $this->ensureReportRecord((int) $event['id']);
            $documentLink = $newDocumentFile ?? (string) ($report['document_link'] ?? '');
            $this->reportDocumentData($report, $documentLink);
        } else {
            $document = $this->ensureDocumentRecord($type, (int) $event['id']);
            $documentLink = $newDocumentFile ?? (string) ($document['document_link'] ?? '');
            $this->documentModel($type)->update($document['id'], [
                'document_link' => $documentLink !== '' ? $documentLink : null,
                'status' => trim((string) ($this->request->getPost('status') ?: ($documentLink !== '' ? 'file tersedia' : ($document['status'] ?? 'draft')))),
                'ai_notes' => ($newDocumentFile !== null ? 'File' : 'Data') . ' dokumen diperbarui oleh ' . (session('user.name') ?: 'user') . '.',
            ]);
        }

        if ($newDocumentFile !== null) {
            $this->syncChecklistDocumentUpload((int) $event['id'], $type, $documentLink);
            $this->updateEventProgressFromChecklist((int) $event['id']);
        }

        return redirect()->to($this->documentRedirectUrl($type))->with('success', strtoupper($type) . ' berhasil diperbarui.');
    }

    public function deleteDocumentLink(string $code, string $type)
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('documents'))->with('error', 'Hanya Admin Sistem yang dapat menghapus dokumen dari halaman Dokumen.');
        }

        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('documents'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeManagedDocumentType($type);
        if (! in_array($type, ['proposal', 'report'], true)) {
            return redirect()->to(site_url('documents'))->with('error', 'Jenis dokumen tidak valid.');
        }

        if ($type === 'report') {
            $report = (new ReportModel())->where('event_id', $event['id'])->first();
            if ($report) {
                (new ReportModel())->update($report['id'], [
                    'document_link' => null,
                    'realization_status' => 'Menunggu laporan',
                ]);
            }
        } else {
            $document = (new ProposalModel())->where('event_id', $event['id'])->first();
            if ($document) {
                (new ProposalModel())->update($document['id'], [
                    'document_link' => null,
                    'status' => 'draft',
                ]);
            }
        }

        $this->clearChecklistDocumentUpload((int) $event['id'], $type);
        $this->updateEventProgressFromChecklist((int) $event['id']);

        return redirect()->to($this->documentRedirectUrl($type))->with('success', $this->eventDocumentLabel($type) . ' berhasil dihapus dari daftar dokumen.');
    }

    public function uploadEventDocument(string $code, string $type)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeEventDocumentType($type);
        if ($type === null) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Jenis dokumen tidak valid.');
        }

        if (in_array($type, ['report', 'video', 'foto'], true) && ! $this->canSubmitPostEventDocuments($this->normalizeEvent($event)) && ! $this->canManageEventChecklist($event)) {
            return redirect()->to(site_url('events/' . $code))->with('error', $this->eventDocumentLabel($type) . ' hanya dapat diupload setelah event terlaksana.');
        }

        if (in_array($type, ['proposal', 'report'], true)) {
            try {
                $documentLink = $this->uploadedDocumentFromRequest('document_file', $event, $type);
            } catch (\RuntimeException $exception) {
                return redirect()->back()->with('error', $exception->getMessage());
            }
            if ($documentLink === null) {
                return redirect()->back()->with('error', 'File ' . $this->eventDocumentLabel($type) . ' wajib diupload dengan format doc, docx, atau pdf.');
            }
        } else {
            $documentLink = trim((string) $this->request->getPost('document_link'));
            if ($documentLink === '' || filter_var($documentLink, FILTER_VALIDATE_URL) === false) {
                return redirect()->back()->with('error', 'Link dokumen wajib berupa URL valid.');
            }
        }

        $checklistItem = (new EventChecklistItemModel())
            ->where('event_id', $event['id'])
            ->where('name', $this->eventDocumentChecklistName($type))
            ->first();

        if (! $this->canManageEventChecklist($event) && (! $checklistItem || ! $this->canReportChecklistItem($checklistItem, $event))) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Anda tidak memiliki akses upload dokumen ini.');
        }

        if ($type === 'report') {
            $report = $this->ensureReportRecord((int) $event['id']);
            $currentStatus = trim((string) ($report['realization_status'] ?? ''));
            (new ReportModel())->update($report['id'], [
                'document_link' => $documentLink,
                'realization_status' => in_array($currentStatus, ['', 'Belum tersedia', 'Menunggu pelaksanaan', 'Menunggu laporan'], true)
                    ? 'Dokumen tersedia'
                    : $currentStatus,
            ]);
        } elseif ($type === 'proposal') {
            $document = $this->ensureDocumentRecord($type, (int) $event['id']);
            $this->documentModel($type)->update($document['id'], [
                'document_link' => $documentLink,
                'status' => 'file tersedia',
                'ai_notes' => 'File dokumen diperbarui dari detail event oleh ' . (session('user.name') ?: 'user') . '.',
            ]);
        }

        $this->syncChecklistDocumentUpload((int) $event['id'], $type, $documentLink);
        $this->updateEventProgressFromChecklist((int) $event['id']);

        return redirect()->to(site_url('events/' . $code))->with('success', $this->eventDocumentLabel($type) . ' berhasil diupload.');
    }

    public function downloadUploadedEventDocument(string $code, string $type)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('events'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeManagedDocumentType($type);
        if (! in_array($type, ['proposal', 'report'], true)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'Jenis dokumen tidak valid.');
        }

        $document = $type === 'report'
            ? (new ReportModel())->where('event_id', $event['id'])->first()
            : (new ProposalModel())->where('event_id', $event['id'])->first();
        $documentLink = trim((string) ($document['document_link'] ?? ''));

        if ($documentLink === '') {
            return redirect()->to(site_url('events/' . $code))->with('error', 'File dokumen belum diupload.');
        }

        if ($this->isExternalDocumentLink($documentLink)) {
            return redirect()->to($documentLink);
        }

        $path = $this->uploadedDocumentAbsolutePath($documentLink);
        if ($path === null || ! is_file($path)) {
            return redirect()->to(site_url('events/' . $code))->with('error', 'File dokumen tidak ditemukan.');
        }

        return $this->response->download($path, null)->setFileName(basename($path));
    }

    public function previewDocument(string $code, string $type): string
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        $type = $this->normalizeDocumentType($type);
        if ($type === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Jenis dokumen tidak valid.');
        }

        $document = $this->documentModel($type)->where('event_id', $event['id'])->first();
        if (! $document) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Dokumen belum dibuat.');
        }

        return $this->render('pages/document_preview', [
            'title' => strtoupper($type) . ' ' . $event['code'],
            'active' => 'documents',
            'event' => $this->normalizeEvent($event),
            'document' => $document,
            'documentType' => $type,
        ]);
    }

    public function downloadDocument(string $code, string $type)
    {
        $event = (new EventModel())->findByCode($code);
        if (! $event) {
            return redirect()->to(site_url('generate-documents'))->with('error', 'Event tidak ditemukan.');
        }

        if (! $this->canViewEvent($event)) {
            return redirect()->to(site_url('generate-documents'))->with('error', 'Event tidak ditemukan.');
        }

        $type = $this->normalizeDocumentType($type);
        if ($type === null) {
            return redirect()->to(site_url('generate-documents'))->with('error', 'Jenis dokumen tidak valid.');
        }

        $document = $this->documentModel($type)->where('event_id', $event['id'])->first();
        if (! $document || (empty($document['generated_path']) && empty($document['content']))) {
            return redirect()->to(site_url('generate-documents/' . $type))->with('error', 'Dokumen belum digenerate.');
        }

        if (! empty($document['content'])) {
            $generatedPath = $this->writeGeneratedDocument($type, $code, (string) $document['content']);
            $this->documentModel($type)->update($document['id'], [
                'generated_path' => $generatedPath,
            ]);
            $document['generated_path'] = $generatedPath;
        }

        $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $document['generated_path']);
        if (! is_file($path)) {
            return redirect()->to(site_url('generate-documents/' . $type))->with('error', 'File hasil generate tidak ditemukan.');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($extension, ['doc', 'docx'], true) && ! empty($document['content'])) {
            $generatedPath = $this->writeGeneratedDocument($type, $code, (string) $document['content']);
            $this->documentModel($type)->update($document['id'], [
                'generated_path' => $generatedPath,
            ]);
            $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $generatedPath);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'doc';

        return $this->response->download($path, null)->setFileName(strtoupper($type) . '-' . $code . '.' . $extension);
    }

    public function reports(): string
    {
        $data = $this->applicationData();
        $reportState = $this->reportState($data['reports'], $data['events']);
        $crmFollowUpRows = $this->crmFollowUpRows($data['events']);
        $crmPerformanceState = $this->crmFollowUpPerformanceState($crmFollowUpRows, $reportState);

        return $this->render('pages/reports', [
            'title' => 'Laporan',
            'active' => 'reports',
            'reports' => $reportState['reports'],
            'events' => $data['events'],
            'reportEventMap' => $reportState['eventMap'],
            'reportCampusOptions' => $reportState['campusOptions'],
            'selectedReportCampus' => $reportState['selectedCampus'],
            'selectedReportMonth' => $reportState['selectedMonth'],
            'selectedReportSort' => $reportState['selectedSort'],
            'selectedReportCategoryScope' => $reportState['selectedCategoryScope'],
            'reportPagination' => $reportState['pagination'],
            'reportChecklistLinks' => $this->reportChecklistLinks($reportState['reports'], $data['events']),
            'crmPerformance' => $crmPerformanceState['rows'],
            'crmPerformancePagination' => $crmPerformanceState['pagination'],
            'reportCrmScores' => $this->crmFollowUpRowsByEventCode($crmFollowUpRows),
            'crmReportLogs' => $this->crmReportLogRows((string) ($reportState['selectedCampus'] ?? '')),
            'isAdminSystem' => session('user.role') === 'Admin Sistem',
        ]);
    }

    public function exportReports()
    {
        $data = $this->applicationData();
        $reportState = $this->reportState($data['reports'], $data['events'], false);
        $checklistLinks = $this->reportChecklistLinks($reportState['reports'], $data['events']);
        $rows = $this->reportExportRows($reportState['reports'], $reportState['eventMap'], $checklistLinks);
        $selectedCampus = (string) ($reportState['selectedCampus'] ?? '');
        $selectedMonth = (string) ($reportState['selectedMonth'] ?? '');
        $selectedCategoryScope = (string) ($reportState['selectedCategoryScope'] ?? '');
        $scope = $selectedCampus !== '' ? $this->safeFileSegment($selectedCampus) : 'semua-kampus';
        $period = $selectedMonth !== '' ? $selectedMonth : 'semua-bulan';
        $categoryScope = $selectedCategoryScope !== '' ? '-' . $this->safeFileSegment($this->reportCategoryScopeFilename($selectedCategoryScope)) : '';
        $filename = 'laporan-kegiatan-' . $scope . '-' . $period . $categoryScope . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->reportsExcelHtml($rows, true));
    }

    public function exportCrmResults()
    {
        $selectedCampus = trim((string) $this->request->getGet('campus'));
        $rows = $this->crmResultsExportRows($selectedCampus);
        $scope = $selectedCampus !== '' ? $this->safeFileSegment($selectedCampus) : 'seluruh-kampus';
        $filename = 'data-hasil-crm-' . $scope . '-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->crmResultsExcelHtml($rows, $selectedCampus));
    }

    public function updateReport(string $code)
    {
        if (session('user.role') !== 'Admin Sistem') {
            return redirect()->to(site_url('reports'))->with('error', 'Hanya Admin Sistem yang dapat mengedit laporan.');
        }

        $report = (new ReportModel())->where('code', $code)->first();

        if (! $report) {
            return redirect()->back()->with('error', 'Laporan tidak ditemukan.');
        }

        (new ReportModel())->update($report['id'], [
            'realization_status' => $this->request->getPost('realization_status') ?: $report['realization_status'],
            'score' => min(100, max(0, (int) $this->request->getPost('score'))),
            'notes' => $this->request->getPost('notes'),
        ]);

        return redirect()->to(site_url('reports'))->with('success', 'Laporan berhasil diperbarui.');
    }

    public function oneCampus(?string $program = null): string
    {
        $requestedProgram = strtolower(trim((string) ($program ?? $this->request->getGet('program') ?? 'all')));
        if (! in_array($requestedProgram, ['ojt', 'school', 'banner', 'all'], true)) {
            $requestedProgram = 'all';
        }

        $campuses = (new CampusModel())->orderBy('name', 'ASC')->findAll();
        $visibleCampuses = $this->visibleOneCampusCampuses($campuses);
        $selectedCampusId = $this->selectedOneCampusId($campuses);
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope !== null) {
            $selectedCampusId = $campusScope > 0 ? $campusScope : null;
        }
        $schoolReports = (new OneCampusSchoolReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $bannerReports = (new OneCampusBannerReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $ojtReports = (new OneCampusOjtReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();

        $activeMap = [
            'ojt' => 'one-campus-ojt',
            'school' => 'one-campus-school',
            'banner' => 'one-campus-banner',
            'all' => 'one-campus',
        ];
        $titleMap = [
            'ojt' => 'One Campus Five OJT',
            'school' => 'One Campus Sixty School',
            'banner' => 'One Campus Five Spanduk',
            'all' => 'One Campus',
        ];

        return $this->render('pages/one_campus', [
            'title' => $titleMap[$requestedProgram] ?? 'One Campus',
            'active' => $activeMap[$requestedProgram] ?? 'one-campus',
            'selectedProgram' => $requestedProgram,
            'campuses' => $visibleCampuses,
            'schoolReports' => $this->filterOneCampusReportsByCampus($schoolReports, $selectedCampusId),
            'bannerReports' => $this->filterOneCampusReportsByCampus($bannerReports, $selectedCampusId),
            'ojtReports' => $this->filterOneCampusReportsByCampus($ojtReports, $selectedCampusId),
            'selectedOneCampusId' => $selectedCampusId,
            'selectedOneCampusName' => $this->campusNameById($campuses, $selectedCampusId),
        ]);
    }

    public function oneCampusOjt(): string
    {
        return $this->oneCampus('ojt');
    }

    public function oneCampusSchools(): string
    {
        return $this->oneCampus('school');
    }

    public function oneCampusBanners(): string
    {
        return $this->oneCampus('banner');
    }

    public function exportOneCampusOjt()
    {
        $campuses = (new CampusModel())->orderBy('name', 'ASC')->findAll();
        $selectedCampusId = $this->selectedOneCampusId($campuses);
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope !== null) {
            $selectedCampusId = $campusScope > 0 ? $campusScope : null;
        }

        $ojtReports = $this->filterOneCampusReportsByCampus(
            (new OneCampusOjtReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll(),
            $selectedCampusId
        );

        $rows = [[
            'No',
            'Kampus',
            'Nama Siswa / Peserta',
            'Asal Sekolah',
            'Jurusan',
            'No. Telepon',
            'Masa OJT',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Keterangan',
            'Tanggal Input',
        ]];

        foreach (array_values($ojtReports) as $idx => $item) {
            $rows[] = [
                (string) ($idx + 1),
                (string) ($item['campus_name'] ?? 'Tanpa kampus'),
                (string) ($item['student_name'] ?? '-'),
                (string) ($item['origin_school'] ?? '-'),
                (string) ($item['major'] ?? '-'),
                (string) ($item['phone'] ?? '-'),
                (string) ($item['ojt_period'] ?? '-'),
                (string) ($item['ojt_start_month'] ?? '-'),
                (string) ($item['ojt_end_month'] ?? '-'),
                (string) ($item['notes'] ?? '-'),
                (string) ($item['created_at'] ?? '-'),
            ];
        }

        $filename = 'laporan-one-campus-five-ojt-' . date('Ymd-His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->xlsxFromRows($rows, 'Five OJT'));
    }

    public function exportOneCampusSchools()
    {
        $campuses = (new CampusModel())->orderBy('name', 'ASC')->findAll();
        $selectedCampusId = $this->selectedOneCampusId($campuses);
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope !== null) {
            $selectedCampusId = $campusScope > 0 ? $campusScope : null;
        }

        $schoolReports = $this->filterOneCampusReportsByCampus(
            (new OneCampusSchoolReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll(),
            $selectedCampusId
        );

        $rows = [[
            'No',
            'Kampus',
            'Nama Sekolah',
            'Nama Guru / PIC',
            'Jabatan',
            'No. Telepon',
            'Perolehan PMB',
            'Keterangan',
            'Tanggal Input',
        ]];

        foreach (array_values($schoolReports) as $idx => $item) {
            $rows[] = [
                (string) ($idx + 1),
                (string) ($item['campus_name'] ?? 'Tanpa kampus'),
                (string) ($item['school_name'] ?? '-'),
                (string) ($item['teacher_name'] ?? '-'),
                (string) ($item['position'] ?? '-'),
                (string) ($item['phone'] ?? '-'),
                (string) ((int) ($item['pmb_count'] ?? 0)),
                (string) ($item['notes'] ?? '-'),
                (string) ($item['created_at'] ?? '-'),
            ];
        }

        $filename = 'laporan-one-campus-sixty-school-' . date('Ymd-His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->xlsxFromRows($rows, 'Sixty School'));
    }

    public function exportOneCampusBanners()
    {
        $campuses = (new CampusModel())->orderBy('name', 'ASC')->findAll();
        $selectedCampusId = $this->selectedOneCampusId($campuses);
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope !== null) {
            $selectedCampusId = $campusScope > 0 ? $campusScope : null;
        }

        $bannerReports = $this->filterOneCampusReportsByCampus(
            (new OneCampusBannerReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll(),
            $selectedCampusId
        );

        $rows = [[
            'No',
            'Kampus',
            'Lokasi Pemasangan',
            'Tanggal & Jam Pasang',
            'Tema Spanduk',
            'Link Foto Google Drive',
            'Tanggal Input',
        ]];

        foreach (array_values($bannerReports) as $idx => $item) {
            $rows[] = [
                (string) ($idx + 1),
                (string) ($item['campus_name'] ?? 'Tanpa kampus'),
                (string) ($item['location'] ?? '-'),
                (string) ($item['installed_at'] ?? '-'),
                (string) ($item['theme'] ?? '-'),
                (string) ($item['drive_photo_link'] ?? '-'),
                (string) ($item['created_at'] ?? '-'),
            ];
        }

        $filename = 'laporan-one-campus-five-spanduk-' . date('Ymd-His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->xlsxFromRows($rows, 'Five Spanduk'));
    }

    public function storeOneCampusSchool()
    {
        $rules = [
            'campus_id' => 'permit_empty|is_natural_no_zero',
            'school_name' => 'required|max_length[180]',
            'teacher_name' => 'required|max_length[160]',
            'phone' => 'required|max_length[40]',
            'position' => 'required|max_length[120]',
            'pmb_count' => 'permit_empty|is_natural',
            'notes' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new OneCampusSchoolReportModel())->insert([
            'campus_id' => $this->oneCampusInputCampusId(),
            'school_name' => $this->request->getPost('school_name'),
            'teacher_name' => $this->request->getPost('teacher_name'),
            'phone' => $this->request->getPost('phone'),
            'position' => $this->request->getPost('position'),
            'pmb_count' => (int) $this->request->getPost('pmb_count'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => (int) session('user.id'),
        ]);

        return redirect()->to(site_url('one-campus/schools'))->with('success', 'Laporan One Campus Sixty School berhasil disimpan.');
    }

    public function updateOneCampusSchool(int $id)
    {
        $model = new OneCampusSchoolReportModel();
        $report = $model->find($id);
        if (! $report || ! $this->canManageOneCampusReport($report)) {
            return redirect()->to(site_url('one-campus/schools'))->with('error', 'Data school tidak ditemukan atau tidak bisa diakses.');
        }

        $rules = [
            'campus_id' => 'permit_empty|is_natural_no_zero',
            'school_name' => 'required|max_length[180]',
            'teacher_name' => 'required|max_length[160]',
            'phone' => 'required|max_length[40]',
            'position' => 'required|max_length[120]',
            'pmb_count' => 'permit_empty|is_natural',
            'notes' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model->update($id, [
            'campus_id' => $this->oneCampusInputCampusId($report['campus_id'] ?? null),
            'school_name' => $this->request->getPost('school_name'),
            'teacher_name' => $this->request->getPost('teacher_name'),
            'phone' => $this->request->getPost('phone'),
            'position' => $this->request->getPost('position'),
            'pmb_count' => (int) $this->request->getPost('pmb_count'),
            'notes' => $this->request->getPost('notes'),
        ]);

        return redirect()->to(site_url('one-campus/schools'))->with('success', 'Laporan One Campus Sixty School berhasil diperbarui.');
    }

    public function deleteOneCampusSchool(int $id)
    {
        $model = new OneCampusSchoolReportModel();
        $report = $model->find($id);
        if (! $report || ! $this->canManageOneCampusReport($report)) {
            return redirect()->to(site_url('one-campus/schools'))->with('error', 'Data school tidak ditemukan atau tidak bisa diakses.');
        }

        $model->delete($id);

        return redirect()->to(site_url('one-campus/schools'))->with('success', 'Laporan One Campus Sixty School berhasil dihapus.');
    }

    public function storeOneCampusBanner()
    {
        $rules = [
            'campus_id' => 'permit_empty|is_natural_no_zero',
            'location' => 'required|max_length[220]',
            'installed_at' => 'required|valid_date[Y-m-d\TH:i]',
            'theme' => 'required|max_length[180]',
            'drive_photo_link' => 'required|valid_url_strict|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new OneCampusBannerReportModel())->insert([
            'campus_id' => $this->oneCampusInputCampusId(),
            'location' => $this->request->getPost('location'),
            'installed_at' => str_replace('T', ' ', (string) $this->request->getPost('installed_at')) . ':00',
            'theme' => $this->request->getPost('theme'),
            'drive_photo_link' => $this->request->getPost('drive_photo_link'),
            'created_by' => (int) session('user.id'),
        ]);

        return redirect()->to(site_url('one-campus/banners'))->with('success', 'Laporan One Campus Five Spanduk berhasil disimpan.');
    }

    public function updateOneCampusBanner(int $id)
    {
        $model = new OneCampusBannerReportModel();
        $report = $model->find($id);
        if (! $report || ! $this->canManageOneCampusReport($report)) {
            return redirect()->to(site_url('one-campus/banners'))->with('error', 'Data spanduk tidak ditemukan atau tidak bisa diakses.');
        }

        $rules = [
            'campus_id' => 'permit_empty|is_natural_no_zero',
            'location' => 'required|max_length[220]',
            'installed_at' => 'required|valid_date[Y-m-d\TH:i]',
            'theme' => 'required|max_length[180]',
            'drive_photo_link' => 'required|valid_url_strict|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model->update($id, [
            'campus_id' => $this->oneCampusInputCampusId($report['campus_id'] ?? null),
            'location' => $this->request->getPost('location'),
            'installed_at' => str_replace('T', ' ', (string) $this->request->getPost('installed_at')) . ':00',
            'theme' => $this->request->getPost('theme'),
            'drive_photo_link' => $this->request->getPost('drive_photo_link'),
        ]);

        return redirect()->to(site_url('one-campus/banners'))->with('success', 'Laporan One Campus Five Spanduk berhasil diperbarui.');
    }

    public function deleteOneCampusBanner(int $id)
    {
        $model = new OneCampusBannerReportModel();
        $report = $model->find($id);
        if (! $report || ! $this->canManageOneCampusReport($report)) {
            return redirect()->to(site_url('one-campus/banners'))->with('error', 'Data spanduk tidak ditemukan atau tidak bisa diakses.');
        }

        $model->delete($id);

        return redirect()->to(site_url('one-campus/banners'))->with('success', 'Laporan One Campus Five Spanduk berhasil dihapus.');
    }

    public function storeOneCampusOjt()
    {
        $rules = [
            'campus_id' => 'permit_empty|is_natural_no_zero',
            'student_name' => 'required|max_length[160]',
            'origin_school' => 'required|max_length[180]',
            'major' => 'permit_empty|max_length[120]',
            'phone' => 'permit_empty|max_length[40]',
            'ojt_start_month' => 'required|valid_date[Y-m-d]',
            'ojt_end_month' => 'required|valid_date[Y-m-d]',
            'notes' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            $period = $this->oneCampusOjtPeriodPayload();
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        (new OneCampusOjtReportModel())->insert([
            'campus_id' => $this->oneCampusInputCampusId(),
            'student_name' => $this->request->getPost('student_name'),
            'origin_school' => $this->request->getPost('origin_school'),
            'major' => $this->request->getPost('major'),
            'phone' => $this->request->getPost('phone'),
            'ojt_period' => $period['label'],
            'ojt_start_month' => $period['start'],
            'ojt_end_month' => $period['end'],
            'notes' => $this->request->getPost('notes'),
            'created_by' => (int) session('user.id'),
        ]);

        return redirect()->to(site_url('one-campus/ojt'))->with('success', 'Laporan One Campus Five OJT berhasil disimpan.');
    }

    public function updateOneCampusOjt(int $id)
    {
        $model = new OneCampusOjtReportModel();
        $report = $model->find($id);
        if (! $report || ! $this->canManageOneCampusReport($report)) {
            return redirect()->to(site_url('one-campus/ojt'))->with('error', 'Data OJT tidak ditemukan atau tidak bisa diakses.');
        }

        $rules = [
            'campus_id' => 'permit_empty|is_natural_no_zero',
            'student_name' => 'required|max_length[160]',
            'origin_school' => 'required|max_length[180]',
            'major' => 'permit_empty|max_length[120]',
            'phone' => 'permit_empty|max_length[40]',
            'ojt_start_month' => 'required|valid_date[Y-m-d]',
            'ojt_end_month' => 'required|valid_date[Y-m-d]',
            'notes' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            $period = $this->oneCampusOjtPeriodPayload();
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        $model->update($id, [
            'campus_id' => $this->oneCampusInputCampusId($report['campus_id'] ?? null),
            'student_name' => $this->request->getPost('student_name'),
            'origin_school' => $this->request->getPost('origin_school'),
            'major' => $this->request->getPost('major'),
            'phone' => $this->request->getPost('phone'),
            'ojt_period' => $period['label'],
            'ojt_start_month' => $period['start'],
            'ojt_end_month' => $period['end'],
            'notes' => $this->request->getPost('notes'),
        ]);

        return redirect()->to(site_url('one-campus/ojt'))->with('success', 'Laporan One Campus Five OJT berhasil diperbarui.');
    }

    public function deleteOneCampusOjt(int $id)
    {
        $model = new OneCampusOjtReportModel();
        $report = $model->find($id);
        if (! $report || ! $this->canManageOneCampusReport($report)) {
            return redirect()->to(site_url('one-campus/ojt'))->with('error', 'Data OJT tidak ditemukan atau tidak bisa diakses.');
        }

        $model->delete($id);

        return redirect()->to(site_url('one-campus/ojt'))->with('success', 'Laporan One Campus Five OJT berhasil dihapus.');
    }

    public function analytics(): string
    {
        $data = $this->applicationData();
        $selectedCampusId = $this->selectedOneCampusId($data['campuses']);
        $campusScope = $this->currentEventCampusScope();
        if ($campusScope !== null) {
            $selectedCampusId = $campusScope > 0 ? $campusScope : null;
        }
        $selectedYear = $this->selectedAnalyticsYear();
        $selectedMonth = $this->selectedAnalyticsMonth();
        $events = $this->filterEventsByCampus($data['events'], $selectedCampusId);
        $events = $this->filterEventsByYear($events, $selectedYear);
        $events = $this->filterEventsByMonth($events, $selectedMonth);
        $eventCodes = array_fill_keys(array_map(static fn (array $event): string => (string) $event['id'], $events), true);
        $reports = array_values(array_filter($data['reports'], static fn (array $report): bool => isset($eventCodes[(string) ($report['event_id'] ?? '')])));
        $crmRows = $this->crmFollowUpRows($events);
        $crmSummary = $this->analyticsCrmSummary($crmRows);
        $allSchoolReports = (new OneCampusSchoolReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $allOjtReports = (new OneCampusOjtReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $allBannerReports = (new OneCampusBannerReportModel())->withCampus()->orderBy('installed_at', 'DESC')->findAll();
        $schoolReports = $this->filterOneCampusAnalyticsRows(
            $allSchoolReports,
            $selectedCampusId,
            $selectedYear,
            $selectedMonth,
            'created_at'
        );
        $ojtReports = $this->filterOneCampusAnalyticsRows(
            $allOjtReports,
            $selectedCampusId,
            $selectedYear,
            $selectedMonth,
            'created_at'
        );
        $bannerReports = $this->filterOneCampusAnalyticsRows(
            $allBannerReports,
            $selectedCampusId,
            $selectedYear,
            $selectedMonth,
            'installed_at'
        );

        return $this->render('pages/analytics', [
            'title' => 'Analytics',
            'active' => 'analytics',
            'stats' => $this->analyticsStats($events, $reports, $crmSummary),
            'eventDistribution' => $this->analyticsEventDistribution($events, $data['categoryOptions']),
            'oneCampusSections' => $this->analyticsOneCampusSections($schoolReports, $ojtReports, $bannerReports),
            'events' => $events,
            'campuses' => $data['campuses'],
            'selectedAnalyticsCampusId' => $selectedCampusId,
            'selectedAnalyticsCampusName' => $selectedCampusId === null ? 'Semua kampus' : $this->campusNameById($data['campuses'], $selectedCampusId),
            'selectedAnalyticsYear' => $selectedYear,
            'selectedAnalyticsMonth' => $selectedMonth,
            'analyticsYears' => $this->analyticsYears($data['events'], $allSchoolReports, $allOjtReports, $allBannerReports),
            'selectedAnalyticsPeriodLabel' => $this->analyticsPeriodLabel($selectedYear, $selectedMonth),
            'crmSummary' => $crmSummary,
            'crmRows' => $this->analyticsCrmRows($crmRows),
        ]);
    }

    public function specialAnalytics(): string
    {
        if (! in_array((string) session('user.role'), ['Admin Sistem', 'DMER'], true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Role Anda tidak memiliki akses ke Analisis Khusus.');
        }

        $data = $this->applicationData();
        $allSchoolReports = (new OneCampusSchoolReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $allOjtReports = (new OneCampusOjtReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll();
        $allBannerReports = (new OneCampusBannerReportModel())->withCampus()->orderBy('installed_at', 'DESC')->findAll();
        $periodType = $this->selectedSpecialAnalyticsPeriodType();
        $selectedYear = $this->selectedAnalyticsYear() ?: date('Y');
        $selectedMonth = $periodType === 'month' ? ($this->selectedAnalyticsMonth() ?: date('m')) : '';
        $state = $this->specialAnalyticsState($data['events'], $data['campuses'], $selectedYear, $selectedMonth);
        $crmRows = $this->crmFollowUpRows($state['eventsInPeriod']);
        $crmSummary = $this->analyticsCrmSummary($crmRows);

        return $this->render('pages/special_analytics', [
            'title' => 'Analisis Khusus',
            'active' => 'special-analytics',
            'periodType' => $periodType,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'analyticsYears' => $this->analyticsYears($data['events'], $allSchoolReports, $allOjtReports, $allBannerReports),
            'periodLabel' => $this->analyticsPeriodLabel($selectedYear, $selectedMonth),
            'summary' => $state['summary'],
            'campusRows' => $state['campusRows'],
            'notifications' => $state['notifications'],
            'monthLabels' => $state['monthLabels'],
            'crmSummary' => $crmSummary,
            'crmRows' => $this->analyticsCrmRows($crmRows),
        ]);
    }

    public function exportSpecialStrategyReport()
    {
        if (! in_array((string) session('user.role'), ['Admin Sistem', 'DMER'], true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Role Anda tidak memiliki akses download Analisis Khusus.');
        }

        $year = $this->selectedAnalyticsYear() ?: date('Y');
        $month = $this->selectedAnalyticsMonth() ?: date('m');
        $date = new \DateTimeImmutable($year . '-' . $month . '-01');
        $data = $this->applicationData();
        $schoolReports = $this->filterOneCampusAnalyticsRows(
            (new OneCampusSchoolReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll(),
            null,
            '',
            '',
            'created_at'
        );
        $ojtReports = $this->filterOjtReportsActiveInMonth(
            (new OneCampusOjtReportModel())->withCampus()->orderBy('created_at', 'DESC')->findAll(),
            $year,
            $month
        );
        $bannerReports = $this->filterOneCampusAnalyticsRows(
            (new OneCampusBannerReportModel())->withCampus()->orderBy('installed_at', 'DESC')->findAll(),
            null,
            '',
            '',
            'installed_at'
        );
        $rows = $this->specialStrategyReportRows(
            $data['events'],
            $data['campuses'],
            $schoolReports,
            $ojtReports,
            $bannerReports,
            $year,
            $month
        );
        $filename = 'laporan-5-strategi-dmer-' . $this->safeFileSegment($date->format('M-y')) . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->specialStrategyExcelHtml($rows, $date));
    }

    public function notifications(): string
    {
        $data = $this->applicationData();

        return $this->render('pages/notifications', [
            'title' => 'Notifikasi',
            'active' => 'notifications',
            'notifications' => $data['notifications'],
        ]);
    }

    public function markNotificationsRead()
    {
        (new NotificationModel())
            ->where('user_id', (int) session('user.id'))
            ->where('read_at', null)
            ->set(['read_at' => date('Y-m-d H:i:s')])
            ->update();

        return redirect()->to(site_url('notifications'))->with('success', 'Semua notifikasi ditandai dibaca.');
    }

    public function users(): string
    {
        $data = $this->applicationData();

        return $this->render('pages/users', [
            'title' => 'Users',
            'active' => 'users',
            'users' => $data['users'],
            'crmUsers' => (new CrmUserModel())
                ->select('crm_users.*, campuses.name AS campus_name, registrars.name AS registered_by_name')
                ->join('campuses', 'campuses.id = crm_users.campus_id', 'left')
                ->join('users AS registrars', 'registrars.id = crm_users.registered_by', 'left')
                ->withDeleted()
                ->orderBy('crm_users.status', 'DESC')
                ->orderBy('campuses.name', 'ASC')
                ->orderBy('crm_users.role', 'ASC')
                ->orderBy('crm_users.full_name', 'ASC')
                ->findAll(),
            'accessOptions' => $this->accessOptions(),
            'roles' => (new RoleModel())->orderBy('is_system', 'DESC')->orderBy('name', 'ASC')->findAll(),
            'campuses' => $data['campuses'],
        ]);
    }

    public function masterData(): string
    {
        return $this->render('pages/master_data', [
            'title' => 'Master Data',
            'active' => 'master-data',
            'campuses' => (new CampusModel())->withDeleted()->orderBy('name', 'ASC')->findAll(),
            'categories' => (new EventCategoryModel())->withDeleted()->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function storeCampus()
    {
        $name = $this->normalizedCampusName((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kampus wajib diisi.');
        }

        $campusModel = new CampusModel();
        $existing = $campusModel->withDeleted()->where('name', $name)->first();
        if ($existing && ! empty($existing['deleted_at'])) {
            db_connect()
                ->table('campuses')
                ->where('id', $existing['id'])
                ->update([
                    'name' => $name,
                    'created_by' => (int) session('user.id'),
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return redirect()->to(site_url('master-data'))->with('success', 'Kampus berhasil diaktifkan kembali.');
        }

        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Nama kampus sudah terdaftar.');
        }

        $ok = $campusModel->insert([
            'name' => $name,
            'created_by' => (int) session('user.id'),
        ]);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $campusModel->errors()));
        }

        return redirect()->to(site_url('master-data'))->with('success', 'Kampus pelaksana berhasil ditambahkan.');
    }

    public function updateCampus(int $id)
    {
        $campusModel = new CampusModel();
        $campus = $campusModel->withDeleted()->find($id);
        if (! $campus) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kampus tidak ditemukan.');
        }

        $name = $this->normalizedCampusName((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->to(site_url('master-data'))->with('error', 'Nama kampus wajib diisi.');
        }

        $duplicate = $campusModel->withDeleted()->where('name', $name)->where('id !=', $id)->first();
        if ($duplicate) {
            return redirect()->to(site_url('master-data'))->with('error', 'Nama kampus sudah dipakai.');
        }

        $campusModel->update($id, ['name' => $name]);

        return redirect()->to(site_url('master-data'))->with('success', 'Nama kampus berhasil diperbarui.');
    }

    public function deleteCampus(int $id)
    {
        $campusModel = new CampusModel();
        $campus = $campusModel->find($id);
        if (! $campus) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kampus tidak ditemukan atau sudah dinonaktifkan.');
        }

        $used = (new EventModel())->where('campus_id', $id)->countAllResults();
        if ($used > 0) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kampus masih dipakai oleh event dan tidak dapat dinonaktifkan.');
        }

        $usedByUsers = (new UserModel())->withDeleted()->where('campus_id', $id)->countAllResults();
        if ($usedByUsers > 0) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kampus masih dipakai oleh user dan tidak dapat dinonaktifkan.');
        }

        $campusModel->delete($id);

        return redirect()->to(site_url('master-data'))->with('success', 'Kampus berhasil dinonaktifkan.');
    }

    public function storeEventCategory()
    {
        $name = $this->normalizedCategoryName((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }

        $categoryModel = new EventCategoryModel();
        $existing = $categoryModel->withDeleted()->where('name', $name)->first();
        if ($existing && ! empty($existing['deleted_at'])) {
            db_connect()
                ->table('event_categories')
                ->where('id', $existing['id'])
                ->update([
                    'name' => $name,
                    'created_by' => (int) session('user.id'),
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return redirect()->to(site_url('master-data'))->with('success', 'Kategori event berhasil diaktifkan kembali.');
        }

        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah terdaftar.');
        }

        $ok = $categoryModel->insert([
            'name' => $name,
            'created_by' => (int) session('user.id'),
        ]);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $categoryModel->errors()));
        }

        return redirect()->to(site_url('master-data'))->with('success', 'Kategori event berhasil ditambahkan.');
    }

    public function updateEventCategory(int $id)
    {
        $categoryModel = new EventCategoryModel();
        $category = $categoryModel->withDeleted()->find($id);
        if (! $category) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kategori tidak ditemukan.');
        }

        $name = $this->normalizedCategoryName((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->to(site_url('master-data'))->with('error', 'Nama kategori wajib diisi.');
        }

        $duplicate = $categoryModel->withDeleted()->where('name', $name)->where('id !=', $id)->first();
        if ($duplicate) {
            return redirect()->to(site_url('master-data'))->with('error', 'Nama kategori sudah dipakai.');
        }

        $oldName = (string) $category['name'];
        $categoryModel->update($id, ['name' => $name]);

        if ($oldName !== $name) {
            db_connect()
                ->table('events')
                ->where('category', $oldName)
                ->update([
                    'category' => $name,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        return redirect()->to(site_url('master-data'))->with('success', 'Kategori event berhasil diperbarui.');
    }

    public function deleteEventCategory(int $id)
    {
        $categoryModel = new EventCategoryModel();
        $category = $categoryModel->find($id);
        if (! $category) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kategori tidak ditemukan atau sudah dinonaktifkan.');
        }

        $used = (new EventModel())->where('category', $category['name'])->countAllResults();
        if ($used > 0) {
            return redirect()->to(site_url('master-data'))->with('error', 'Kategori masih dipakai oleh event dan tidak dapat dinonaktifkan.');
        }

        $categoryModel->delete($id);

        return redirect()->to(site_url('master-data'))->with('success', 'Kategori event berhasil dinonaktifkan.');
    }

    public function storeUser()
    {
        $password = (string) $this->request->getPost('password');

        if ($password === '') {
            $password = 'password';
        }

        $role = $this->activeRoleByName((string) $this->request->getPost('role'));
        if (! $role) {
            return redirect()->back()->withInput()->with('error', 'Kategori role tidak valid atau sudah dihapus.');
        }

        $userModel = new UserModel();
        $permissions = $this->selectedPermissions('access_permissions', $this->permissionsFromRole($role));
        $campusId = $this->userCampusIdForRole($role['name']);
        $newStatus = (string) ($this->request->getPost('status') ?: 'Aktif');
        if ($this->roleRequiresCampus($role['name']) && $newStatus === 'Aktif' && $campusId === null) {
            return redirect()->back()->withInput()->with('error', 'Pilih kampus pelaksana untuk role ' . $role['name'] . '.');
        }

        $ok = $userModel->insert([
            'name' => $this->request->getPost('name'),
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role['name'],
            'campus_id' => $campusId,
            'access_permissions' => json_encode($permissions),
            'status' => $newStatus,
        ]);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $userModel->errors()));
        }

        return redirect()->to(site_url('users'))->with('success', 'User baru berhasil dibuat.');
    }

    public function updateUser(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $newRole = (string) $this->request->getPost('role');
        $role = $this->activeRoleByName($newRole);
        if (! $role) {
            return redirect()->to(site_url('users'))->with('error', 'Kategori role tidak valid atau sudah dihapus.');
        }

        $newRole = $role['name'];
        $newStatus = (string) $this->request->getPost('status');
        $permissions = $this->selectedPermissions('access_permissions', $this->permissionsFromRole($role));
        $campusId = $this->userCampusIdForRole($newRole, $user['campus_id'] ?? null);
        if ($this->roleRequiresCampus($newRole) && $newStatus === 'Aktif' && $campusId === null) {
            return redirect()->to(site_url('users'))->with('error', 'Pilih kampus pelaksana untuk role ' . $newRole . '.');
        }

        if ((int) session('user.id') === $id && ($newRole !== 'Admin Sistem' || $newStatus !== 'Aktif' || ! in_array('users', $permissions, true))) {
            return redirect()->to(site_url('users'))->with('error', 'Anda tidak dapat mengubah role/status atau menghapus akses Users dari akun admin yang sedang dipakai.');
        }

        if ($user['role'] === 'Admin Sistem' && ($newRole !== 'Admin Sistem' || $newStatus !== 'Aktif') && $this->activeAdminCount($id) === 0) {
            return redirect()->to(site_url('users'))->with('error', 'Minimal harus ada satu Admin Sistem aktif.');
        }

        $newEmail = strtolower(trim((string) $this->request->getPost('email')));
        $newName = (string) $this->request->getPost('name');
        $newPassword = (string) $this->request->getPost('password');
        $shouldEmailActivation = ($user['status'] ?? '') === 'Review' && $newStatus === 'Aktif';

        if ($newPassword !== '' && strlen($newPassword) < 6) {
            return redirect()->to(site_url('users'))->withInput()->with('error', 'Password baru minimal 6 karakter.');
        }

        $userData = [
            'id' => $id,
            'name' => $newName,
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'email' => $newEmail,
            'role' => $newRole,
            'campus_id' => $campusId,
            'access_permissions' => json_encode($permissions),
            'status' => $newStatus,
        ];

        if ($newPassword !== '') {
            $userData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $ok = $userModel->update($id, $userData);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $userModel->errors()));
        }

        $emailSent = true;
        if ($shouldEmailActivation) {
            $emailSent = $this->sendUserActivationEmail($newEmail, $newName);

            (new NotificationModel())->insert([
                'user_id' => $id,
                'type' => 'user',
                'message' => 'Akun Anda telah aktif dan dapat digunakan untuk login.',
            ]);
        }

        if ((int) session('user.id') === $id) {
            session()->put('user.name', $newName);
            session()->put('user.username', strtolower(trim((string) $this->request->getPost('username'))));
            session()->put('user.email', $newEmail);
            session()->put('user.role', $newRole);
            session()->put('user.campus_id', $campusId);
            session()->put('user.permissions', $newRole === 'Admin Sistem' ? array_keys($this->accessOptions()) : $permissions);
        }

        $message = 'User berhasil diperbarui.';
        if ($shouldEmailActivation) {
            $message .= $emailSent ? ' Email aktivasi telah dikirim ke user.' : ' Namun email aktivasi gagal dikirim. Periksa konfigurasi email.';
        }

        return redirect()->to(site_url('users'))->with($emailSent ? 'success' : 'error', $message);
    }

    public function deleteUser(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User tidak ditemukan atau sudah dihapus.');
        }

        if ((int) session('user.id') === $id) {
            return redirect()->to(site_url('users'))->with('error', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
        }

        if ($user['role'] === 'Admin Sistem' && $this->activeAdminCount($id) === 0) {
            return redirect()->to(site_url('users'))->with('error', 'Minimal harus ada satu Admin Sistem aktif.');
        }

        $userModel->update($id, ['status' => 'Nonaktif']);
        $userModel->delete($id);

        return redirect()->to(site_url('users'))->with('success', 'User berhasil dihapus secara soft delete.');
    }

    public function forceDeleteUser(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User tidak ditemukan.');
        }

        if ((int) session('user.id') === $id) {
            return redirect()->to(site_url('users'))->with('error', 'Anda tidak dapat menghapus permanen akun yang sedang dipakai.');
        }

        if (empty($user['deleted_at'])) {
            return redirect()->to(site_url('users'))->with('error', 'Nonaktifkan user terlebih dahulu sebelum menghapus permanen dari database.');
        }

        if ($user['role'] === 'Admin Sistem' && $this->activeAdminCount($id) === 0) {
            return redirect()->to(site_url('users'))->with('error', 'Minimal harus ada satu Admin Sistem aktif.');
        }

        $userModel->delete($id, true);

        return redirect()->to(site_url('users'))->with('success', 'User berhasil dihapus permanen dari database.');
    }

    public function restoreUser(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User tidak ditemukan.');
        }

        db_connect()
            ->table('users')
            ->where('id', $id)
            ->update([
                'deleted_at' => null,
                'status' => 'Aktif',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(site_url('users'))->with('success', 'User berhasil diaktifkan ulang.');
    }

    public function resetUserPassword(int $id)
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 6) {
            return redirect()->to(site_url('users'))->with('error', 'Password baru minimal 6 karakter.');
        }

        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $updated = db_connect()
            ->table('users')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->update([
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if (! $updated) {
            return redirect()->to(site_url('users'))->with('error', 'Password user gagal direset. Coba lagi.');
        }

        return redirect()->to(site_url('users'))->with('success', 'Password user berhasil direset.');
    }

    public function storeCrmUser()
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 6) {
            return redirect()->to(site_url('users'))->withInput()->with('error', 'Password akun CRM minimal 6 karakter.');
        }

        $model = new CrmUserModel();
        $ok = $model->insert([
            'username' => strtolower(trim((string) ($this->request->getPost('crm_username') ?: $this->request->getPost('username')))),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'role' => $this->normalizedCrmRole((string) $this->request->getPost('role')),
            'status' => $this->normalizedCrmStatus((string) $this->request->getPost('status')),
            'campus_id' => $this->optionalCrmCampusId(),
            'registered_by' => (int) session('user.id'),
        ]);

        if (! $ok) {
            return redirect()->to(site_url('users'))->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(site_url('users'))->with('success', 'Akun CRM berhasil dibuat.');
    }

    public function updateCrmUser(int $id)
    {
        $model = new CrmUserModel();
        $user = $model->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'Akun CRM tidak ditemukan.');
        }

        $ok = $model->update($id, [
            'id' => $id,
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'role' => $this->normalizedCrmRole((string) $this->request->getPost('role')),
            'status' => $this->normalizedCrmStatus((string) $this->request->getPost('status')),
            'campus_id' => $this->optionalCrmCampusId($user['campus_id'] ?? null),
        ]);

        if (! $ok) {
            return redirect()->to(site_url('users'))->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(site_url('users'))->with('success', 'Akun CRM berhasil diperbarui.');
    }

    public function deleteCrmUser(int $id)
    {
        $model = new CrmUserModel();
        $user = $model->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'Akun CRM tidak ditemukan atau sudah dihapus.');
        }

        if (($user['status'] ?? '') !== 'Inactive') {
            return redirect()->to(site_url('users'))->with('error', 'Nonaktifkan akun CRM terlebih dahulu sebelum menghapus.');
        }

        $model->delete($id);

        return redirect()->to(site_url('users'))->with('success', 'Akun CRM berhasil dihapus.');
    }

    public function forceDeleteCrmUser(int $id)
    {
        $model = new CrmUserModel();
        $user = $model->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'Akun CRM tidak ditemukan.');
        }

        if ((int) session('crmUser.id') === $id) {
            return redirect()->to(site_url('users'))->with('error', 'Anda tidak dapat menghapus permanen akun CRM yang sedang dipakai.');
        }

        if (empty($user['deleted_at'])) {
            return redirect()->to(site_url('users'))->with('error', 'Nonaktifkan akun CRM terlebih dahulu sebelum menghapus dari database.');
        }

        $model->delete($id, true);

        return redirect()->to(site_url('users'))->with('success', 'Akun CRM berhasil dihapus permanen dari database.');
    }

    public function restoreCrmUser(int $id)
    {
        $model = new CrmUserModel();
        $user = $model->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'Akun CRM tidak ditemukan.');
        }

        db_connect()
            ->table('crm_users')
            ->where('id', $id)
            ->update([
                'deleted_at' => null,
                'status' => 'Active',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(site_url('users'))->with('success', 'Akun CRM berhasil diaktifkan ulang.');
    }

    public function resetCrmUserPassword(int $id)
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 6) {
            return redirect()->to(site_url('users'))->with('error', 'Password CRM baru minimal 6 karakter.');
        }

        $model = new CrmUserModel();
        $user = $model->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'Akun CRM tidak ditemukan.');
        }

        $updated = db_connect()
            ->table('crm_users')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->update([
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if (! $updated) {
            return redirect()->to(site_url('users'))->with('error', 'Password akun CRM gagal direset. Coba lagi.');
        }

        return redirect()->to(site_url('users'))->with('success', 'Password akun CRM berhasil direset.');
    }

    public function storeRole()
    {
        $roleModel = new RoleModel();
        $name = $this->normalizedRoleName((string) $this->request->getPost('name'));
        $permissions = $this->selectedPermissions('default_permissions');

        $ok = $roleModel->insert([
            'name' => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'is_system' => 0,
            'default_permissions' => json_encode($permissions),
        ]);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $roleModel->errors()));
        }

        return redirect()->to(site_url('users'))->with('success', 'Kategori role berhasil ditambahkan.');
    }

    public function updateRole(int $id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);

        if (! $role) {
            return redirect()->to(site_url('users'))->with('error', 'Kategori role tidak ditemukan.');
        }

        $name = $role['name'];
        if ((int) $role['is_system'] !== 1) {
            $name = $this->normalizedRoleName((string) $this->request->getPost('name'));
        }

        $permissions = $role['name'] === 'Admin Sistem'
            ? array_keys($this->accessOptions())
            : $this->selectedPermissions('default_permissions');

        $ok = $roleModel->update($id, [
            'id' => $id,
            'name' => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'default_permissions' => json_encode($permissions),
        ]);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $roleModel->errors()));
        }

        if ($name !== $role['name']) {
            db_connect()
                ->table('users')
                ->where('role', $role['name'])
                ->update([
                    'role' => $name,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        return redirect()->to(site_url('users'))->with('success', 'Kategori role berhasil diperbarui.');
    }

    public function deleteRole(int $id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);

        if (! $role) {
            return redirect()->to(site_url('users'))->with('error', 'Kategori role tidak ditemukan atau sudah dihapus.');
        }

        if ((int) $role['is_system'] === 1 || $role['name'] === 'Admin Sistem') {
            return redirect()->to(site_url('users'))->with('error', 'Role sistem tidak dapat dihapus.');
        }

        $usersInRole = (new UserModel())->withDeleted()->where('role', $role['name'])->countAllResults();
        if ($usersInRole > 0) {
            return redirect()->to(site_url('users'))->with('error', 'Role masih dipakai oleh user. Pindahkan user ke role lain terlebih dahulu.');
        }

        $roleModel->delete($id, true);

        return redirect()->to(site_url('users'))->with('success', 'Kategori role berhasil dihapus.');
    }

    private function render(string $view, array $data = []): string
    {
        $data['content'] = view($view, $data);
        $data['currentUser'] = session('user') ?: [
            'name' => 'Guest',
            'role' => 'Guest',
            'email' => '',
            'permissions' => [],
        ];
        $data['demoRoles'] = (new RoleModel())->orderBy('is_system', 'DESC')->orderBy('name', 'ASC')->findAll();

        return view('layouts/dashboard', $data);
    }

    private function manageableTaskEvents(): array
    {
        $builder = (new EventModel())->withCreator()->orderBy('events.event_date', 'DESC');
        $this->applyEventCampusScope($builder);
        $events = $builder->findAll();

        return array_values(array_filter($events, fn (array $event): bool => $this->canManageEventChecklist($event)));
    }

    private function applicationData(): array
    {
        $this->completePastApprovedEvents();
        $eventBuilder = (new EventModel())->withCreator()->orderBy('event_date', 'ASC');
        $this->applyEventCampusScope($eventBuilder);
        $rawEvents = $eventBuilder->findAll();
        foreach ($rawEvents as $rawEvent) {
            $this->eventCodeCache[(int) $rawEvent['id']] = (string) $rawEvent['code'];
        }
        $events = array_map(fn (array $event) => $this->normalizeEvent($event), $rawEvents);
        $campuses = $this->visibleCampuses((new CampusModel())->orderBy('name', 'ASC')->findAll());
        $categoryOptions = (new EventCategoryModel())->orderBy('name', 'ASC')->findAll();
        $users = (new UserModel())->withDeleted()->orderBy('name', 'ASC')->findAll();
        $proposals = array_map(fn (array $item) => $this->normalizeDocument($item), (new ProposalModel())->findAll());
        $reports = $this->reportsForCompletedEvents($events);
        $calendarIntegrations = (new CalendarIntegrationModel())->findAll();
        $notifications = (new NotificationModel())
            ->where('user_id', (int) session('user.id'))
            ->orWhere('user_id', 1)
            ->orderBy('created_at', 'DESC')
            ->findAll(8);

        return [
            'stats' => $this->stats($rawEvents, $reports),
            'categories' => $this->categories($rawEvents),
            'categoryOptions' => $categoryOptions,
            'campuses' => $campuses,
            'users' => $users,
            'events' => $events,
            'proposals' => $proposals,
            'reports' => $reports,
            'notifications' => $notifications,
            'activities' => $this->activities($events, $reports),
            'calendarIntegrations' => $this->calendarIntegrationsByEvent($calendarIntegrations),
        ];
    }

    private function completePastApprovedEvents(): void
    {
        db_connect()
            ->table('events')
            ->where('status', 'disetujui')
            ->groupStart()
                ->where('event_end_date <', date('Y-m-d'))
                ->orGroupStart()
                    ->where('event_end_date', null)
                    ->where('event_date <', date('Y-m-d'))
                ->groupEnd()
            ->groupEnd()
            ->where('deleted_at', null)
            ->update([
                'status' => 'selesai',
                'progress' => 100,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function eventDateRangeInput(): array
    {
        $start = trim((string) $this->request->getPost('event_date'));
        $end = trim((string) $this->request->getPost('event_end_date'));
        $usesRange = (string) $this->request->getPost('use_date_range') === '1';

        if (! $usesRange) {
            return ['start' => $start, 'end' => null, 'error' => ''];
        }

        if ($end === '') {
            return ['start' => $start, 'end' => null, 'error' => 'Tanggal akhir pelaksanaan wajib diisi untuk event rentang hari.'];
        }

        if ($start !== '' && $end < $start) {
            return ['start' => $start, 'end' => null, 'error' => 'Tanggal akhir pelaksanaan tidak boleh lebih awal dari tanggal awal pelaksanaan.'];
        }

        return ['start' => $start, 'end' => $end, 'error' => ''];
    }

    private function normalizeEvent(array $event): array
    {
        $eventDate = (string) ($event['event_date'] ?? '');
        $eventEndDate = (string) ($event['event_end_date'] ?? '');
        $campus = (string) ($event['campus_name'] ?? 'Belum ditentukan');

        return [
            'id' => $event['code'],
            'db_id' => $event['id'],
            'title' => $event['title'],
            'category' => $event['category'],
            'campus_id' => $event['campus_id'] ?? null,
            'campus' => $campus,
            'calendar_campus' => $this->calendarCampusKey($campus),
            'date' => $eventDate,
            'end_date' => $eventEndDate,
            'date_label' => $this->eventDateLabel($eventDate, $eventEndDate),
            'date_compact_label' => $this->eventDateCompactLabel($eventDate, $eventEndDate),
            'time' => substr((string) $event['event_time'], 0, 5),
            'location' => $event['location'],
            'status' => strtolower(trim((string) $event['status'])),
            'created_by' => $event['creator_name'] ?? $event['created_by'],
            'participants_count' => (int) $event['participants_count'],
            'budget' => 'Rp ' . number_format((float) $event['budget'], 0, ',', '.'),
            'progress' => (int) $event['progress'],
            'description' => $event['description'] ?? '',
            'can_delete' => $this->canDeleteEvent($event),
        ];
    }

    private function eventDateLabel(string $start, ?string $end = null): string
    {
        if ($start === '') {
            return '-';
        }

        $startLabel = date('d M Y', strtotime($start));
        $end = trim((string) $end);

        if ($end === '' || $end === $start) {
            return $startLabel;
        }

        return $startLabel . ' - ' . date('d M Y', strtotime($end));
    }

    private function eventDateCompactLabel(string $start, ?string $end = null): string
    {
        if ($start === '') {
            return '-';
        }

        $startLabel = date('d M', strtotime($start));
        $end = trim((string) $end);

        if ($end === '' || $end === $start) {
            return $startLabel;
        }

        return $startLabel . ' - ' . date('d M', strtotime($end));
    }

    private function normalizeDocument(array $item): array
    {
        return [
            'id' => $item['code'],
            'db_id' => $item['id'],
            'event_id' => $this->eventCode((int) $item['event_id']),
            'status' => $item['status'],
            'content' => $item['content'] ?? '',
            'document_link' => $item['document_link'] ?? '',
            'template_name' => $item['template_name'] ?? null,
            'template_path' => $item['template_path'] ?? null,
            'generated_path' => $item['generated_path'] ?? null,
            'generated_at' => $item['generated_at'] ?? null,
            'ai_model' => $item['ai_model'] ?? null,
            'ai_notes' => $item['ai_notes'] ?? null,
            'updated_at' => $item['updated_at'] ?? '',
        ];
    }

    private function selectedEventFilters(): array
    {
        $category = trim((string) $this->request->getGet('category'));
        $status = trim((string) $this->request->getGet('status'));
        if (strtolower($category) === 'semua kategori') {
            $category = '';
        }
        if (strtolower($status) === 'semua status') {
            $status = '';
        }

        return [
            'q' => trim((string) $this->request->getGet('q')),
            'category' => $category,
            'status' => $status,
        ];
    }

    private function eventState(array $events, array $filters = []): array
    {
        $events = $this->filterEventList($events, $filters);

        usort($events, static function (array $left, array $right): int {
            return strcmp((string) ($right['date'] ?? ''), (string) ($left['date'] ?? ''))
                ?: strcmp((string) ($right['id'] ?? ''), (string) ($left['id'] ?? ''));
        });

        $perPage = 10;
        $total = count($events);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        return [
            'events' => array_slice($events, $offset, $perPage),
            'pagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $total),
            ],
        ];
    }

    private function filterEventList(array $events, array $filters): array
    {
        $query = strtolower(trim((string) ($filters['q'] ?? '')));
        $category = trim((string) ($filters['category'] ?? ''));
        $status = strtolower(trim((string) ($filters['status'] ?? '')));

        return array_values(array_filter($events, static function (array $event) use ($query, $category, $status): bool {
            if ($category !== '' && (string) ($event['category'] ?? '') !== $category) {
                return false;
            }

            if ($status !== '' && (string) ($event['status'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = strtolower(implode(' ', [
                (string) ($event['id'] ?? ''),
                (string) ($event['title'] ?? ''),
                (string) ($event['category'] ?? ''),
                (string) ($event['campus'] ?? ''),
                (string) ($event['location'] ?? ''),
                (string) ($event['created_by'] ?? ''),
                (string) ($event['status'] ?? ''),
            ]));

            return str_contains($haystack, $query);
        }));
    }

    private function approvalState(array $events): array
    {
        $statusCounts = [
            'draft' => 0,
            'diajukan' => 0,
            'disetujui' => 0,
            'ditolak' => 0,
            'selesai' => 0,
        ];

        foreach ($events as $event) {
            $status = (string) ($event['status'] ?? '');
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        usort($events, static function (array $left, array $right): int {
            $leftIsPending = ((string) ($left['status'] ?? '') === 'diajukan') ? 1 : 0;
            $rightIsPending = ((string) ($right['status'] ?? '') === 'diajukan') ? 1 : 0;

            return ($rightIsPending <=> $leftIsPending)
                ?: strcmp((string) ($right['date'] ?? ''), (string) ($left['date'] ?? ''))
                ?: strcmp((string) ($right['id'] ?? ''), (string) ($left['id'] ?? ''));
        });

        $perPage = 10;
        $total = count($events);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        return [
            'events' => array_slice($events, $offset, $perPage),
            'pagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $total),
            ],
            'statusCounts' => $statusCounts,
        ];
    }

    private function normalizeReport(array $item): array
    {
        return [
            'id' => $item['code'],
            'db_id' => $item['id'],
            'event_id' => $this->eventCode((int) $item['event_id']),
            'realization_status' => $item['realization_status'],
            'score' => (int) $item['score'],
            'notes' => $item['notes'] ?? '',
            'document_link' => $item['document_link'] ?? '',
            'promotion_link' => $item['promotion_link'] ?? '',
            'publication_link' => $item['publication_link'] ?? '',
            'updated_at' => $item['updated_at'] ?? '',
        ];
    }

    private function optionalCampusId(): ?int
    {
        $campusId = (int) $this->request->getPost('campus_id');

        if ($campusId <= 0) {
            return null;
        }

        return (new CampusModel())->find($campusId) ? $campusId : null;
    }

    private function oneCampusInputCampusId(mixed $fallback = null): ?int
    {
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope !== null) {
            return $campusScope > 0 ? $campusScope : null;
        }

        $campusId = (int) ($this->request->getPost('campus_id') ?: $fallback);
        if ($campusId <= 0) {
            return null;
        }

        return (new CampusModel())->find($campusId) ? $campusId : null;
    }

    private function canManageOneCampusReport(array $report): bool
    {
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope === null) {
            return true;
        }

        return $campusScope > 0 && (int) ($report['campus_id'] ?? 0) === $campusScope;
    }

    private function currentOneCampusScope(): ?int
    {
        $role = (string) session('user.role');
        if (in_array($role, ['Admin Sistem', 'DMER'], true)) {
            return null;
        }

        if ($this->isGlobalMarkomCampus()) {
            return null;
        }

        $campusId = (int) session('user.campus_id');
        if ($campusId > 0) {
            return $campusId;
        }

        return $this->roleRequiresCampus($role) ? 0 : null;
    }

    private function visibleOneCampusCampuses(array $campuses): array
    {
        $campusScope = $this->currentOneCampusScope();
        if ($campusScope === null) {
            return $campuses;
        }

        return array_values(array_filter($campuses, static fn (array $campus): bool => (int) ($campus['id'] ?? 0) === $campusScope));
    }

    private function oneCampusOjtPeriodPayload(): array
    {
        $startInput = (string) $this->request->getPost('ojt_start_month');
        $endInput = (string) $this->request->getPost('ojt_end_month');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startInput) !== 1 || preg_match('/^\d{4}-\d{2}-\d{2}$/', $endInput) !== 1) {
            throw new \InvalidArgumentException('Format tanggal OJT tidak valid.');
        }

        if ($endInput < $startInput) {
            throw new \InvalidArgumentException('Tanggal akhir OJT tidak boleh lebih awal dari tanggal awal.');
        }

        $startDate = new \DateTimeImmutable(substr($startInput, 0, 7) . '-01');
        $endDate = new \DateTimeImmutable(substr($endInput, 0, 7) . '-01');

        return [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
            'label' => $this->formatOjtPeriod($startDate, $endDate),
        ];
    }

    private function optionalCrmCampusId(mixed $fallback = null): ?int
    {
        $campusId = (int) ($this->request->getPost('crm_campus_id') ?: $this->request->getPost('campus_id') ?: $fallback);

        if ($campusId <= 0) {
            return null;
        }

        return (new CampusModel())->find($campusId) ? $campusId : null;
    }

    private function userCampusIdForRole(string $roleName, mixed $fallback = null): ?int
    {
        if (! $this->roleRequiresCampus($roleName)) {
            return null;
        }

        $campusId = (int) ($this->request->getPost('campus_id') ?: $fallback);
        if ($campusId <= 0) {
            return null;
        }

        return (new CampusModel())->find($campusId) ? $campusId : null;
    }

    private function roleRequiresCampus(string $roleName): bool
    {
        return strtolower($this->normalizedRoleName($roleName)) === 'kepala kampus' || $this->isMarkomRole($roleName);
    }

    private function currentEventCampusScope(): ?int
    {
        $role = (string) session('user.role');
        if (! $this->roleRequiresCampus($role)) {
            return null;
        }

        if ($this->isMarkomRole($role) && $this->isGlobalMarkomCampus()) {
            return null;
        }

        $campusId = (int) session('user.campus_id');

        return $campusId > 0 ? $campusId : 0;
    }

    private function applyEventCampusScope($builder): void
    {
        $campusScope = $this->currentEventCampusScope();
        if ($campusScope !== null) {
            $builder->where('events.campus_id', $campusScope);
        }
    }

    private function canViewEvent(array $event): bool
    {
        $campusScope = $this->currentEventCampusScope();
        if ($campusScope === null) {
            return true;
        }

        return $campusScope > 0 && (int) ($event['campus_id'] ?? 0) === $campusScope;
    }

    private function canUseEventCampus(int $campusId): bool
    {
        $campusScope = $this->currentEventCampusScope();

        return $campusScope === null || ($campusScope > 0 && $campusId === $campusScope);
    }

    private function visibleCampuses(array $campuses): array
    {
        $campusScope = $this->currentEventCampusScope();
        if ($campusScope === null) {
            return $campuses;
        }

        return array_values(array_filter($campuses, static fn (array $campus): bool => (int) ($campus['id'] ?? 0) === $campusScope));
    }

    private function isGlobalMarkomCampus(): bool
    {
        if (! $this->isMarkomRole((string) session('user.role'))) {
            return false;
        }

        $campusId = (int) session('user.campus_id');
        if ($campusId <= 0) {
            return false;
        }

        $campus = (new CampusModel())->find($campusId);
        $name = strtolower((string) ($campus['name'] ?? ''));

        return str_contains($name, 'wilayah 1') && str_contains($name, 'jabodetabek');
    }

    private function isMarkomRole(string $roleName): bool
    {
        return in_array(strtolower($this->normalizedRoleName($roleName)), ['markom', 'marketing akademik'], true);
    }

    private function selectedOneCampusId(array $campuses): ?int
    {
        $campusId = (int) $this->request->getGet('campus_id');
        if ($campusId <= 0) {
            return null;
        }

        foreach ($campuses as $campus) {
            if ((int) ($campus['id'] ?? 0) === $campusId) {
                return $campusId;
            }
        }

        return null;
    }

    private function campusNameById(array $campuses, ?int $campusId): string
    {
        if ($campusId === null) {
            return '';
        }

        foreach ($campuses as $campus) {
            if ((int) ($campus['id'] ?? 0) === $campusId) {
                return 'ID ' . $campusId . ' - ' . (string) ($campus['name'] ?? '');
            }
        }

        return '';
    }

    private function filterOneCampusReportsByCampus(array $reports, ?int $campusId): array
    {
        if ($campusId === null) {
            return $reports;
        }

        return array_values(array_filter($reports, static fn (array $report): bool => (int) ($report['campus_id'] ?? 0) === $campusId));
    }

    private function documentByEvent($model, int $eventId): ?array
    {
        $item = $model->where('event_id', $eventId)->first();

        if (! $item) {
            return null;
        }

        if ($model instanceof ReportModel) {
            return $this->normalizeReport($item);
        }

        return $this->normalizeDocument($item);
    }

    private function ensureEventChecklist(int $eventId, int $creatorId): void
    {
        $templateModel = new EventChecklistTemplateModel();
        $itemModel = new EventChecklistItemModel();
        $templates = $templateModel
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        $now = date('Y-m-d H:i:s');

        foreach ($templates as $template) {
            $exists = $itemModel
                ->where('event_id', $eventId)
                ->where('template_id', $template['id'])
                ->first();

            if ($exists) {
                continue;
            }

            $itemModel->insert([
                'event_id' => $eventId,
                'template_id' => $template['id'],
                'name' => $template['name'],
                'phase' => $template['phase'],
                'pic_user_id' => $creatorId > 0 ? $creatorId : null,
                'is_assigned' => 0,
                'assigned_at' => null,
                'deadline' => null,
                'status' => 'pending',
                'notes' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function groupChecklistItems(array $items): array
    {
        $groups = [
            'Pra Event' => [],
            'Event' => [],
            'Pasca Event' => [],
        ];

        foreach ($items as $item) {
            $phase = $item['phase'] ?: 'Pra Event';
            if (! array_key_exists($phase, $groups)) {
                $groups[$phase] = [];
            }
            $groups[$phase][] = $item;
        }

        return array_filter($groups, static fn (array $group): bool => $group !== []);
    }

    private function checklistDocumentUploads(array $items): array
    {
        $uploads = [
            'proposal' => null,
            'report' => null,
            'foto' => null,
            'video' => null,
        ];

        foreach ($items as $item) {
            $key = match (strtolower(trim((string) ($item['name'] ?? '')))) {
                'proposal' => 'proposal',
                'laporan' => 'report',
                'link dokumentasi foto' => 'foto',
                'link dokumentasi video' => 'video',
                'link upload dokumentasi after event' => 'video',
                default => null,
            };

            if ($key === null) {
                continue;
            }

            if ($uploads[$key] !== null && empty($item['drive_link'])) {
                continue;
            }

            $uploads[$key] = [
                'name' => $item['name'] ?? '',
                'status' => $item['status'] ?? 'pending',
                'drive_link' => $item['drive_link'] ?? '',
                'submitted_at' => $item['submitted_at'] ?? '',
                'pic_name' => $item['pic_name'] ?? '',
            ];
        }

        return $uploads;
    }

    private function reportChecklistLinks(array $reports, array $events): array
    {
        $eventIdsByCode = [];
        foreach ($events as $event) {
            $eventIdsByCode[$event['id']] = (int) ($event['db_id'] ?? 0);
        }

        $eventIds = [];
        foreach ($reports as $report) {
            $eventId = $eventIdsByCode[$report['event_id']] ?? 0;
            if ($eventId > 0) {
                $eventIds[] = $eventId;
            }
        }

        $eventIds = array_values(array_unique($eventIds));
        if ($eventIds === []) {
            return [];
        }

        $names = $this->completionReportChecklistNames();

        $items = (new EventChecklistItemModel())
            ->whereIn('event_id', $eventIds)
            ->whereIn('name', array_values($names))
            ->findAll();

        $linksByEventId = [];
        foreach ($items as $item) {
            $key = array_search($item['name'], $names, true);
            if ($key === false) {
                continue;
            }

            $linksByEventId[(int) $item['event_id']][$key] = [
                'label' => $item['name'],
                'link' => $item['drive_link'] ?? '',
                'status' => $item['status'] ?? 'pending',
                'submitted_at' => $item['submitted_at'] ?? '',
            ];
        }

        $linksByReport = [];
        foreach ($reports as $report) {
            $eventId = $eventIdsByCode[$report['event_id']] ?? 0;
            $linksByReport[$report['id']] = $linksByEventId[$eventId] ?? [];
        }

        return $linksByReport;
    }

    private function completionReportChecklistNames(): array
    {
        return [
            'laporan' => 'Laporan',
            'proposal' => 'Proposal',
            'foto' => 'Link Dokumentasi Foto',
            'video' => 'Link Dokumentasi Video',
        ];
    }

    private function reportsForCompletedEvents(array $events): array
    {
        $reports = [];
        $reportableEvents = array_values(array_filter(
            $events,
            fn (array $event): bool => $this->isReportableEvent($event) && (int) ($event['db_id'] ?? 0) > 0,
        ));
        $eventIds = array_values(array_unique(array_map(
            static fn (array $event): int => (int) $event['db_id'],
            $reportableEvents,
        )));
        $existingByEvent = [];

        if ($eventIds !== []) {
            foreach ((new ReportModel())->whereIn('event_id', $eventIds)->findAll() as $report) {
                $existingByEvent[(int) $report['event_id']] = $report;
            }
        }

        foreach ($reportableEvents as $event) {
            $eventId = (int) $event['db_id'];
            $report = $existingByEvent[$eventId] ?? $this->ensureCompletedEventReport($eventId);
            $status = trim((string) ($report['realization_status'] ?? ''));
            if (in_array($status, ['', 'Belum tersedia', 'Menunggu pelaksanaan'], true)) {
                (new ReportModel())->update($report['id'], [
                    'realization_status' => 'Menunggu laporan',
                    'notes' => $report['notes'] ?: 'Event telah terlaksana. Laporan dapat dilengkapi dari dokumen dan checklist pasca event.',
                ]);
                $report['realization_status'] = 'Menunggu laporan';
            }
            $reports[] = $this->normalizeReport($report);
        }

        return $reports;
    }

    private function isReportableEvent(array $event): bool
    {
        return ($event['status'] ?? '') === 'selesai';
    }

    private function canSubmitPostEventDocuments(array $event): bool
    {
        $date = (string) (($event['end_date'] ?? '') ?: ($event['date'] ?? ''));
        if (! in_array(($event['status'] ?? ''), ['disetujui', 'selesai'], true)) {
            return false;
        }

        return ($event['status'] ?? '') === 'selesai'
            || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && $date <= date('Y-m-d'));
    }

    private function reportState(array $reports, array $events, bool $paginate = true): array
    {
        $eventMap = [];
        $campusOptions = [];

        foreach ($events as $event) {
            $eventMap[$event['id']] = $event;
            $campus = (string) ($event['campus'] ?? '');
            if ($campus !== '') {
                $campusOptions[$campus] = $campus;
            }
        }

        ksort($campusOptions);

        $selectedCampus = trim((string) $this->request->getGet('campus'));
        $selectedMonth = trim((string) $this->request->getGet('month'));
        $selectedSort = trim((string) $this->request->getGet('sort')) ?: 'month_desc';
        $selectedCategoryScope = trim((string) $this->request->getGet('category_scope'));

        if (! preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = '';
        }

        $allowedCategoryScopes = ['academic', 'non_academic', 'without_academic_non_academic'];
        if (! in_array($selectedCategoryScope, $allowedCategoryScopes, true)) {
            $selectedCategoryScope = '';
        }

        $allowedSorts = ['campus_asc', 'campus_desc', 'month_asc', 'month_desc'];
        if (! in_array($selectedSort, $allowedSorts, true)) {
            $selectedSort = 'month_desc';
        }

        $filtered = array_values(array_filter($reports, function (array $report) use ($eventMap, $selectedCampus, $selectedMonth, $selectedCategoryScope): bool {
            $event = $eventMap[$report['event_id']] ?? null;
            if (! $event) {
                return false;
            }

            if ($selectedCampus !== '' && ($event['campus'] ?? '') !== $selectedCampus) {
                return false;
            }

            if ($selectedMonth !== '' && substr((string) ($event['date'] ?? ''), 0, 7) !== $selectedMonth) {
                return false;
            }

            if (! $this->reportCategoryScopeMatches((string) ($event['category'] ?? ''), $selectedCategoryScope)) {
                return false;
            }

            return true;
        }));

        usort($filtered, function (array $left, array $right) use ($eventMap, $selectedSort): int {
            $leftEvent = $eventMap[$left['event_id']] ?? [];
            $rightEvent = $eventMap[$right['event_id']] ?? [];

            return match ($selectedSort) {
                'campus_asc' => strcmp((string) ($leftEvent['campus'] ?? ''), (string) ($rightEvent['campus'] ?? '')) ?: strcmp((string) ($leftEvent['date'] ?? ''), (string) ($rightEvent['date'] ?? '')),
                'campus_desc' => strcmp((string) ($rightEvent['campus'] ?? ''), (string) ($leftEvent['campus'] ?? '')) ?: strcmp((string) ($rightEvent['date'] ?? ''), (string) ($leftEvent['date'] ?? '')),
                'month_asc' => strcmp((string) ($leftEvent['date'] ?? ''), (string) ($rightEvent['date'] ?? '')) ?: strcmp((string) ($leftEvent['campus'] ?? ''), (string) ($rightEvent['campus'] ?? '')),
                default => strcmp((string) ($rightEvent['date'] ?? ''), (string) ($leftEvent['date'] ?? '')) ?: strcmp((string) ($leftEvent['campus'] ?? ''), (string) ($rightEvent['campus'] ?? '')),
            };
        });

        $perPage = 15;
        $total = count($filtered);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $paginated = $paginate ? array_slice($filtered, $offset, $perPage) : $filtered;
        $query = [
            'campus' => $selectedCampus,
            'month' => $selectedMonth,
            'sort' => $selectedSort,
            'category_scope' => $selectedCategoryScope,
        ];
        $query = array_filter($query, static fn (string $value): bool => $value !== '');

        return [
            'reports' => $paginated,
            'eventMap' => $eventMap,
            'campusOptions' => array_values($campusOptions),
            'selectedCampus' => $selectedCampus,
            'selectedMonth' => $selectedMonth,
            'selectedSort' => $selectedSort,
            'selectedCategoryScope' => $selectedCategoryScope,
            'pagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $total),
                'query' => $query,
            ],
        ];
    }

    private function reportCategoryScopeMatches(string $category, string $scope): bool
    {
        if ($scope === '') {
            return true;
        }

        $isAcademic = $this->isAcademicReportCategory($category);
        $isNonAcademic = $this->isNonAcademicReportCategory($category);

        return match ($scope) {
            'academic' => $isAcademic,
            'non_academic' => $isNonAcademic,
            'without_academic_non_academic' => ! $isAcademic && ! $isNonAcademic,
            default => true,
        };
    }

    private function isAcademicReportCategory(string $category): bool
    {
        return $this->normalizedReportCategoryKey($category) === 'akademik';
    }

    private function isNonAcademicReportCategory(string $category): bool
    {
        return in_array($this->normalizedReportCategoryKey($category), ['nonakademik', 'nonacademic'], true);
    }

    private function normalizedReportCategoryKey(string $category): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $category));
    }

    private function reportCategoryScopeFilename(string $scope): string
    {
        return match ($scope) {
            'academic' => 'kategori-akademik',
            'non_academic' => 'kategori-non-akademik',
            'without_academic_non_academic' => 'tanpa-kategori-akademik-non-akademik',
            default => '',
        };
    }

    private function reportExportRows(array $reports, array $eventMap, array $checklistLinks): array
    {
        $rowsByDate = [];

        foreach ($reports as $report) {
            $event = $eventMap[$report['event_id']] ?? [];
            $links = $checklistLinks[$report['id']] ?? [];
            $dateKey = (string) ($event['date'] ?? $event['date_label'] ?? $report['event_id'] ?? '');
            if ($dateKey === '') {
                $dateKey = 'report-' . (string) ($report['id'] ?? count($rowsByDate));
            }

            $row = [
                'report_id' => (string) ($report['id'] ?? ''),
                'event_code' => (string) ($report['event_id'] ?? ''),
                'event_title' => (string) ($event['title'] ?? $report['event_id'] ?? ''),
                'category' => (string) ($event['category'] ?? ''),
                'campus' => (string) ($event['campus'] ?? ''),
                'event_date' => (string) ($event['date_label'] ?? $event['date'] ?? ''),
                'realization_status' => (string) ($report['realization_status'] ?? ''),
                'score' => (string) ($report['score'] ?? ''),
                'participants_count' => (string) ($event['participants_count'] ?? ''),
                'location' => (string) ($event['location'] ?? ''),
                'notes' => (string) ($report['notes'] ?? ''),
                'report_link' => (string) ($links['laporan']['link'] ?? $report['document_link'] ?? ''),
                'proposal_link' => (string) ($links['proposal']['link'] ?? ''),
                'photo_link' => (string) ($links['foto']['link'] ?? ''),
                'video_link' => (string) ($links['video']['link'] ?? ''),
                'promotion_link' => (string) ($report['promotion_link'] ?? ''),
                'publication_link' => (string) ($report['publication_link'] ?? ''),
            ];

            if (! isset($rowsByDate[$dateKey])) {
                $rowsByDate[$dateKey] = $row;
                continue;
            }

            $rowsByDate[$dateKey] = $this->mergeReportExportRows($rowsByDate[$dateKey], $row);
        }

        return array_values($rowsByDate);
    }

    private function mergeReportExportRows(array $existing, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if ($key === 'event_date') {
                continue;
            }

            $existing[$key] = $this->joinUniqueExportValues((string) ($existing[$key] ?? ''), (string) $value);
        }

        return $existing;
    }

    private function joinUniqueExportValues(string $left, string $right): string
    {
        $values = [];
        foreach ([$left, $right] as $chunk) {
            foreach (explode('; ', $chunk) as $value) {
                $value = trim($value);
                if ($value !== '' && ! in_array($value, $values, true)) {
                    $values[] = $value;
                }
            }
        }

        return implode('; ', $values);
    }

    private function crmResultsExportRows(string $selectedCampus = ''): array
    {
        $leadBuilder = (new CrmLeadModel())
            ->select('crm_leads.*, events.code AS event_code, events.title AS event_title, events.category AS event_category, events.event_date, events.event_end_date, events.location AS event_location, campuses.name AS campus_name, crm_users.full_name AS agent_name, crm_upload_batches.batch_code, crm_upload_batches.file_name')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left')
            ->join('crm_users', 'crm_users.id = crm_leads.agent_id', 'left')
            ->join('crm_upload_batches', 'crm_upload_batches.id = crm_leads.batch_id', 'left');

        if ($selectedCampus !== '') {
            $leadBuilder->where('campuses.name', $selectedCampus);
        }

        $leads = $leadBuilder
            ->orderBy('campuses.name', 'ASC')
            ->orderBy('events.event_date', 'DESC')
            ->orderBy('events.code', 'ASC')
            ->orderBy('crm_leads.id', 'ASC')
            ->findAll();
        $rows = [];
        $logsByLeadId = $this->crmLeadExportLogsByLeadId(array_column($leads, 'id'));

        foreach ($leads as $index => $lead) {
            $eventDate = (string) ($lead['event_date'] ?? '');
            $eventEndDate = (string) ($lead['event_end_date'] ?? '');
            $stage = (string) ($lead['stage'] ?? '');
            $label = trim((string) ($lead['label'] ?? ''));
            $leadId = (int) ($lead['id'] ?? 0);

            $rows[] = [
                'no' => (string) ($index + 1),
                'campus' => (string) ($lead['campus_name'] ?? 'Tanpa kampus'),
                'event_code' => (string) ($lead['event_code'] ?? ''),
                'event_title' => (string) ($lead['event_title'] ?? ''),
                'event_category' => (string) ($lead['event_category'] ?? ''),
                'event_date' => $eventDate !== '' ? $this->eventDateLabel($eventDate, $eventEndDate) : '',
                'event_location' => (string) ($lead['event_location'] ?? ''),
                'student_name' => (string) ($lead['student_name'] ?? ''),
                'school_origin' => (string) ($lead['school_origin'] ?? ''),
                'whatsapp' => (string) ($lead['whatsapp'] ?? ''),
                'stage' => $stage,
                'label' => $label,
                'result' => $this->crmLeadResultLabel($stage, $label),
                'result_score' => (string) $this->crmLeadResultScore($stage, $label),
                'agent' => (string) ($lead['agent_name'] ?? 'Belum diassign'),
                'notes' => (string) ($lead['notes'] ?? ''),
                'rejection_reason' => (string) ($lead['rejection_reason'] ?? ''),
                'responded_at' => (string) ($lead['responded_at'] ?? ''),
                'change_log' => implode("\n", $logsByLeadId[$leadId] ?? []),
                'uploaded_batch' => (string) ($lead['batch_code'] ?? ''),
                'source_file' => (string) ($lead['file_name'] ?? ''),
                'created_at' => (string) ($lead['created_at'] ?? ''),
                'updated_at' => (string) ($lead['updated_at'] ?? ''),
            ];
        }

        return $rows;
    }

    private function crmLeadExportLogsByLeadId(array $leadIds): array
    {
        $leadIds = array_values(array_filter(array_map('intval', $leadIds), static fn (int $id): bool => $id > 0));
        $db = db_connect();
        if ($leadIds === [] || ! $db->tableExists('crm_lead_logs')) {
            return [];
        }

        $logs = $db->table('crm_lead_logs')
            ->select('crm_lead_logs.*, crm_users.full_name AS actor_name')
            ->join('crm_users', 'crm_users.id = crm_lead_logs.user_id', 'left')
            ->whereIn('crm_lead_logs.lead_id', $leadIds)
            ->orderBy('crm_lead_logs.created_at', 'ASC')
            ->orderBy('crm_lead_logs.id', 'ASC')
            ->get()
            ->getResultArray();
        $mapped = [];

        foreach ($logs as $log) {
            $leadId = (int) ($log['lead_id'] ?? 0);
            if ($leadId <= 0) {
                continue;
            }

            $line = trim(implode(' - ', array_filter([
                (string) ($log['created_at'] ?? ''),
                (string) ($log['actor_name'] ?? 'User CRM'),
                (string) ($log['summary'] ?? ''),
            ], static fn (string $value): bool => $value !== '')));

            if (! empty($log['new_rejection_reason'])) {
                $line .= ' | Alasan: ' . $log['new_rejection_reason'];
            }

            $mapped[$leadId][] = $line;
        }

        return $mapped;
    }

    private function reportsExcelHtml(array $rows, bool $showCampusColumn): string
    {
        $columns = [
            'report_id' => 'Report',
            'event_code' => 'Kode Event',
            'event_title' => 'Nama Event',
            'category' => 'Kategori Event',
            'event_date' => 'Tanggal Pelaksanaan',
            'realization_status' => 'Status Realisasi',
            'score' => 'Skor',
            'participants_count' => 'Peserta',
            'location' => 'Lokasi',
            'notes' => 'Catatan',
            'report_link' => 'Laporan',
            'proposal_link' => 'Proposal',
            'photo_link' => 'Link Dokumentasi Foto',
            'video_link' => 'Link Dokumentasi Video',
            'promotion_link' => 'Link Promosi',
            'publication_link' => 'Link Publikasi',
        ];

        if ($showCampusColumn) {
            $columns = array_slice($columns, 0, 4, true)
                + ['campus' => 'Kampus Pelaksana']
                + array_slice($columns, 4, null, true);
        }

        $html = [
            '<!doctype html>',
            '<html><head><meta charset="UTF-8"></head><body>',
            '<table border="1">',
            '<thead><tr>',
        ];

        foreach ($columns as $label) {
            $html[] = '<th>' . esc($label) . '</th>';
        }

        $html[] = '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html[] = '<tr>';
            foreach (array_keys($columns) as $key) {
                $value = (string) ($row[$key] ?? '');
                if ($key === 'event_date' && $value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                    $value = date('d M Y', strtotime($value));
                }
                $html[] = '<td>' . esc($value !== '' ? $value : '-') . '</td>';
            }
            $html[] = '</tr>';
        }

        if ($rows === []) {
            $html[] = '<tr><td colspan="' . count($columns) . '">Belum ada laporan sesuai filter.</td></tr>';
        }

        $html[] = '</tbody></table></body></html>';

        return implode('', $html);
    }

    private function crmResultsExcelHtml(array $rows, string $selectedCampus = ''): string
    {
        $columns = [
            'no' => 'No',
            'campus' => 'Kampus',
            'event_code' => 'Kode Event',
            'event_title' => 'Nama Event',
            'event_category' => 'Kategori Event',
            'event_date' => 'Tanggal Pelaksanaan',
            'event_location' => 'Lokasi Event',
            'student_name' => 'Nama Siswa',
            'school_origin' => 'Asal Sekolah',
            'whatsapp' => 'WhatsApp',
            'stage' => 'Tahap CRM',
            'label' => 'Label CRM',
            'result' => 'Hasil CRM',
            'result_score' => 'Skor Hasil',
            'agent' => 'Agent CRM',
            'notes' => 'Catatan',
            'rejection_reason' => 'Alasan/Keterangan Label',
            'responded_at' => 'Tanggal Follow Up',
            'change_log' => 'Log Perubahan',
            'uploaded_batch' => 'Batch Upload',
            'source_file' => 'File Sumber',
            'created_at' => 'Tanggal Input',
            'updated_at' => 'Terakhir Update',
        ];
        $html = [
            '<!doctype html>',
            '<html><head><meta charset="UTF-8"></head><body>',
            '<table border="1">',
            '<thead>',
            '<tr><th colspan="' . count($columns) . '">Data Hasil CRM ' . esc($selectedCampus !== '' ? $selectedCampus : 'Seluruh Kampus') . '</th></tr>',
            '<tr><th colspan="' . count($columns) . '">Diekspor pada ' . esc(date('d M Y H:i')) . '</th></tr>',
            '<tr>',
        ];

        foreach ($columns as $label) {
            $html[] = '<th>' . esc($label) . '</th>';
        }

        $html[] = '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html[] = '<tr>';
            foreach (array_keys($columns) as $key) {
                $html[] = '<td>' . esc((string) (($row[$key] ?? '') !== '' ? $row[$key] : '-')) . '</td>';
            }
            $html[] = '</tr>';
        }

        if ($rows === []) {
            $html[] = '<tr><td colspan="' . count($columns) . '">Belum ada data CRM dari kampus mana pun.</td></tr>';
        }

        $html[] = '</tbody></table></body></html>';

        return implode('', $html);
    }

    private function crmLeadResultLabel(string $stage, string $label): string
    {
        if ($label !== '') {
            return $label;
        }

        return match ($stage) {
            'Tahap 3' => 'Tahap 3',
            'Tahap 2' => 'Tahap 2 belum berlabel',
            default => 'Fresh',
        };
    }

    private function crmLeadResultScore(string $stage, string $label): int
    {
        if ($label === 'Closing') {
            return 100;
        }

        if ($label === 'Menolak') {
            return 5;
        }

        if ($label === 'Tidak Merespon') {
            return 20;
        }

        if ($label !== '' || $stage === 'Tahap 2') {
            return 55;
        }

        if ($stage === 'Tahap 3') {
            return 80;
        }

        return 0;
    }

    private function eventsExcelHtml(array $events): string
    {
        $columns = [
            'id' => 'Kode Event',
            'title' => 'Nama Event',
            'category' => 'Kategori Event',
            'campus' => 'Kampus Pelaksana',
            'date' => 'Tanggal Pelaksanaan',
            'time' => 'Jam',
            'location' => 'Lokasi',
            'created_by' => 'PIC',
            'participants_count' => 'Peserta',
            'status' => 'Status',
            'progress' => 'Progress',
            'budget' => 'Anggaran',
            'description' => 'Deskripsi',
        ];

        $html = [
            '<!doctype html>',
            '<html><head><meta charset="UTF-8"></head><body>',
            '<table border="1">',
            '<thead><tr>',
        ];

        foreach ($columns as $label) {
            $html[] = '<th>' . esc($label) . '</th>';
        }

        $html[] = '</tr></thead><tbody>';

        foreach ($events as $event) {
            $html[] = '<tr>';
            foreach (array_keys($columns) as $key) {
                $value = (string) ($event[$key] ?? '');
                if ($key === 'date' && ! empty($event['date_label'])) {
                    $value = (string) $event['date_label'];
                } elseif ($key === 'date' && $value !== '') {
                    $value = date('d M Y', strtotime($value));
                }
                if ($key === 'progress' && $value !== '') {
                    $value .= '%';
                }
                $html[] = '<td>' . esc($value !== '' ? $value : '-') . '</td>';
            }
            $html[] = '</tr>';
        }

        if ($events === []) {
            $html[] = '<tr><td colspan="' . count($columns) . '">Belum ada event disetujui atau selesai.</td></tr>';
        }

        $html[] = '</tbody></table></body></html>';

        return implode('', $html);
    }

    private function eventImportColumns(): array
    {
        return [
            'code' => 'Kode event. Kosongkan untuk dibuat otomatis.',
            'title' => 'Nama event.',
            'category' => 'Nama kategori dari master data.',
            'kampus_pelaksana' => 'Nama kampus pelaksana dari master data.',
            'event_date' => 'Tanggal mulai, format YYYY-MM-DD.',
            'event_end_date' => 'Tanggal akhir, boleh kosong.',
            'event_time' => 'Jam mulai, format HH:MM.',
            'location' => 'Lokasi event.',
            'status' => 'draft, diajukan, disetujui, ditolak, atau selesai.',
            'created_by' => 'ID user PIC/pembuat event.',
            'participants_count' => 'Jumlah peserta.',
            'budget' => 'Anggaran.',
            'progress' => 'Progress 0-100.',
            'description' => 'Deskripsi event.',
            'approved_by' => 'ID user approver, boleh kosong.',
            'approved_at' => 'Waktu approval, boleh kosong.',
            'rejected_reason' => 'Alasan ditolak, boleh kosong.',
        ];
    }

    private function prepareEventImportRows(array $rows): array
    {
        $headers = $this->normalizeImportHeaders($rows[0] ?? []);
        $required = ['title', 'category', 'event_date', 'event_time', 'location'];
        $missing = array_diff($required, $headers);
        if (! in_array('campus_id', $headers, true) && ! in_array('campus_name', $headers, true)) {
            $missing[] = 'kampus_pelaksana';
        }
        if ($missing !== []) {
            return [
                'rows' => [],
                'errors' => ['Kolom wajib belum ada: ' . implode(', ', $missing) . '.'],
            ];
        }

        $categories = [];
        foreach ((new EventCategoryModel())->findAll() as $category) {
            $categoryName = (string) $category['name'];
            $categories[strtolower($this->normalizedCategoryName($categoryName))] = $categoryName;

            $categoryImportKey = $this->categoryImportKey($categoryName);
            if ($categoryImportKey !== '') {
                $categories[$categoryImportKey] = $categoryName;
            }
        }

        $campusesById = [];
        $campusesByName = [];
        foreach ((new CampusModel())->findAll() as $campus) {
            $campusId = (int) ($campus['id'] ?? 0);
            $campusName = strtolower($this->normalizedCampusName((string) ($campus['name'] ?? '')));
            $campusImportKey = $this->campusImportKey((string) ($campus['name'] ?? ''));
            if ($campusId > 0) {
                $campusesById[$campusId] = $campusId;
            }
            if ($campusName !== '') {
                $campusesByName[$campusName] = $campusId;
            }
            if ($campusImportKey !== '') {
                $campusesByName[$campusImportKey] = $campusId;
            }
        }
        $userIds = array_map(static fn (array $user): int => (int) $user['id'], (new UserModel())->withDeleted()->findAll());
        $existingCodes = array_map(
            static fn (array $event): string => strtoupper((string) $event['code']),
            (new EventModel())->withDeleted()->select('code')->findAll()
        );
        $reservedEventCodes = array_fill_keys($existingCodes, true);
        $allowedStatuses = ['draft', 'diajukan', 'disetujui', 'ditolak', 'selesai'];
        $prepared = [];
        $errors = [];
        $currentUserId = (int) session('user.id');

        foreach (array_slice($rows, 1) as $index => $values) {
            $line = $index + 2;
            if ($this->isImportRowBlank($values)) {
                continue;
            }

            $row = [];
            foreach ($headers as $columnIndex => $header) {
                $row[$header] = trim((string) ($values[$columnIndex] ?? ''));
            }

            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            if (strlen($title) < 3 && $this->isSkippableImportTitle($title)) {
                continue;
            }

            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            if ($code !== '') {
                if (strlen($code) > 30) {
                    $errors[] = 'Baris ' . $line . ': code maksimal 30 karakter.';
                }
                if (isset($reservedEventCodes[$code])) {
                    $errors[] = 'Baris ' . $line . ': code ' . $code . ' sudah dipakai.';
                }
                $reservedEventCodes[$code] = true;
            } else {
                $code = $this->nextAvailableCode('EVT', 'events', $reservedEventCodes);
            }

            if (strlen($title) < 3 || strlen($title) > 180) {
                $errors[] = 'Baris ' . $line . ': title "' . $title . '" wajib 3-180 karakter.';
            }

            $categoryKey = strtolower($this->normalizedCategoryName((string) ($row['category'] ?? '')));
            if ($categoryKey !== '' && ! isset($categories[$categoryKey])) {
                $categoryKey = $this->categoryImportKey((string) ($row['category'] ?? ''));
            }
            if ($categoryKey === '' || ! isset($categories[$categoryKey])) {
                $errors[] = 'Baris ' . $line . ': category harus sesuai master data.';
            }

            $campusId = $this->campusIdFromImportRow($row, $campusesById, $campusesByName);
            if ($campusId <= 0) {
                $campusValue = trim((string) ($row['campus_name'] ?? $row['campus_id'] ?? ''));
                $errors[] = 'Baris ' . $line . ': kampus_pelaksana "' . ($campusValue !== '' ? $campusValue : '-') . '" tidak valid.';
            }

            $eventDate = $this->normalizeImportDate((string) ($row['event_date'] ?? ''));
            if ($eventDate === null) {
                $errors[] = 'Baris ' . $line . ': event_date tidak valid.';
            }

            $eventEndDate = $this->normalizeImportDate((string) ($row['event_end_date'] ?? ''));
            if (($row['event_end_date'] ?? '') !== '' && $eventEndDate === null) {
                $errors[] = 'Baris ' . $line . ': event_end_date tidak valid.';
            }
            if ($eventDate !== null && $eventEndDate !== null && $eventEndDate < $eventDate) {
                $errors[] = 'Baris ' . $line . ': event_end_date tidak boleh lebih awal dari event_date.';
            }

            $eventTime = $this->normalizeImportTime((string) ($row['event_time'] ?? ''));
            if ($eventTime === null) {
                $errors[] = 'Baris ' . $line . ': event_time tidak valid.';
            }

            $location = trim((string) ($row['location'] ?? ''));
            if ($location === '' || strlen($location) > 180) {
                $errors[] = 'Baris ' . $line . ': location wajib diisi maksimal 180 karakter.';
            }

            $status = strtolower(trim((string) ($row['status'] ?? '')));
            $status = $status !== '' ? $status : 'draft';
            if (! in_array($status, $allowedStatuses, true)) {
                $errors[] = 'Baris ' . $line . ': status tidak valid.';
            }

            $createdBy = (int) ($row['created_by'] ?? 0);
            $createdBy = $createdBy > 0 ? $createdBy : $currentUserId;
            if (! in_array($createdBy, $userIds, true)) {
                $errors[] = 'Baris ' . $line . ': created_by tidak valid.';
            }

            $approvedBy = (int) ($row['approved_by'] ?? 0);
            if ($approvedBy > 0 && ! in_array($approvedBy, $userIds, true)) {
                $errors[] = 'Baris ' . $line . ': approved_by tidak valid.';
            }
            if ($approvedBy <= 0 && in_array($status, ['disetujui', 'selesai'], true)) {
                $approvedBy = $currentUserId;
            }

            $approvedAt = $this->normalizeImportDateTime((string) ($row['approved_at'] ?? ''));
            if (($row['approved_at'] ?? '') !== '' && $approvedAt === null) {
                $errors[] = 'Baris ' . $line . ': approved_at tidak valid.';
            }
            if ($approvedAt === null && in_array($status, ['disetujui', 'selesai'], true)) {
                $approvedAt = date('Y-m-d H:i:s');
            }

            $prepared[] = [
                'code' => $code,
                'title' => $title,
                'category' => $categories[$categoryKey] ?? (string) ($row['category'] ?? ''),
                'campus_id' => $campusId,
                'event_date' => $eventDate ?? date('Y-m-d'),
                'event_end_date' => $eventEndDate,
                'event_time' => $eventTime ?? '00:00:00',
                'location' => $location,
                'status' => $status,
                'created_by' => $createdBy,
                'participants_count' => max(0, (int) $this->numericImportValue((string) ($row['participants_count'] ?? '0'))),
                'budget' => max(0, $this->numericImportValue((string) ($row['budget'] ?? '0'))),
                'progress' => $this->progressForImportRow($row, $status),
                'description' => trim((string) ($row['description'] ?? '')),
                'approved_by' => $approvedBy > 0 ? $approvedBy : null,
                'approved_at' => $approvedAt,
                'rejected_reason' => trim((string) ($row['rejected_reason'] ?? '')) ?: null,
            ];
        }

        if ($prepared === [] && $errors === []) {
            $errors[] = 'Tidak ada baris data event yang valid untuk diimport.';
        }

        return ['rows' => $prepared, 'errors' => $errors];
    }

    private function progressForImportRow(array $row, string $status): int
    {
        $value = trim((string) ($row['progress'] ?? ''));
        if ($value !== '') {
            return max(0, min(100, (int) round($this->numericImportValue($value))));
        }

        return match ($status) {
            'diajukan' => 40,
            'disetujui' => 75,
            'selesai' => 100,
            default => 20,
        };
    }

    private function proposalStatusForEventStatus(string $status): string
    {
        return match ($status) {
            'diajukan' => 'review admin',
            'disetujui', 'selesai' => 'siap export',
            'ditolak' => 'ditolak',
            default => 'draft',
        };
    }

    private function campusIdFromImportRow(array $row, array $campusesById, array $campusesByName): int
    {
        $campusId = (int) ($row['campus_id'] ?? 0);
        if ($campusId > 0 && isset($campusesById[$campusId])) {
            return $campusId;
        }

        $campusName = strtolower($this->normalizedCampusName((string) ($row['campus_name'] ?? '')));
        if ($campusName !== '' && isset($campusesByName[$campusName])) {
            return (int) $campusesByName[$campusName];
        }

        $campusImportKey = $this->campusImportKey((string) ($row['campus_name'] ?? ''));
        if ($campusImportKey !== '' && isset($campusesByName[$campusImportKey])) {
            return (int) $campusesByName[$campusImportKey];
        }

        if ($campusImportKey !== '') {
            $matches = [];
            foreach ($campusesByName as $key => $id) {
                if ($key === '') {
                    continue;
                }

                if (str_contains($key, $campusImportKey) || str_contains($campusImportKey, $key)) {
                    $matches[(int) $id] = (int) $id;
                }
            }

            if (count($matches) === 1) {
                return (int) array_values($matches)[0];
            }
        }

        return 0;
    }

    private function campusImportKey(string $name): string
    {
        $name = strtolower($this->normalizedCampusName($name));
        $name = preg_replace('/\b(universitas|bina|sarana|informatika|ubsi|bsi|kampus|psdku)\b/', ' ', $name) ?? $name;
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? $name;

        return trim((string) preg_replace('/\s+/', ' ', $name));
    }

    private function categoryImportKey(string $name): string
    {
        $name = strtolower($this->normalizedCategoryName($name));
        $name = preg_replace('/\b(campus|online|nice)\b/', ' ', $name) ?? $name;
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? $name;

        return trim((string) preg_replace('/\s+/', ' ', $name));
    }

    private function readEventCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return [];
        }

        $rows = [];
        $delimiter = null;
        while (($line = fgetcsv($handle)) !== false) {
            if ($delimiter === null) {
                $delimiter = $this->detectImportCsvDelimiter($line);
            }
            if ($delimiter !== ',' && count($line) === 1) {
                $line = str_getcsv((string) $line[0], $delimiter);
            }
            $rows[] = $line;
        }
        fclose($handle);

        return $rows;
    }

    private function readEventXlsxRows(string $path): array
    {
        $sharedStrings = $this->xlsxSharedStringsFromXml($this->xlsxEntryContents($path, 'xl/sharedStrings.xml'));
        $sheetXml = $this->xlsxEntryContents($path, 'xl/worksheets/sheet1.xml');
        if (! is_string($sheetXml) || $sheetXml === '') {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        if ($xml === false) {
            return [];
        }

        $namespace = $this->xlsxSpreadsheetNamespace($xml);
        $sheetRows = [];
        if ($namespace !== null) {
            $xml->registerXPathNamespace('xlsx', $namespace);
            $sheetRows = $xml->xpath('/xlsx:worksheet/xlsx:sheetData/xlsx:row') ?: [];
        } elseif (isset($xml->sheetData)) {
            $sheetRows = iterator_to_array($xml->sheetData->row, false);
        }
        $rows = [];
        foreach ($sheetRows as $row) {
            $values = [];
            $fallbackIndex = 0;
            if ($namespace !== null) {
                $row->registerXPathNamespace('xlsx', $namespace);
                $cells = $row->xpath('xlsx:c') ?: [];
            } else {
                $cells = iterator_to_array($row->c, false);
            }

            foreach ($cells as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $reference !== '' ? $this->xlsxColumnIndex($reference) : $fallbackIndex;
                $values[$columnIndex] = trim($this->xlsxCellValue($cell, $sharedStrings));
                $fallbackIndex++;
            }

            ksort($values);
            $rows[] = $this->denseImportRowValues($values);
        }

        return $rows;
    }

    private function normalizeImportHeaders(array $headers): array
    {
        $aliases = [
            'kode_event' => 'code',
            'nama_event' => 'title',
            'judul' => 'title',
            'kategori_event' => 'category',
            'kampus' => 'campus_name',
            'kampus_pelaksana' => 'campus_name',
            'nama_kampus' => 'campus_name',
            'campus' => 'campus_name',
            'campus_name' => 'campus_name',
            'tanggal_pelaksanaan' => 'event_date',
            'tanggal_mulai' => 'event_date',
            'tanggal_akhir' => 'event_end_date',
            'jam' => 'event_time',
            'waktu' => 'event_time',
            'lokasi' => 'location',
            'pic' => 'created_by',
            'pic_user_id' => 'created_by',
            'peserta' => 'participants_count',
            'jumlah_peserta' => 'participants_count',
            'anggaran' => 'budget',
            'deskripsi' => 'description',
            'approver' => 'approved_by',
            'waktu_approval' => 'approved_at',
            'alasan_ditolak' => 'rejected_reason',
        ];

        return array_map(static function ($header) use ($aliases): string {
            $header = strtolower(trim((string) $header));
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;
            $header = trim($header, '_');

            return $aliases[$header] ?? $header;
        }, $headers);
    }

    private function normalizeImportDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (int) floor((float) $value);
            if ($serial > 0) {
                return (new \DateTimeImmutable('1899-12-30'))->modify('+' . $serial . ' days')->format('Y-m-d');
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeImportTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $float = (float) $value;
            $fraction = $float - floor($float);
            if ($fraction > 0) {
                $seconds = (int) round($fraction * 86400);

                return gmdate('H:i:s', $seconds);
            }
        }

        foreach (['H:i:s', 'H:i', 'h:i A', 'h:i a'] as $format) {
            $time = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($time instanceof \DateTimeImmutable) {
                return $time->format('H:i:s');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('H:i:s', $timestamp) : null;
    }

    private function normalizeImportDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;
            $days = (int) floor($serial);
            $seconds = (int) round(($serial - $days) * 86400);

            return (new \DateTimeImmutable('1899-12-30'))->modify('+' . $days . ' days')->modify('+' . $seconds . ' seconds')->format('Y-m-d H:i:s');
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function numericImportValue(string $value): float
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^\d,.\-]/', '', $value) ?? '';
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0;
    }

    private function isImportRowBlank(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isSkippableImportTitle(string $title): bool
    {
        $normalized = strtolower(trim($title));
        if ($normalized === '') {
            return true;
        }

        if (preg_match('/^[\d\W_]+$/', $normalized) === 1) {
            return true;
        }

        return in_array($normalized, [
            'no',
            'no.',
            'tgl',
            'date',
            'hari',
            'bulan',
            'total',
            'sub',
            'pic',
            'ket',
        ], true);
    }

    private function detectImportCsvDelimiter(array $firstLine): string
    {
        if (count($firstLine) > 1) {
            return ',';
        }

        $raw = (string) ($firstLine[0] ?? '');
        $bestDelimiter = ',';
        $bestCount = 0;
        foreach ([',', ';', "\t"] as $delimiter) {
            $count = substr_count($raw, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function xlsxFromRows(array $rows, string $sheetName = 'Event'): string
    {
        if (! class_exists(\ZipArchive::class)) {
            return $this->csvFromRows($rows);
        }

        $sheetRows = [];
        foreach ($rows as $rowIndex => $row) {
            $cells = [];
            foreach (array_values($row) as $columnIndex => $value) {
                $cellReference = $this->xlsxColumnName($columnIndex + 1) . ($rowIndex + 1);
                $cells[] = '<c r="' . $cellReference . '" t="inlineStr"><is><t>' . $this->xmlValue((string) $value) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . ($rowIndex + 1) . '">' . implode('', $cells) . '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '</worksheet>';

        $safeSheetName = $this->xmlValue(substr(preg_replace('/[\\\\\\/?*\\[\\]:]/', ' ', $sheetName) ?: 'Event', 0, 31));

        $entries = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        $path = tempnam(WRITEPATH . 'cache', 'event-template-');
        if ($path === false) {
            return '';
        }

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            @unlink($path);

            return '';
        }

        foreach ($entries as $name => $contents) {
            $zip->addFromString($name, $contents);
        }
        $zip->close();

        $contents = file_get_contents($path);
        @unlink($path);

        return is_string($contents) ? $contents : '';
    }

    private function csvFromRows(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        if (! $handle) {
            return '';
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return is_string($contents) ? $contents : '';
    }

    private function xlsxEntryContents(string $path, string $entryName): ?string
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                return null;
            }

            $contents = $zip->getFromName($entryName);
            $zip->close();

            return is_string($contents) ? $contents : null;
        }

        return $this->xlsxEntryContentsWithoutZipArchive($path, $entryName);
    }

    private function xlsxEntryContentsWithoutZipArchive(string $path, string $entryName): ?string
    {
        $data = @file_get_contents($path);
        if (! is_string($data) || $data === '') {
            return null;
        }

        $eocdOffset = strrpos($data, "PK\x05\x06");
        if ($eocdOffset === false) {
            return null;
        }

        $centralDirectorySize = $this->zipUint32($data, $eocdOffset + 12);
        $centralDirectoryOffset = $this->zipUint32($data, $eocdOffset + 16);
        $offset = $centralDirectoryOffset;
        $end = $centralDirectoryOffset + $centralDirectorySize;

        while ($offset < $end && substr($data, $offset, 4) === "PK\x01\x02") {
            $flags = $this->zipUint16($data, $offset + 8);
            $method = $this->zipUint16($data, $offset + 10);
            $compressedSize = $this->zipUint32($data, $offset + 20);
            $nameLength = $this->zipUint16($data, $offset + 28);
            $extraLength = $this->zipUint16($data, $offset + 30);
            $commentLength = $this->zipUint16($data, $offset + 32);
            $localHeaderOffset = $this->zipUint32($data, $offset + 42);
            $name = substr($data, $offset + 46, $nameLength);

            if ($name === $entryName) {
                if (($flags & 1) === 1 || substr($data, $localHeaderOffset, 4) !== "PK\x03\x04") {
                    return null;
                }

                $localNameLength = $this->zipUint16($data, $localHeaderOffset + 26);
                $localExtraLength = $this->zipUint16($data, $localHeaderOffset + 28);
                $payloadOffset = $localHeaderOffset + 30 + $localNameLength + $localExtraLength;
                $payload = substr($data, $payloadOffset, $compressedSize);

                if ($method === 0) {
                    return $payload;
                }

                if ($method === 8 && function_exists('gzinflate')) {
                    $inflated = @gzinflate($payload);

                    return is_string($inflated) ? $inflated : null;
                }

                return null;
            }

            $offset += 46 + $nameLength + $extraLength + $commentLength;
        }

        return null;
    }

    private function xlsxSpreadsheetNamespace(\SimpleXMLElement $xml): ?string
    {
        $spreadsheetNamespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        foreach ($xml->getNamespaces(true) as $namespace) {
            if ($namespace === $spreadsheetNamespace) {
                return $spreadsheetNamespace;
            }
        }

        return null;
    }

    private function xlsxChildren(\SimpleXMLElement $xml, ?string $namespace = null): \SimpleXMLElement
    {
        if ($namespace === null) {
            $namespace = $this->xlsxSpreadsheetNamespace($xml);
        }

        return $namespace !== null ? $xml->children($namespace) : $xml->children();
    }

    private function xlsxSharedStringsFromXml(?string $xml): array
    {
        if (! is_string($xml) || $xml === '') {
            return [];
        }

        $shared = simplexml_load_string($xml);
        if ($shared === false) {
            return [];
        }

        $namespace = $this->xlsxSpreadsheetNamespace($shared);
        $sharedItems = $this->xlsxChildren($shared, $namespace);
        $strings = [];
        foreach ($sharedItems->si as $item) {
            $itemChildren = $this->xlsxChildren($item, $namespace);
            if (isset($itemChildren->t)) {
                $strings[] = trim((string) $itemChildren->t);
                continue;
            }

            $text = '';
            if (isset($itemChildren->r)) {
                foreach ($itemChildren->r as $run) {
                    $runChildren = $this->xlsxChildren($run, $namespace);
                    $text .= (string) $runChildren->t;
                }
            }
            $strings[] = trim($text);
        }

        return $strings;
    }

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $namespace = $this->xlsxSpreadsheetNamespace($cell);
        $cellChildren = $this->xlsxChildren($cell, $namespace);
        if ($type === 's') {
            return $sharedStrings[(int) ((string) $cellChildren->v)] ?? '';
        }

        if ($type === 'inlineStr') {
            if (! isset($cellChildren->is)) {
                return '';
            }

            $inlineString = $this->xlsxChildren($cellChildren->is, $namespace);
            if (isset($inlineString->t)) {
                return (string) $inlineString->t;
            }

            $text = '';
            if (isset($inlineString->r)) {
                foreach ($inlineString->r as $run) {
                    $runChildren = $this->xlsxChildren($run, $namespace);
                    $text .= (string) $runChildren->t;
                }
            }

            return $text;
        }

        return (string) $cellChildren->v;
    }

    private function xlsxColumnIndex(string $cellReference): int
    {
        if (preg_match('/^([A-Z]+)/i', $cellReference, $matches) !== 1) {
            return 0;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function denseImportRowValues(array $values): array
    {
        $max = $values === [] ? -1 : max(array_keys($values));
        $dense = [];
        for ($index = 0; $index <= $max; $index++) {
            $dense[] = $values[$index] ?? '';
        }

        return $dense;
    }

    private function zipUint16(string $data, int $offset): int
    {
        $value = unpack('v', substr($data, $offset, 2));

        return (int) ($value[1] ?? 0);
    }

    private function zipUint32(string $data, int $offset): int
    {
        $value = unpack('V', substr($data, $offset, 4));

        return (int) ($value[1] ?? 0);
    }

    private function xmlValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function safeFileSegment(string $value): string
    {
        $value = strtolower(trim($value));
        $value = (string) preg_replace('/[^a-z0-9]+/i', '-', $value);
        $value = trim($value, '-');

        return $value !== '' ? $value : 'data';
    }

    private function crmFollowUpRows(array $events): array
    {
        $eventMap = [];
        foreach ($events as $event) {
            $eventId = (int) ($event['db_id'] ?? 0);
            if ($eventId <= 0) {
                continue;
            }

            $eventMap[$eventId] = [
                'event_id' => $eventId,
                'event_code' => $event['id'],
                'title' => $event['title'],
                'campus' => $event['campus'] ?? '-',
                'date' => $event['date'] ?? '',
                'date_label' => $event['date_label'] ?? '',
                'total' => 0,
                'fresh' => 0,
                'stage2_responded' => 0,
                'stage2_no_response' => 0,
                'rejected' => 0,
                'closing' => 0,
                'score' => 0,
                'tone' => 'red',
            ];
        }

        if ($eventMap === []) {
            return [];
        }

        $leads = (new CrmLeadModel())
            ->whereIn('event_id', array_keys($eventMap))
            ->findAll();

        foreach ($leads as $lead) {
            $eventId = (int) ($lead['event_id'] ?? 0);
            if (! isset($eventMap[$eventId])) {
                continue;
            }

            $stage = (string) ($lead['stage'] ?? '');
            $label = trim((string) ($lead['label'] ?? ''));
            $eventMap[$eventId]['total']++;
            $isClosing = $label === 'Closing'
                || ($stage === 'Tahap 3' && ! in_array($label, ['Menolak', 'Tidak Merespon'], true));

            if ($isClosing) {
                $eventMap[$eventId]['closing']++;
                continue;
            }

            if ($label === 'Menolak') {
                $eventMap[$eventId]['rejected']++;
                continue;
            }

            if ($label === 'Tidak Merespon') {
                $eventMap[$eventId]['stage2_no_response']++;
                continue;
            }

            if ($stage === 'Tahap 2' && $label !== '') {
                $eventMap[$eventId]['stage2_responded']++;
                continue;
            }

            if ($stage === 'Tahap 2') {
                $eventMap[$eventId]['stage2_no_response']++;
                continue;
            }

            $eventMap[$eventId]['fresh']++;
        }

        foreach ($eventMap as $row) {
            if ($row['total'] <= 0) {
                continue;
            }

            $weighted = ($row['closing'] * 100)
                + ($row['stage2_responded'] * 55)
                + ($row['stage2_no_response'] * 20)
                + ($row['rejected'] * 5);
            $row['score'] = (int) round($weighted / max(1, $row['total']));
            $row['tone'] = $row['score'] >= 70 ? 'green' : ($row['score'] >= 40 ? 'amber' : 'red');
            $eventMap[(int) $row['event_id']] = $row;
        }

        return $eventMap;
    }

    private function crmFollowUpRowsByEventCode(array $rows): array
    {
        $mapped = [];

        foreach ($rows as $row) {
            $eventCode = (string) ($row['event_code'] ?? '');
            if ($eventCode !== '') {
                $mapped[$eventCode] = $row;
            }
        }

        return $mapped;
    }

    private function crmFollowUpPerformance(array $crmRows, array $reportState): array
    {
        $selectedCampus = (string) ($reportState['selectedCampus'] ?? '');
        $selectedMonth = (string) ($reportState['selectedMonth'] ?? '');
        $rows = [];

        foreach ($crmRows as $row) {
            if (($row['total'] ?? 0) <= 0) {
                continue;
            }

            if ($selectedCampus !== '' && ($row['campus'] ?? '') !== $selectedCampus) {
                continue;
            }

            if ($selectedMonth !== '' && substr((string) ($row['date'] ?? ''), 0, 7) !== $selectedMonth) {
                continue;
            }

            $rows[] = $row;
        }

        usort($rows, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'] ?: strcmp($a['title'], $b['title']);
        });

        return $rows;
    }

    private function crmFollowUpPerformanceState(array $crmRows, array $reportState): array
    {
        $rows = $this->crmFollowUpPerformance($crmRows, $reportState);
        $perPage = 5;
        $total = count($rows);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('crm_page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $query = [
            'campus' => (string) ($reportState['selectedCampus'] ?? ''),
            'month' => (string) ($reportState['selectedMonth'] ?? ''),
            'sort' => (string) ($reportState['selectedSort'] ?? ''),
            'page' => (string) ($reportState['pagination']['current'] ?? ''),
        ];
        $query = array_filter($query, static fn (string $value): bool => $value !== '');

        return [
            'rows' => array_slice($rows, $offset, $perPage),
            'pagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $total),
                'rankStart' => $offset + 1,
                'query' => $query,
            ],
        ];
    }

    private function crmReportLogRows(string $selectedCampus = '', int $limit = 12): array
    {
        $db = db_connect();
        if (! $db->tableExists('crm_lead_logs')) {
            return [];
        }

        $builder = $db->table('crm_lead_logs')
            ->select('crm_lead_logs.*, crm_leads.student_name, crm_leads.school_origin, crm_users.full_name AS actor_name, events.code AS event_code, events.title AS event_title, campuses.name AS campus_name')
            ->join('crm_leads', 'crm_leads.id = crm_lead_logs.lead_id', 'left')
            ->join('crm_users', 'crm_users.id = crm_lead_logs.user_id', 'left')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left')
            ->orderBy('crm_lead_logs.created_at', 'DESC')
            ->orderBy('crm_lead_logs.id', 'DESC')
            ->limit($limit);

        if ($selectedCampus !== '') {
            $builder->where('campuses.name', $selectedCampus);
        }

        return $builder->get()->getResultArray();
    }

    private function checklistSummary(array $items): array
    {
        $total = count($items);
        $done = count(array_filter($items, static fn (array $item): bool => ($item['status'] ?? '') === 'done'));

        return [
            'total' => $total,
            'done' => $done,
            'pending' => max(0, $total - $done),
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
        ];
    }

    private function canManageEventChecklist(array $event): bool
    {
        if (! $this->canViewEvent($event)) {
            return false;
        }

        if (session('user.role') === 'Admin Sistem') {
            return true;
        }

        return (int) ($event['created_by'] ?? 0) === (int) session('user.id');
    }

    private function canDeleteEvent(array $event): bool
    {
        if (! $this->canViewEvent($event)) {
            return false;
        }

        if (session('user.role') === 'Admin Sistem') {
            return true;
        }

        if (strtolower($this->normalizedRoleName((string) session('user.role'))) !== 'kepala kampus') {
            return false;
        }

        $campusId = (int) session('user.campus_id');

        return $campusId > 0 && (int) ($event['campus_id'] ?? 0) === $campusId;
    }

    private function canReportAnyChecklist(array $items): bool
    {
        $userId = (int) session('user.id');

        foreach ($items as $item) {
            if ((int) ($item['pic_user_id'] ?? 0) === $userId) {
                return true;
            }
        }

        return false;
    }

    private function canReportChecklistItem(array $item, array $event): bool
    {
        return $this->canManageEventChecklist($event)
            || (int) ($item['pic_user_id'] ?? 0) === (int) session('user.id');
    }

    private function isSafeExternalLink(string $link): bool
    {
        if ($link === '') {
            return false;
        }

        if (preg_match('/[\x00-\x1F\x7F\s]/', $link) === 1) {
            return false;
        }

        if (filter_var($link, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($link, PHP_URL_HOST));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        return parse_url($link, PHP_URL_USER) === null && parse_url($link, PHP_URL_PASS) === null;
    }

    private function updateEventProgressFromChecklist(int $eventId): void
    {
        $items = (new EventChecklistItemModel())->where('event_id', $eventId)->findAll();
        $summary = $this->checklistSummary($items);
        $event = (new EventModel())->find($eventId);

        if (! $event || $summary['total'] === 0) {
            return;
        }

        $progress = max((int) $event['progress'], $summary['percent']);
        if (($event['status'] ?? '') === 'draft') {
            $progress = $summary['percent'];
        }

        $completionReportsReady = $this->hasCompletionReportUploads($items);
        $updates = ['progress' => $progress];
        if ($completionReportsReady && ! in_array(($event['status'] ?? ''), ['draft', 'ditolak'], true)) {
            $updates['status'] = 'selesai';
            $updates['progress'] = 100;
            $report = $this->ensureCompletedEventReport($eventId);
            $currentReportStatus = trim((string) ($report['realization_status'] ?? ''));
            if (in_array($currentReportStatus, ['', 'Belum tersedia', 'Menunggu pelaksanaan', 'Menunggu laporan', 'Dokumen tersedia'], true)) {
                (new ReportModel())->update($report['id'], [
                    'realization_status' => 'Menunggu hasil CRM',
                    'notes' => 'Dokumen pasca event lengkap. Laporan menunggu hasil follow up CRM.',
                ]);
            }
        }

        (new EventModel())->update($eventId, $updates);
    }

    private function hasCompletionReportUploads(array $items): bool
    {
        $requiredNames = array_values($this->completionReportChecklistNames());
        $reportedNames = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if (! in_array($name, $requiredNames, true)) {
                continue;
            }

            if (trim((string) ($item['drive_link'] ?? '')) === '') {
                continue;
            }

            $reportedNames[$name] = true;
        }

        foreach ($requiredNames as $name) {
            if (! isset($reportedNames[$name])) {
                return false;
            }
        }

        return true;
    }

    private function syncNewEventToGoogleCalendar(int $eventId, int $userId): array
    {
        $event = (new EventModel())->find($eventId);
        if (! $event) {
            return ['status' => 'failed', 'message' => 'Event tidak ditemukan.'];
        }

        $google = new GoogleCalendarService();
        if (! $google->isConfigured() || ! ($google->tokenStatus($userId)['connected'] ?? false)) {
            $this->upsertCalendarIntegration($eventId, null, null, 'pending_connect');

            return ['status' => 'pending_connect', 'message' => 'Google Calendar belum terhubung.'];
        }

        try {
            $existingIntegration = (new CalendarIntegrationModel())
                ->where('event_id', $eventId)
                ->where('provider', 'google_calendar')
                ->first();
            $googleEvent = $google->syncEvent($event, $userId, $existingIntegration['external_event_id'] ?? null);
            $this->upsertCalendarIntegration(
                $eventId,
                $googleEvent['id'] ?? null,
                $googleEvent['htmlLink'] ?? null,
                'synced'
            );

            return ['status' => 'synced', 'message' => 'Berhasil sync ke Google Calendar.'];
        } catch (\Throwable $error) {
            $this->upsertCalendarIntegration($eventId, null, null, 'failed');

            return ['status' => 'failed', 'message' => $error->getMessage()];
        }
    }

    private function upsertCalendarIntegration(int $eventId, ?string $externalId, ?string $externalLink, string $status): void
    {
        $model = new CalendarIntegrationModel();
        $existing = $model->where('event_id', $eventId)->where('provider', 'google_calendar')->first();
        if ($existing && $externalId === null) {
            $externalId = $existing['external_event_id'] ?? null;
        }
        if ($existing && $externalLink === null) {
            $externalLink = $existing['external_link'] ?? null;
        }

        $data = [
            'event_id' => $eventId,
            'provider' => 'google_calendar',
            'external_event_id' => $externalId,
            'external_link' => $externalLink,
            'sync_status' => $status,
            'last_synced_at' => $status === 'synced' ? date('Y-m-d H:i:s') : null,
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

    private function normalizeDocumentType(string $type): ?string
    {
        $type = strtolower(trim($type));

        return $type === 'proposal' ? $type : null;
    }

    private function normalizeManagedDocumentType(string $type): ?string
    {
        $type = strtolower(trim($type));

        return in_array($type, ['all', 'proposal', 'report'], true) ? $type : null;
    }

    private function eventDocumentState(array $events, array $proposals, array $reports, string $type): array
    {
        $selectedCampus = trim((string) $this->request->getGet('campus'));
        $campusOptions = [];
        $proposalMap = [];
        $reportMap = [];

        foreach ($events as $event) {
            $campus = (string) ($event['campus'] ?? '');
            if ($campus !== '') {
                $campusOptions[$campus] = true;
            }
        }

        foreach ($proposals as $proposal) {
            $proposalMap[(string) ($proposal['event_id'] ?? '')] = $proposal;
        }

        foreach ($reports as $report) {
            $reportMap[(string) ($report['event_id'] ?? '')] = $report;
        }

        $uploadsByEventId = $this->checklistDocumentUploadsForEvents($events);
        $rows = [];

        foreach ($events as $event) {
            if ($selectedCampus !== '' && (string) ($event['campus'] ?? '') !== $selectedCampus) {
                continue;
            }

            $eventCode = (string) ($event['id'] ?? '');
            $eventDbId = (int) ($event['db_id'] ?? 0);
            $uploads = $uploadsByEventId[$eventDbId] ?? [];
            $proposal = $proposalMap[$eventCode] ?? null;
            $report = $reportMap[$eventCode] ?? null;
            $documents = [
                'proposal' => $this->managedDocumentItem('proposal', $proposal, $uploads['proposal'] ?? null),
                'report' => $this->managedDocumentItem('report', $report, $uploads['report'] ?? null),
            ];

            $visibleDocuments = $type === 'all' ? $documents : [$type => $documents[$type]];
            $hasAnyUpload = false;
            foreach ($visibleDocuments as $document) {
                if (($document['link'] ?? '') !== '') {
                    $hasAnyUpload = true;
                    break;
                }
            }

            $rows[] = [
                'event' => $event,
                'documents' => $visibleDocuments,
                'has_upload' => $hasAnyUpload,
                'all_documents' => $documents,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            $leftEvent = $left['event'] ?? [];
            $rightEvent = $right['event'] ?? [];

            return strcmp((string) ($rightEvent['date'] ?? ''), (string) ($leftEvent['date'] ?? ''))
                ?: strcmp((string) ($rightEvent['id'] ?? ''), (string) ($leftEvent['id'] ?? ''));
        });

        $notifications = $this->recentDocumentUploadNotifications($rows);

        // Ringkasan statistik dokumen (berdasarkan filter kampus aktif)
        $approvedKeywords = ['disetujui', 'approved', 'selesai', 'lengkap', 'terverifikasi', 'diterima', 'valid'];
        $stats = [
            'total_events' => count($rows),
            'waiting_review' => 0,
            'complete' => 0,
            'missing' => 0,
            'trend_pct' => 0,
        ];
        $recentCount = 0;
        $previousCount = 0;
        $recentThreshold = strtotime('-30 days');
        $previousThreshold = strtotime('-60 days');

        foreach ($rows as $index => $row) {
            $allDocuments = $row['all_documents'] ?? ($row['documents'] ?? []);
            $uploaded = 0;
            $missingTypes = [];
            foreach ($allDocuments as $docType => $document) {
                $hasLink = trim((string) ($document['link'] ?? '')) !== '';
                if ($hasLink) {
                    $uploaded++;
                    $statusText = mb_strtolower((string) ($document['status'] ?? ''));
                    $isApproved = false;
                    foreach ($approvedKeywords as $keyword) {
                        if (str_contains($statusText, $keyword)) {
                            $isApproved = true;
                            break;
                        }
                    }
                    if (! $isApproved) {
                        $stats['waiting_review']++;
                    }
                } else {
                    $missingTypes[] = (string) $docType;
                }
            }

            $requiredCount = max(1, count($allDocuments));
            if ($uploaded >= $requiredCount) {
                $docState = 'complete';
                $stats['complete']++;
            } elseif ($uploaded === 0) {
                $docState = 'none';
                $stats['missing']++;
            } else {
                $docState = in_array('proposal', $missingTypes, true) ? 'waiting_proposal' : 'waiting_report';
                $stats['missing']++;
            }

            $rows[$index]['doc_uploaded'] = $uploaded;
            $rows[$index]['doc_required'] = $requiredCount;
            $rows[$index]['doc_state'] = $docState;

            $eventTime = ! empty($row['event']['date']) ? strtotime((string) $row['event']['date']) : false;
            if ($eventTime !== false && $eventTime <= time()) {
                if ($eventTime >= $recentThreshold) {
                    $recentCount++;
                } elseif ($eventTime >= $previousThreshold) {
                    $previousCount++;
                }
            }
        }
        $stats['trend_pct'] = $previousCount > 0
            ? (int) round((($recentCount - $previousCount) / $previousCount) * 100)
            : ($recentCount > 0 ? 100 : 0);

        // Filter lanjutan: pencarian, status dokumen, periode
        $search = mb_strtolower(trim((string) $this->request->getGet('q')));
        $statusFilter = trim((string) $this->request->getGet('doc_status'));
        $periodFilter = trim((string) $this->request->getGet('period'));
        $sortFilter = (string) $this->request->getGet('sort') === 'oldest' ? 'oldest' : 'newest';
        $perPageInput = (int) $this->request->getGet('per_page');
        $perPage = in_array($perPageInput, [10, 25, 50], true) ? $perPageInput : 10;

        if (! in_array($statusFilter, ['complete', 'partial', 'none', 'waiting_review'], true)) {
            $statusFilter = '';
        }
        if (preg_match('/^\d{4}-\d{2}$/', $periodFilter) !== 1) {
            $periodFilter = '';
        }

        if ($search !== '' || $statusFilter !== '' || $periodFilter !== '') {
            $rows = array_values(array_filter($rows, static function (array $row) use ($search, $statusFilter, $periodFilter, $approvedKeywords): bool {
                $event = $row['event'] ?? [];
                $allDocuments = $row['all_documents'] ?? ($row['documents'] ?? []);

                if ($search !== '') {
                    $haystack = [
                        (string) ($event['id'] ?? ''),
                        (string) ($event['title'] ?? ''),
                        (string) ($event['campus'] ?? ''),
                        (string) ($event['category'] ?? ''),
                    ];
                    foreach ($allDocuments as $document) {
                        $haystack[] = (string) ($document['code'] ?? '');
                    }
                    if (! str_contains(mb_strtolower(implode(' ', $haystack)), $search)) {
                        return false;
                    }
                }

                if ($periodFilter !== '' && ! str_starts_with((string) ($event['date'] ?? ''), $periodFilter)) {
                    return false;
                }

                $state = (string) ($row['doc_state'] ?? '');
                if ($statusFilter === 'complete' && $state !== 'complete') {
                    return false;
                }
                if ($statusFilter === 'none' && $state !== 'none') {
                    return false;
                }
                if ($statusFilter === 'partial' && ! in_array($state, ['waiting_proposal', 'waiting_report'], true)) {
                    return false;
                }
                if ($statusFilter === 'waiting_review') {
                    $hasPending = false;
                    foreach ($allDocuments as $document) {
                        if (trim((string) ($document['link'] ?? '')) === '') {
                            continue;
                        }
                        $statusText = mb_strtolower((string) ($document['status'] ?? ''));
                        $approved = false;
                        foreach ($approvedKeywords as $keyword) {
                            if (str_contains($statusText, $keyword)) {
                                $approved = true;
                                break;
                            }
                        }
                        if (! $approved) {
                            $hasPending = true;
                            break;
                        }
                    }
                    if (! $hasPending) {
                        return false;
                    }
                }

                return true;
            }));
        }

        if ($sortFilter === 'oldest') {
            $rows = array_reverse($rows);
        }

        $filters = [
            'q' => trim((string) $this->request->getGet('q')),
            'doc_status' => $statusFilter,
            'period' => $periodFilter,
            'sort' => $sortFilter,
            'per_page' => $perPage,
        ];

        $total = count($rows);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $query = array_filter([
            'campus' => $selectedCampus,
            'type' => $type !== 'all' ? $type : '',
            'q' => $filters['q'],
            'doc_status' => $filters['doc_status'],
            'period' => $filters['period'],
            'sort' => $filters['sort'] !== 'newest' ? $filters['sort'] : '',
            'per_page' => $perPage !== 10 ? (string) $perPage : '',
        ], static fn (string $value): bool => $value !== '');

        ksort($campusOptions);

        return [
            'rows' => array_slice($rows, $offset, $perPage),
            'notifications' => $notifications,
            'campusOptions' => array_keys($campusOptions),
            'selectedCampus' => $selectedCampus,
            'stats' => $stats,
            'filters' => $filters,
            'pagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $total),
                'query' => $query,
            ],
        ];
    }

    private function recentDocumentUploadNotifications(array $rows): array
    {
        $notifications = [];
        $threshold = strtotime('-7 days');
        $dismissed = $this->dismissedDocumentNotifications();

        foreach ($rows as $row) {
            $event = $row['event'] ?? [];
            foreach (($row['documents'] ?? []) as $document) {
                $type = (string) ($document['type'] ?? '');
                if (! in_array($type, ['proposal', 'report'], true) || trim((string) ($document['link'] ?? '')) === '') {
                    continue;
                }

                $uploadedAt = (string) (($document['submitted_at'] ?? '') ?: ($document['updated_at'] ?? ''));
                $uploadedTime = $uploadedAt !== '' ? strtotime($uploadedAt) : false;
                if ($uploadedTime === false || $uploadedTime < $threshold) {
                    continue;
                }

                $key = $this->documentNotificationKey(
                    (string) ($event['id'] ?? ''),
                    $type,
                    $uploadedAt,
                    (string) ($document['link'] ?? '')
                );
                if (in_array($key, $dismissed, true)) {
                    continue;
                }

                $notifications[] = [
                    'key' => $key,
                    'type' => $type,
                    'label' => $this->eventDocumentLabel($type),
                    'event_id' => $event['id'] ?? '',
                    'event_title' => $event['title'] ?? '-',
                    'campus' => $event['campus'] ?? '-',
                    'uploaded_at' => $uploadedAt,
                    'uploaded_time' => $uploadedTime,
                    'pic_name' => $document['pic_name'] ?? '',
                    'link' => $document['link'] ?? '',
                ];
            }
        }

        usort($notifications, static fn (array $left, array $right): int => ($right['uploaded_time'] ?? 0) <=> ($left['uploaded_time'] ?? 0));

        return array_slice($notifications, 0, 5);
    }

    private function dismissedDocumentNotifications(): array
    {
        $dismissed = session('dismissed_document_notifications') ?? [];

        return is_array($dismissed) ? array_values(array_filter($dismissed, 'is_string')) : [];
    }

    private function dismissDocumentNotification(string $key): void
    {
        $dismissed = $this->dismissedDocumentNotifications();
        $dismissed[] = $key;
        $dismissed = array_slice(array_values(array_unique($dismissed)), -100);

        session()->put('dismissed_document_notifications', $dismissed);
    }

    private function documentNotificationKey(string $eventCode, string $type, string $uploadedAt, string $link): string
    {
        return sha1(implode('|', [$eventCode, $type, $uploadedAt, $link]));
    }

    private function documentLinkForNotification(int $eventId, string $type): string
    {
        $document = $type === 'report'
            ? (new ReportModel())->where('event_id', $eventId)->first()
            : (new ProposalModel())->where('event_id', $eventId)->first();
        $documentLink = trim((string) ($document['document_link'] ?? ''));
        if ($documentLink !== '') {
            return $documentLink;
        }

        $upload = (new EventChecklistItemModel())
            ->where('event_id', $eventId)
            ->where('name', $this->eventDocumentChecklistName($type))
            ->first();

        return trim((string) ($upload['drive_link'] ?? ''));
    }

    private function managedDocumentItem(string $type, ?array $record, ?array $upload): array
    {
        $link = trim((string) (($record['document_link'] ?? '') ?: ($upload['drive_link'] ?? '')));
        $status = $type === 'report'
            ? (string) ($record['realization_status'] ?? ($upload['status'] ?? 'Belum ada laporan'))
            : (string) ($record['status'] ?? ($upload['status'] ?? 'Belum ada proposal'));

        return [
            'type' => $type,
            'label' => $this->eventDocumentLabel($type),
            'code' => $record['id'] ?? '-',
            'db_id' => $record['db_id'] ?? null,
            'status' => $status,
            'link' => $link,
            'notes' => $record['notes'] ?? '',
            'score' => (int) ($record['score'] ?? 0),
            'updated_at' => $record['updated_at'] ?? '',
            'upload_status' => $upload['status'] ?? 'belum upload',
            'submitted_at' => $upload['submitted_at'] ?? '',
            'pic_name' => $upload['pic_name'] ?? '',
        ];
    }

    private function checklistDocumentUploadsForEvents(array $events): array
    {
        $eventIds = array_values(array_filter(array_map(
            static fn (array $event): int => (int) ($event['db_id'] ?? 0),
            $events
        )));

        if ($eventIds === []) {
            return [];
        }

        $items = (new EventChecklistItemModel())
            ->select('event_checklist_items.*, users.name AS pic_name')
            ->join('users', 'users.id = event_checklist_items.pic_user_id', 'left')
            ->whereIn('event_checklist_items.event_id', $eventIds)
            ->whereIn('event_checklist_items.name', array_values($this->completionReportChecklistNames()))
            ->findAll();

        $uploads = [];
        $names = $this->completionReportChecklistNames();
        $typeMap = [
            'laporan' => 'report',
            'proposal' => 'proposal',
        ];

        foreach ($items as $item) {
            $key = array_search($item['name'], $names, true);
            if ($key === false || ! isset($typeMap[$key])) {
                continue;
            }

            $type = $typeMap[$key];
            $eventId = (int) $item['event_id'];
            if (isset($uploads[$eventId][$type]) && empty($item['drive_link'])) {
                continue;
            }

            $uploads[$eventId][$type] = [
                'name' => $item['name'] ?? '',
                'status' => $item['status'] ?? 'pending',
                'drive_link' => $item['drive_link'] ?? '',
                'submitted_at' => $item['submitted_at'] ?? '',
                'pic_name' => $item['pic_name'] ?? '',
            ];
        }

        return $uploads;
    }

    private function reportDocumentData(array $report, string $documentLink): void
    {
        $status = trim((string) ($this->request->getPost('realization_status') ?: ($report['realization_status'] ?? 'Menunggu laporan')));
        $notes = trim((string) ($this->request->getPost('notes') ?? ($report['notes'] ?? '')));

        (new ReportModel())->update($report['id'], [
            'document_link' => $documentLink !== '' ? $documentLink : null,
            'realization_status' => $status !== '' ? $status : ($documentLink !== '' ? 'Dokumen tersedia' : 'Menunggu laporan'),
            'score' => (int) ($this->request->getPost('score') ?? ($report['score'] ?? 0)),
            'notes' => $notes,
        ]);
    }

    private function uploadedDocumentFromRequest(string $field, array $event, string $type): ?string
    {
        $file = $this->request->getFile($field);
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid()) {
            throw new \RuntimeException('File gagal diupload. Silakan pilih ulang file dokumen.');
        }

        $extension = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        if (! in_array($extension, ['doc', 'docx', 'pdf'], true)) {
            throw new \RuntimeException('Format file harus doc, docx, atau pdf.');
        }

        if ($file->getSize() > 15 * 1024 * 1024) {
            throw new \RuntimeException('Ukuran file maksimal 15 MB.');
        }

        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'event_documents' . DIRECTORY_SEPARATOR . $type;
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $safeCode = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($event['code'] ?? $event['id'] ?? 'event')) ?: 'event';
        $fileName = $type . '-' . trim($safeCode, '-') . '-' . date('YmdHis') . '.' . $extension;
        $file->move($targetDir, $fileName, true);

        return 'uploads/event_documents/' . $type . '/' . $fileName;
    }

    private function isExternalDocumentLink(string $link): bool
    {
        return preg_match('/^https?:\/\//i', $link) === 1;
    }

    private function uploadedDocumentAbsolutePath(string $relativePath): ?string
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        if (! str_starts_with($relativePath, 'uploads/event_documents/')) {
            return null;
        }

        return WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function clearChecklistDocumentUpload(int $eventId, string $type): void
    {
        $item = (new EventChecklistItemModel())
            ->where('event_id', $eventId)
            ->where('name', $this->eventDocumentChecklistName($type))
            ->first();

        if (! $item) {
            return;
        }

        (new EventChecklistItemModel())->update($item['id'], [
            'drive_link' => null,
            'status' => 'pending',
            'submitted_by' => null,
            'submitted_at' => null,
            'completed_at' => null,
        ]);
    }

    private function documentRedirectUrl(string $fallbackType): string
    {
        $selectedType = $this->normalizeManagedDocumentType((string) $this->request->getPost('selected_document_type')) ?? $fallbackType;
        $query = array_filter([
            'campus' => trim((string) $this->request->getPost('campus')),
        ], static fn (string $value): bool => $value !== '');

        return site_url('documents/' . $selectedType . ($query !== [] ? '?' . http_build_query($query) : ''));
    }

    private function documentStructureFields(string $type): array
    {
        return [
            'ringkasan' => 'Ringkasan kegiatan',
        ];
    }

    private function submittedDocumentStructure(string $type): array
    {
        $submitted = $this->request->getPost('structure');
        $submitted = is_array($submitted) ? $submitted : [];
        $allowed = $this->documentStructureFields($type);
        $structure = [];

        foreach ($allowed as $key => $label) {
            $value = trim((string) ($submitted[$key] ?? ''));
            if ($value !== '') {
                $structure[$label] = $value;
            }
        }

        return $structure;
    }

    private function normalizeEventDocumentType(string $type): ?string
    {
        $type = strtolower(trim($type));
        if ($type === 'after_event') {
            return 'video';
        }

        return in_array($type, ['proposal', 'report', 'foto', 'video'], true) ? $type : null;
    }

    private function eventDocumentChecklistName(string $type): string
    {
        return match ($type) {
            'proposal' => 'Proposal',
            'report' => 'Laporan',
            'foto' => 'Link Dokumentasi Foto',
            'video' => 'Link Dokumentasi Video',
        };
    }

    private function eventDocumentLabel(string $type): string
    {
        return match ($type) {
            'proposal' => 'Proposal',
            'report' => 'Laporan',
            'foto' => 'Link Dokumentasi Foto',
            'video' => 'Link Dokumentasi Video',
            default => 'Dokumen',
        };
    }

    private function documentModel(string $type)
    {
        return new ProposalModel();
    }

    private function ensureReportRecord(int $eventId): array
    {
        $model = new ReportModel();
        $report = $model->where('event_id', $eventId)->first();

        if ($report) {
            return $report;
        }

        $id = $model->insert([
            'code' => $this->nextCode('RPT', 'reports'),
            'event_id' => $eventId,
            'realization_status' => 'Menunggu laporan',
            'score' => 0,
            'notes' => 'Event telah terlaksana. Laporan dapat dilengkapi dari dokumen dan checklist pasca event.',
        ], true);

        return $model->find($id);
    }

    private function ensureCompletedEventReport(int $eventId): array
    {
        $model = new ReportModel();
        $report = $this->ensureReportRecord($eventId);
        $status = trim((string) ($report['realization_status'] ?? ''));

        if (in_array($status, ['', 'Belum tersedia', 'Menunggu pelaksanaan'], true)) {
            $model->update($report['id'], [
                'realization_status' => 'Menunggu laporan',
                'notes' => $report['notes'] ?: 'Event telah terlaksana. Laporan dapat dilengkapi dari dokumen dan checklist pasca event.',
            ]);

            return $model->find($report['id']);
        }

        return $report;
    }

    private function syncChecklistDocumentUpload(int $eventId, string $type, string $documentLink): void
    {
        $model = new EventChecklistItemModel();
        $item = $model
            ->where('event_id', $eventId)
            ->where('name', $this->eventDocumentChecklistName($type))
            ->first();

        if (! $item) {
            return;
        }

        $model->update($item['id'], [
            'drive_link' => $documentLink,
            'submitted_by' => (int) session('user.id'),
            'submitted_at' => date('Y-m-d H:i:s'),
            'status' => 'done',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function syncChecklistUploadsToDocumentRecords(int $eventId, array $uploads): void
    {
        foreach ($uploads as $type => $upload) {
            $documentLink = trim((string) ($upload['drive_link'] ?? ''));
            if ($documentLink === '') {
                continue;
            }

            if ($type === 'report') {
                $report = $this->ensureReportRecord($eventId);
                if (($report['document_link'] ?? '') !== $documentLink) {
                    (new ReportModel())->update($report['id'], ['document_link' => $documentLink]);
                }
                continue;
            }

            if ($type !== 'proposal') {
                continue;
            }

            $document = $this->ensureDocumentRecord($type, $eventId);
            if (($document['document_link'] ?? '') !== $documentLink) {
                $this->documentModel($type)->update($document['id'], [
                    'document_link' => $documentLink,
                    'status' => 'link tersedia',
                ]);
            }
        }
    }

    private function ensureDocumentRecord(string $type, int $eventId): array
    {
        $model = $this->documentModel($type);
        $document = $model->where('event_id', $eventId)->first();

        if ($document) {
            return $document;
        }

        $id = $model->insert([
            'code' => $this->nextCode('PRP', 'proposals'),
            'event_id' => $eventId,
            'status' => 'draft',
            'content' => null,
        ], true);

        return $model->find($id);
    }

    private function documentTemplateContent(string $type, array $document): string
    {
        if (! empty($document['template_path'])) {
            $path = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $document['template_path']);
            if (is_file($path)) {
                $content = $this->extractTemplateText($path);
                if ($content !== '') {
                    return $content;
                }

                return implode("\n\n", [
                    'Template admin: ' . ($document['template_name'] ?? basename($path)),
                    'Catatan: isi template dokumen tidak dapat diekstrak sebagai teks oleh sistem. Gunakan struktur standar berikut sebagai dasar, lalu sesuaikan dengan data event.',
                    $this->defaultDocumentTemplate($type),
                ]);
            }
        }

        return $this->defaultDocumentTemplate($type);
    }

    private function extractTemplateText(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            return $this->extractDocxTemplateText($path);
        }

        $content = file_get_contents($path);
        if (! is_string($content)) {
            return '';
        }

        return $this->cleanExtractedTemplateText($content);
    }

    private function extractDocxTemplateText(string $path): string
    {
        if (! class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($xml) || $xml === '') {
            return '';
        }

        $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/w:tr>/', "\n", $xml) ?? $xml;
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return $this->cleanExtractedTemplateText($text);
    }

    private function cleanExtractedTemplateText(string $content): string
    {
        if (function_exists('mb_convert_encoding')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }

        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]+/', ' ', $content) ?? '';
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\R{3,}/', "\n\n", $text) ?? $text;
        $text = trim($text);

        if (strlen($text) < 40) {
            return '';
        }

        return substr($text, 0, 12000);
    }

    private function defaultDocumentTemplate(string $type): string
    {
        return implode("\n\n", [
            '# Proposal Kegiatan',
            '## Judul Kegiatan',
            '{{judul_event}}',
            '## Ringkasan',
            '{{ringkasan}}',
            '## Latar Belakang',
            '{{latar_belakang}}',
            '## Tujuan Kegiatan',
            '{{tujuan}}',
            '## Sasaran Peserta',
            '{{jumlah_peserta}} peserta',
            '## Jadwal dan Lokasi',
            '{{tanggal}} {{waktu}} di {{lokasi}}',
            '## Rencana Kegiatan',
            '{{rencana_kegiatan}}',
            '## Estimasi Anggaran',
            '{{budget}}',
            '## Penutup',
            '{{penutup}}',
        ]);
    }

    private function writeGeneratedDocument(string $type, string $code, string $content): string
    {
        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'generated_documents';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $fileName = $type . '-' . $code . '-' . date('YmdHis') . '.doc';
        file_put_contents($targetDir . DIRECTORY_SEPARATOR . $fileName, $this->wordDocumentHtml($type, $code, $content));

        return 'uploads/generated_documents/' . $fileName;
    }

    private function wordDocumentHtml(string $type, string $code, string $content): string
    {
        $title = strtoupper($type) . ' ' . $code;

        return implode("\n", [
            '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">',
            '<head>',
            '<meta charset="utf-8">',
            '<meta name="ProgId" content="Word.Document">',
            '<meta name="Generator" content="EventHub Gemini Document Generator">',
            '<title>' . esc($title) . '</title>',
            '<style>',
            '@page Section1 { size: 21cm 29.7cm; margin: 2.5cm 2.5cm 2.5cm 2.5cm; }',
            'div.Section1 { page: Section1; }',
            'body { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.5; color: #111111; }',
            'h1 { font-size: 16pt; text-align: center; text-transform: uppercase; margin: 0 0 18pt; font-weight: bold; }',
            'h1.page-heading { page-break-before: always; margin-top: 0; }',
            'h1.first-heading { page-break-before: auto; }',
            'h2 { font-size: 13pt; margin: 14pt 0 6pt; font-weight: bold; }',
            'h3 { font-size: 12pt; margin: 10pt 0 4pt; font-weight: bold; }',
            'p { margin: 0 0 8pt; text-align: justify; }',
            'p.center { text-align: center; }',
            'p.signature-line { margin-top: 28pt; text-align: center; }',
            'ul, ol { margin: 0 0 8pt 24pt; padding: 0; }',
            'li { margin-bottom: 4pt; text-align: justify; }',
            'table { width: 100%; border-collapse: collapse; margin: 8pt 0 12pt; }',
            'th, td { border: 1px solid #333333; padding: 5pt 6pt; vertical-align: top; font-size: 11pt; line-height: 1.35; }',
            'th { text-align: center; font-weight: bold; background: #eeeeee; }',
            'td.number, th.number { text-align: center; }',
            '.page-break { page-break-before: always; }',
            '</style>',
            '</head>',
            '<body>',
            '<div class="Section1">',
            $this->markdownToWordHtml($content),
            '</div>',
            '</body>',
            '</html>',
        ]);
    }

    private function markdownToWordHtml(string $content): string
    {
        $lines = preg_split('/\R/', trim($content)) ?: [];
        $html = [];
        $listType = null;
        $headingCount = 0;
        $closeList = static function () use (&$html, &$listType): void {
            if ($listType !== null) {
                $html[] = '</' . $listType . '>';
                $listType = null;
            }
        };

        $totalLines = count($lines);
        for ($index = 0; $index < $totalLines; $index++) {
            $line = trim($lines[$index]);
            if ($this->isMarkdownTableLine($line)) {
                $closeList();
                $tableLines = [];
                while ($index < $totalLines && $this->isMarkdownTableLine(trim($lines[$index]))) {
                    $tableLines[] = trim($lines[$index]);
                    $index++;
                }
                $index--;
                $html[] = $this->markdownTableToWordHtml($tableLines);
                continue;
            }

            $line = trim($line);
            if ($line === '') {
                $closeList();
                continue;
            }

            if (preg_match('/^-{3,}$/', $line) === 1) {
                $closeList();
                $html[] = '<div class="page-break"></div>';
                continue;
            }

            if (str_starts_with($line, '# ')) {
                $closeList();
                $heading = trim(substr($line, 2));
                $headingCount++;
                $html[] = '<h1 class="' . ($headingCount === 1 ? 'first-heading' : 'page-heading') . '">' . $this->formatInlineWordHtml($heading) . '</h1>';
                continue;
            }

            if (str_starts_with($line, '## ')) {
                $closeList();
                $html[] = '<h2>' . $this->formatInlineWordHtml(trim(substr($line, 3))) . '</h2>';
                continue;
            }

            if (str_starts_with($line, '### ')) {
                $closeList();
                $html[] = '<h3>' . $this->formatInlineWordHtml(trim(substr($line, 4))) . '</h3>';
                continue;
            }

            if ($this->looksLikeMajorDocumentHeading($line)) {
                $closeList();
                $headingCount++;
                $html[] = '<h1 class="' . ($headingCount === 1 ? 'first-heading' : 'page-heading') . '">' . $this->formatInlineWordHtml($line) . '</h1>';
                continue;
            }

            if ($this->looksLikeSubDocumentHeading($line)) {
                $closeList();
                $html[] = '<h2>' . $this->formatInlineWordHtml($line) . '</h2>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $line, $matches) === 1) {
                if ($listType !== 'ul') {
                    $closeList();
                    $html[] = '<ul>';
                    $listType = 'ul';
                }
                $html[] = '<li>' . $this->formatInlineWordHtml($matches[1]) . '</li>';
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches) === 1) {
                if ($listType !== 'ol') {
                    $closeList();
                    $html[] = '<ol>';
                    $listType = 'ol';
                }
                $html[] = '<li>' . $this->formatInlineWordHtml($matches[1]) . '</li>';
                continue;
            }

            $closeList();
            $paragraphClass = preg_match('/^(Jakarta,|Mengetahui,|Menyetujui,|Steering Committee|Ketua Pelaksana|Pimpinan Kampus|Rektor)/i', $line) === 1
                ? ' class="center"'
                : '';
            $html[] = '<p' . $paragraphClass . '>' . $this->formatInlineWordHtml($line) . '</p>';
        }

        $closeList();

        return implode("\n", $html);
    }

    private function isMarkdownTableLine(string $line): bool
    {
        return $line !== '' && substr_count($line, '|') >= 2;
    }

    private function markdownTableToWordHtml(array $lines): string
    {
        $rows = [];
        foreach ($lines as $line) {
            $cells = array_map('trim', explode('|', trim($line, '| ')));
            if ($cells === [] || $this->isMarkdownTableSeparator($cells)) {
                continue;
            }
            $rows[] = $cells;
        }

        if ($rows === []) {
            return '';
        }

        $columnCount = max(array_map('count', $rows));
        $html = ['<table>'];
        foreach ($rows as $rowIndex => $row) {
            $html[] = '<tr>';
            for ($cellIndex = 0; $cellIndex < $columnCount; $cellIndex++) {
                $cell = $row[$cellIndex] ?? '';
                $tag = $rowIndex === 0 ? 'th' : 'td';
                $class = preg_match('/^(no|qty|hari)$/i', $cell) === 1 || preg_match('/^\d+$/', $cell) === 1 ? ' class="number"' : '';
                $html[] = '<' . $tag . $class . '>' . $this->formatInlineWordHtml($cell) . '</' . $tag . '>';
            }
            $html[] = '</tr>';
        }
        $html[] = '</table>';

        return implode("\n", $html);
    }

    private function isMarkdownTableSeparator(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell === '' || preg_match('/^:?-{3,}:?$/', $cell) !== 1) {
                return false;
            }
        }

        return true;
    }

    private function looksLikeMajorDocumentHeading(string $line): bool
    {
        return preg_match('/^(HALAMAN COVER|KATA PENGANTAR|DAFTAR ISI|BAB\s+[IVXLCDM]+|HALAMAN PENGESAHAN)\b/i', $line) === 1;
    }

    private function looksLikeSubDocumentHeading(string $line): bool
    {
        return preg_match('/^\d+\.\d+\s+.+$/', $line) === 1;
    }

    private function formatInlineWordHtml(string $text): string
    {
        $escaped = esc($text);
        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $escaped) ?? $escaped;

        return $escaped;
    }

    private function eventCode(int $eventId): string
    {
        if (isset($this->eventCodeCache[$eventId])) {
            return $this->eventCodeCache[$eventId];
        }

        $event = (new EventModel())->find($eventId);
        if ($event) {
            $this->eventCodeCache[$eventId] = (string) $event['code'];
        }

        return $event['code'] ?? (string) $eventId;
    }

    private function stats(array $events, array $reports): array
    {
        $pending = count(array_filter($events, static fn ($event) => $event['status'] === 'diajukan'));
        $participants = array_sum(array_map(static fn ($event) => (int) $event['participants_count'], $events));
        $readyReports = count(array_filter($reports, static fn ($report) => $report['realization_status'] === 'Lengkap'));

        return [
            ['label' => 'Total Event', 'value' => count($events), 'trend' => '+real-time', 'tone' => 'blue'],
            ['label' => 'Menunggu Approval', 'value' => $pending, 'trend' => 'perlu review', 'tone' => 'amber'],
            ['label' => 'Total Peserta', 'value' => $participants, 'trend' => 'akumulasi', 'tone' => 'green'],
            ['label' => 'Laporan Lengkap', 'value' => $readyReports, 'trend' => 'post event', 'tone' => 'violet'],
        ];
    }

    private function analyticsStats(array $events, array $reports, array $crmSummary): array
    {
        $readyReports = count(array_filter($reports, static fn ($report) => ($report['realization_status'] ?? '') === 'Lengkap'));

        return [
            ['label' => 'Total Event', 'value' => count($events), 'suffix' => '', 'trend' => 'sesuai kampus', 'tone' => 'blue'],
            ['label' => 'Hasil CRM', 'value' => $crmSummary['score'], 'suffix' => '%', 'trend' => 'keberhasilan follow up', 'tone' => $crmSummary['tone']],
            ['label' => 'Data Terkumpul', 'value' => $crmSummary['total'], 'suffix' => '', 'trend' => 'data CRM', 'tone' => 'green'],
            ['label' => 'Laporan Lengkap', 'value' => $readyReports, 'suffix' => '', 'trend' => 'post event', 'tone' => 'violet'],
        ];
    }

    private function specialAnalyticsState(array $events, array $campuses, string $year, string $month): array
    {
        $monthLabels = $this->shortMonthLabels();
        $eventsInYear = $this->filterEventsByYear($events, $year);
        $eventsInPeriod = $month === '' ? $eventsInYear : $this->filterEventsByMonth($eventsInYear, $month);
        $targetMonths = $month === '' ? 12 : 1;
        $targetRegular = $targetMonths * 2;
        $targetOpenhouse = $targetMonths * 2;
        $targetTotal = $targetRegular + $targetOpenhouse;
        $eventsByCampus = [];
        $eventsYearByCampus = [];

        foreach ($eventsInPeriod as $event) {
            $eventsByCampus[(int) ($event['campus_id'] ?? 0)][] = $event;
        }

        foreach ($eventsInYear as $event) {
            $eventsYearByCampus[(int) ($event['campus_id'] ?? 0)][] = $event;
        }

        $rows = [];
        foreach ($campuses as $campus) {
            $campusId = (int) ($campus['id'] ?? 0);
            if ($campusId <= 0) {
                continue;
            }

            $periodEvents = $eventsByCampus[$campusId] ?? [];
            $yearEvents = $eventsYearByCampus[$campusId] ?? [];
            $openhouseCount = count(array_filter($periodEvents, fn (array $event): bool => $this->isOpenhouseCategory((string) ($event['category'] ?? ''))));
            $regularCount = count(array_filter($periodEvents, fn (array $event): bool => $this->isOneCampusTwoEventCategory((string) ($event['category'] ?? ''))));
            $eligibleTotal = $regularCount + $openhouseCount;
            $regularGap = max(0, $targetRegular - $regularCount);
            $openhouseGap = max(0, $targetOpenhouse - $openhouseCount);
            $totalGap = $regularGap + $openhouseGap;
            $monthly = array_fill_keys(array_keys($monthLabels), 0);

            foreach ($yearEvents as $event) {
                if (! $this->isOneCampusFulfillmentCategory((string) ($event['category'] ?? ''))) {
                    continue;
                }

                $eventMonth = substr((string) ($event['date'] ?? ''), 5, 2);
                if (isset($monthly[$eventMonth])) {
                    $monthly[$eventMonth]++;
                }
            }

            $maxMonthly = max(1, max($monthly));
            $rows[] = [
                'campus_id' => $campusId,
                'campus' => (string) ($campus['name'] ?? 'Tanpa kampus'),
                'total' => $eligibleTotal,
                'regular' => $regularCount,
                'openhouse' => $openhouseCount,
                'target_total' => $targetTotal,
                'target_regular' => $targetRegular,
                'target_openhouse' => $targetOpenhouse,
                'regular_gap' => $regularGap,
                'openhouse_gap' => $openhouseGap,
                'total_gap' => $totalGap,
                'percent' => $targetTotal > 0 ? min(100, (int) round(($eligibleTotal / $targetTotal) * 100)) : 100,
                'target_met' => $totalGap === 0,
                'monthly' => array_map(static fn (int $count): array => [
                    'count' => $count,
                    'percent' => (int) round(($count / $maxMonthly) * 100),
                ], $monthly),
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return ($right['total_gap'] <=> $left['total_gap'])
                ?: ($left['percent'] <=> $right['percent'])
                ?: strcmp((string) $left['campus'], (string) $right['campus']);
        });

        $notifications = array_values(array_filter($rows, static fn (array $row): bool => ! $row['target_met']));
        $targetMet = count($rows) - count($notifications);

        return [
            'summary' => [
                ['label' => 'Kampus Dipantau', 'value' => count($rows), 'suffix' => '', 'trend' => 'seluruh master kampus', 'tone' => 'blue'],
                ['label' => 'Mencapai Target', 'value' => $targetMet, 'suffix' => '', 'trend' => 'sesuai periode', 'tone' => 'green'],
                ['label' => 'Belum Target', 'value' => count($notifications), 'suffix' => '', 'trend' => 'perlu tindak lanjut', 'tone' => count($notifications) > 0 ? 'red' : 'green'],
                ['label' => 'Total Event', 'value' => count($eventsInPeriod), 'suffix' => '', 'trend' => 'periode terpilih', 'tone' => 'violet'],
            ],
            'eventsInPeriod' => $eventsInPeriod,
            'campusRows' => $rows,
            'notifications' => $notifications,
            'monthLabels' => $monthLabels,
        ];
    }

    private function specialStrategyReportRows(
        array $events,
        array $campuses,
        array $schoolReports,
        array $ojtReports,
        array $bannerReports,
        string $year,
        string $month
    ): array {
        $targets = $this->specialStrategyTargets();
        $eventsInPeriod = $this->filterEventsByMonth($this->filterEventsByYear($events, $year), $month);
        $rows = [];

        foreach ($campuses as $campus) {
            $campusId = (int) ($campus['id'] ?? 0);
            if ($campusId <= 0) {
                continue;
            }

            $rows[$campusId] = [
                'campus_id' => $campusId,
                'campus' => (string) ($campus['name'] ?? 'Tanpa kampus'),
                'octh' => 0,
                'octe' => 0,
                'octs' => 0,
                'ocfo' => 0,
                'ocfs' => 0,
            ];
        }

        foreach ($eventsInPeriod as $event) {
            $campusId = (int) ($event['campus_id'] ?? 0);
            if (! isset($rows[$campusId])) {
                continue;
            }

            if ($this->isOpenhouseCategory((string) ($event['category'] ?? ''))) {
                $rows[$campusId]['octh']++;
            } elseif ($this->isOneCampusTwoEventCategory((string) ($event['category'] ?? ''))) {
                $rows[$campusId]['octe']++;
            }
        }

        foreach ($this->countRowsByCampus($schoolReports) as $campusId => $count) {
            if (isset($rows[$campusId])) {
                $rows[$campusId]['octs'] = $count;
            }
        }

        foreach ($this->countRowsByCampus($ojtReports) as $campusId => $count) {
            if (isset($rows[$campusId])) {
                $rows[$campusId]['ocfo'] = $count;
            }
        }

        foreach ($this->countRowsByCampus($bannerReports) as $campusId => $count) {
            if (isset($rows[$campusId])) {
                $rows[$campusId]['ocfs'] = $count;
            }
        }

        foreach ($rows as &$row) {
            foreach ($targets as $key => $target) {
                $row[$key . '_target'] = $target;
                $row[$key . '_missed'] = (int) $row[$key] < $target;
            }
            $row['target_met'] = ! $row['octh_missed']
                && ! $row['octe_missed']
                && ! $row['octs_missed']
                && ! $row['ocfo_missed']
                && ! $row['ocfs_missed'];
        }
        unset($row);

        usort($rows, static fn (array $left, array $right): int => strcmp((string) $left['campus'], (string) $right['campus']));

        $number = 1;
        foreach ($rows as &$row) {
            $row['no'] = $number++;
        }
        unset($row);

        return array_values($rows);
    }

    private function specialStrategyTargets(): array
    {
        return [
            'octh' => 2,
            'octe' => 2,
            'octs' => 60,
            'ocfo' => 5,
            'ocfs' => 5,
        ];
    }

    private function countRowsByCampus(array $rows): array
    {
        $counts = [];

        foreach ($rows as $row) {
            $campusId = (int) ($row['campus_id'] ?? 0);
            if ($campusId <= 0) {
                continue;
            }

            $counts[$campusId] = ($counts[$campusId] ?? 0) + 1;
        }

        return $counts;
    }

    private function specialStrategyExcelHtml(array $rows, \DateTimeImmutable $period): string
    {
        $columns = [
            'octh' => 'OCTH',
            'octe' => 'OCTE',
            'octs' => 'OCTS',
            'ocfo' => 'OCFO',
            'ocfs' => 'OCFS',
        ];
        $border = 'border:1px solid #000000;';
        $center = 'text-align:center;vertical-align:middle;';
        $headerStyle = $border . $center . 'font-weight:bold;background:#ffffff;';
        $numberStyle = $border . $center;
        $campusStyle = $border . 'vertical-align:middle;';
        $missedStyle = $border . $center . 'background:#f8cbd2;color:#9f1239;';

        $html = [
            '<!doctype html>',
            '<html>',
            '<head>',
            '<meta charset="UTF-8">',
            '<style>',
            'table{border-collapse:collapse;font-family:Arial,Helvetica,sans-serif;font-size:11pt;}',
            'td,th{padding:4px 6px;}',
            '.title{font-weight:bold;font-size:14pt;text-align:center;}',
            '.subtitle{font-weight:bold;font-size:12pt;text-align:center;}',
            '.period{font-weight:bold;font-size:12pt;text-align:center;}',
            '</style>',
            '</head>',
            '<body>',
            '<table>',
            '<colgroup>',
            '<col style="width:40px;">',
            '<col style="width:300px;">',
            '<col style="width:70px;">',
            '<col style="width:70px;">',
            '<col style="width:70px;">',
            '<col style="width:70px;">',
            '<col style="width:70px;">',
            '</colgroup>',
            '<tr><td colspan="7" class="title">Laporan 5 Strategi DMER (MARKOM)</td></tr>',
            '<tr><td colspan="7" class="subtitle">OCTH - OCTE - OCTS - OCFO - OCFS</td></tr>',
            '<tr><td colspan="7" class="period">' . esc($period->format('M-y')) . '</td></tr>',
            '<tr><td colspan="7">&nbsp;</td></tr>',
            '<tr>',
            '<th style="' . $headerStyle . '">No</th>',
            '<th style="' . $headerStyle . 'text-align:left;">Nama Kampus</th>',
        ];

        foreach ($columns as $label) {
            $html[] = '<th style="' . $headerStyle . '">' . esc($label) . '</th>';
        }

        $html[] = '</tr>';

        foreach ($rows as $row) {
            $html[] = '<tr>';
            $html[] = '<td style="' . $numberStyle . '">' . esc((string) ($row['no'] ?? '')) . '</td>';
            $html[] = '<td style="' . $campusStyle . '">' . esc((string) ($row['campus'] ?? '')) . '</td>';

            foreach (array_keys($columns) as $key) {
                $style = ! empty($row[$key . '_missed']) ? $missedStyle : $numberStyle;
                $html[] = '<td style="' . $style . '">' . esc((string) ((int) ($row[$key] ?? 0))) . '</td>';
            }

            $html[] = '</tr>';
        }

        if ($rows === []) {
            $html[] = '<tr><td colspan="7" style="' . $border . '">Belum ada data kampus untuk laporan ini.</td></tr>';
        }

        $html[] = '<tr><td colspan="7">&nbsp;</td></tr>';
        $html[] = '<tr>';
        $html[] = '<td style="background:#ff0000;border:1px solid #ff0000;">&nbsp;</td>';
        $html[] = '<td colspan="6">Belum Tercapai</td>';
        $html[] = '</tr>';
        $html[] = '<tr><td colspan="7">&nbsp;</td></tr>';
        $html[] = '<tr><td colspan="7">Target bulanan: OCTH 2, OCTE 2, OCTS 60, OCFO 5, OCFS 5.</td></tr>';
        $html[] = '<tr><td colspan="7">OCTH = One Campus Two Hangout/Open House; OCTE = One Campus Two Event; OCTS = One Campus Sixty School; OCFO = One Campus Five OJT; OCFS = One Campus Five Spanduk.</td></tr>';
        $html[] = '</table>';
        $html[] = '</body></html>';

        return implode('', $html);
    }

    private function shortMonthLabels(): array
    {
        return [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mei',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Agu',
            '09' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des',
        ];
    }

    private function filterEventsByCampus(array $events, ?int $campusId): array
    {
        if ($campusId === null) {
            return $events;
        }

        return array_values(array_filter($events, static fn (array $event): bool => (int) ($event['campus_id'] ?? 0) === $campusId));
    }

    private function filterEventsByMonth(array $events, string $month): array
    {
        if ($month === '') {
            return $events;
        }

        return array_values(array_filter($events, static fn (array $event): bool => substr((string) ($event['date'] ?? ''), 5, 2) === $month));
    }

    private function filterEventsByYear(array $events, string $year): array
    {
        if ($year === '') {
            return $events;
        }

        return array_values(array_filter($events, static fn (array $event): bool => substr((string) ($event['date'] ?? ''), 0, 4) === $year));
    }

    private function selectedAnalyticsYear(): string
    {
        $year = trim((string) $this->request->getGet('year'));

        if (preg_match('/^\d{4}$/', $year) === 1) {
            return $year;
        }

        $month = trim((string) $this->request->getGet('month'));
        if (preg_match('/^(\d{4})-\d{2}$/', $month, $matches) === 1) {
            return $matches[1];
        }

        $date = trim((string) $this->request->getGet('date'));
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $date, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    private function selectedSpecialAnalyticsPeriodType(): string
    {
        $periodType = strtolower(trim((string) $this->request->getGet('period_type')));

        return in_array($periodType, ['year', 'month'], true) ? $periodType : 'year';
    }

    private function selectedAnalyticsMonth(): string
    {
        $month = trim((string) $this->request->getGet('month'));

        if (preg_match('/^\d{2}$/', $month) === 1 && (int) $month >= 1 && (int) $month <= 12) {
            return $month;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return substr($month, 5, 2);
        }

        $date = trim((string) $this->request->getGet('date'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            return substr($date, 5, 2);
        }

        return '';
    }

    private function analyticsYears(array $events, array $schoolReports, array $ojtReports, array $bannerReports): array
    {
        $years = [];

        foreach ($events as $event) {
            $year = substr((string) ($event['date'] ?? ''), 0, 4);
            if (preg_match('/^\d{4}$/', $year) === 1) {
                $years[$year] = $year;
            }
        }

        foreach ([
            [$schoolReports, 'created_at'],
            [$ojtReports, 'created_at'],
            [$bannerReports, 'installed_at'],
        ] as [$rows, $field]) {
            foreach ($rows as $row) {
                $year = substr((string) ($row[$field] ?? $row['created_at'] ?? ''), 0, 4);
                if (preg_match('/^\d{4}$/', $year) === 1) {
                    $years[$year] = $year;
                }
            }
        }

        rsort($years, SORT_STRING);

        return array_values($years);
    }

    private function analyticsPeriodLabel(string $year, string $month): string
    {
        if ($year === '' && $month === '') {
            return 'Semua periode';
        }

        if ($year !== '' && $month !== '') {
            return $this->analyticsMonthLabel($month, $year);
        }

        if ($year !== '') {
            return 'Tahun ' . $year;
        }

        return $this->analyticsMonthLabel($month);
    }

    private function analyticsMonthLabel(string $month, ?string $year = null): string
    {
        $date = new \DateTimeImmutable(($year ?: '2026') . '-' . $month . '-01');

        $label = $this->monthLabel($date);

        return $year === null ? trim((string) preg_replace('/\s+\d{4}$/', '', $label)) : $label;
    }

    private function formatOjtPeriod(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): string
    {
        $startLabel = $this->monthLabel($startDate);
        $endLabel = $this->monthLabel($endDate);

        return $startLabel === $endLabel ? $startLabel : $startLabel . ' - ' . $endLabel;
    }

    private function analyticsEventDistribution(array $events, array $categoryOptions): array
    {
        $groups = [
            'two_event' => [
                'label' => 'One Campus Two Event',
                'description' => 'Kategori event pemenuhan OCTE, tidak termasuk Openhouse dan Aplikasi PMB.',
                'count' => 0,
                'percent' => 0,
                'target' => 2,
                'target_label' => 'Target 2 event/bulan',
                'categories' => [],
                'tone' => 'blue',
            ],
            'hangout' => [
                'label' => 'One Campus Two Hangout',
                'description' => 'Khusus data Hangout/Open House.',
                'count' => 0,
                'percent' => 0,
                'target' => 2,
                'target_label' => 'Target 2 hangout/bulan',
                'categories' => [],
                'tone' => 'green',
            ],
        ];

        foreach ($categoryOptions as $categoryOption) {
            $category = (string) ($categoryOption['name'] ?? '');
            if ($category === '') {
                continue;
            }

            if ($this->isOpenhouseCategory($category)) {
                $groups['hangout']['categories'][$category] = 0;
            } elseif ($this->isOneCampusTwoEventCategory($category)) {
                $groups['two_event']['categories'][$category] = 0;
            }
        }

        foreach ($events as $event) {
            $category = (string) ($event['category'] ?? 'Tanpa kategori');
            if ($this->isOpenhouseCategory($category)) {
                $groupKey = 'hangout';
            } elseif ($this->isOneCampusTwoEventCategory($category)) {
                $groupKey = 'two_event';
            } else {
                continue;
            }

            $groups[$groupKey]['count']++;
            $groups[$groupKey]['categories'][$category] = ($groups[$groupKey]['categories'][$category] ?? 0) + 1;
        }

        $total = max(1, array_sum(array_map(static fn (array $group): int => (int) $group['count'], $groups)));
        foreach ($groups as $key => $group) {
            $max = max(1, max($group['categories'] ?: [0]));
            ksort($group['categories']);
            $categories = [];

            foreach ($group['categories'] as $name => $count) {
                $categories[] = [
                    'name' => $name,
                    'count' => $count,
                    'percent' => (int) round(($count / $max) * 100),
                ];
            }

            $groups[$key]['percent'] = (int) round(($group['count'] / $total) * 100);
            $groups[$key]['target_met'] = (int) $group['count'] >= (int) $group['target'];
            $groups[$key]['categories'] = $categories;
        }

        return array_values($groups);
    }

    private function isOpenhouseCategory(string $category): bool
    {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $category));

        return str_contains($normalized, 'openhouse') || str_contains($normalized, 'hangout');
    }

    private function isOneCampusTwoEventCategory(string $category): bool
    {
        return ! $this->isOpenhouseCategory($category) && ! $this->isAplikasiPmbCategory($category);
    }

    private function isOneCampusFulfillmentCategory(string $category): bool
    {
        return $this->isOpenhouseCategory($category) || $this->isOneCampusTwoEventCategory($category);
    }

    private function isAplikasiPmbCategory(string $category): bool
    {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $category));

        return str_contains($normalized, 'aplikasipmb');
    }

    private function filterOneCampusAnalyticsRows(array $rows, ?int $campusId, string $year, string $month, string $dateField): array
    {
        return array_values(array_filter($rows, static function (array $row) use ($campusId, $year, $month, $dateField): bool {
            if ($campusId !== null && (int) ($row['campus_id'] ?? 0) !== $campusId) {
                return false;
            }

            $date = (string) ($row[$dateField] ?? $row['created_at'] ?? '');
            if ($year !== '' && substr($date, 0, 4) !== $year) {
                return false;
            }

            if ($month === '') {
                return true;
            }

            return substr($date, 5, 2) === $month;
        }));
    }

    private function filterOjtReportsActiveInMonth(array $rows, string $year, string $month): array
    {
        $targetMonth = $year . '-' . $month;

        return array_values(array_filter($rows, function (array $row) use ($targetMonth): bool {
            return $this->isOjtReportActiveInMonth($row, $targetMonth);
        }));
    }

    private function isOjtReportActiveInMonth(array $row, string $targetMonth): bool
    {
        $startMonth = $this->monthKeyFromDate((string) ($row['ojt_start_month'] ?? ''));
        $endMonth = $this->monthKeyFromDate((string) ($row['ojt_end_month'] ?? ''));

        if ($startMonth === '' || $endMonth === '') {
            [$startMonth, $endMonth] = $this->legacyOjtPeriodMonthRange(
                (string) ($row['ojt_period'] ?? ''),
                (string) ($row['created_at'] ?? ''),
                $targetMonth
            );
        }

        if ($startMonth === '' || $endMonth === '') {
            return false;
        }

        return $startMonth <= $targetMonth && $endMonth >= $targetMonth;
    }

    private function monthKeyFromDate(string $date): string
    {
        return preg_match('/^\d{4}-\d{2}/', $date, $matches) === 1 ? $matches[0] : '';
    }

    private function legacyOjtPeriodMonthRange(string $period, string $createdAt, string $targetMonth): array
    {
        $monthNames = [
            'januari' => 1,
            'jan' => 1,
            'februari' => 2,
            'feb' => 2,
            'maret' => 3,
            'mar' => 3,
            'april' => 4,
            'apr' => 4,
            'mei' => 5,
            'may' => 5,
            'juni' => 6,
            'jun' => 6,
            'juli' => 7,
            'jul' => 7,
            'agustus' => 8,
            'agu' => 8,
            'ags' => 8,
            'aug' => 8,
            'september' => 9,
            'sep' => 9,
            'oktober' => 10,
            'okt' => 10,
            'oct' => 10,
            'november' => 11,
            'nov' => 11,
            'desember' => 12,
            'des' => 12,
            'dec' => 12,
        ];
        $pattern = '/\b(' . implode('|', array_map('preg_quote', array_keys($monthNames))) . ')\b(?:\s+(\d{4}))?/i';

        if (preg_match_all($pattern, $period, $matches, PREG_SET_ORDER) === 0) {
            return ['', ''];
        }

        $fallbackYear = '';
        if (preg_match_all('/\b(\d{4})\b/', $period, $yearMatches) > 0) {
            $fallbackYear = (string) end($yearMatches[1]);
        }
        if ($fallbackYear === '') {
            $fallbackYear = substr($createdAt, 0, 4) ?: substr($targetMonth, 0, 4);
        }

        $first = $matches[0];
        $last = $matches[count($matches) - 1];
        $startYear = (string) ($first[2] ?? '') ?: $fallbackYear;
        $endYear = (string) ($last[2] ?? '') ?: $fallbackYear;
        $startMonth = sprintf('%04d-%02d', (int) $startYear, $monthNames[strtolower($first[1])] ?? 0);
        $endMonth = sprintf('%04d-%02d', (int) $endYear, $monthNames[strtolower($last[1])] ?? 0);

        return [$startMonth, $endMonth];
    }

    private function analyticsOneCampusSections(array $schoolReports, array $ojtReports, array $bannerReports): array
    {
        return [
            [
                'label' => 'One Campus Sixty School',
                'count' => count($schoolReports),
                'meta' => array_sum(array_map(static fn (array $row): int => (int) ($row['pmb_count'] ?? 0), $schoolReports)) . ' PMB',
                'rows' => array_map(static fn (array $row): array => [
                    'title' => (string) ($row['school_name'] ?? '-'),
                    'detail' => trim((string) (($row['teacher_name'] ?? '-') . ' - ' . ($row['position'] ?? '-')), ' -'),
                    'campus' => (string) ($row['campus_name'] ?? 'Tanpa kampus'),
                    'date' => (string) ($row['created_at'] ?? ''),
                ], array_slice($schoolReports, 0, 5)),
                'empty' => 'Belum ada data school pada filter ini.',
            ],
            [
                'label' => 'One Campus Five OJT',
                'count' => count($ojtReports),
                'meta' => count($ojtReports) . ' peserta OJT',
                'rows' => array_map(static fn (array $row): array => [
                    'title' => (string) ($row['student_name'] ?? '-'),
                    'detail' => trim((string) (($row['origin_school'] ?? '-') . ' - ' . ($row['ojt_period'] ?? '-')), ' -'),
                    'campus' => (string) ($row['campus_name'] ?? 'Tanpa kampus'),
                    'date' => (string) ($row['created_at'] ?? ''),
                ], array_slice($ojtReports, 0, 5)),
                'empty' => 'Belum ada data OJT pada filter ini.',
            ],
            [
                'label' => 'One Campus Five Spanduk',
                'count' => count($bannerReports),
                'meta' => count($bannerReports) . ' titik spanduk',
                'rows' => array_map(static fn (array $row): array => [
                    'title' => (string) ($row['location'] ?? '-'),
                    'detail' => (string) ($row['theme'] ?? '-'),
                    'campus' => (string) ($row['campus_name'] ?? 'Tanpa kampus'),
                    'date' => (string) ($row['installed_at'] ?? $row['created_at'] ?? ''),
                ], array_slice($bannerReports, 0, 5)),
                'empty' => 'Belum ada data spanduk pada filter ini.',
            ],
        ];
    }

    private function analyticsCrmSummary(array $crmRows): array
    {
        $summary = [
            'total' => 0,
            'fresh' => 0,
            'stage2_responded' => 0,
            'stage2_no_response' => 0,
            'rejected' => 0,
            'closing' => 0,
            'score' => 0,
            'tone' => 'red',
        ];

        foreach ($crmRows as $row) {
            foreach (['total', 'fresh', 'stage2_responded', 'stage2_no_response', 'rejected', 'closing'] as $key) {
                $summary[$key] += (int) ($row[$key] ?? 0);
            }
        }

        if ($summary['total'] > 0) {
            $weighted = ($summary['closing'] * 100)
                + ($summary['stage2_responded'] * 55)
                + ($summary['stage2_no_response'] * 20)
                + ($summary['rejected'] * 5);
            $summary['score'] = (int) round($weighted / $summary['total']);
        }

        $summary['tone'] = $summary['score'] >= 70 ? 'green' : ($summary['score'] >= 40 ? 'amber' : 'red');

        return $summary;
    }

    private function analyticsCrmRows(array $crmRows): array
    {
        $rows = array_values(array_filter($crmRows, static fn (array $row): bool => (int) ($row['total'] ?? 0) > 0));

        usort($rows, static function (array $left, array $right): int {
            return ((int) ($right['total'] ?? 0) <=> (int) ($left['total'] ?? 0))
                ?: ((int) ($right['score'] ?? 0) <=> (int) ($left['score'] ?? 0))
                ?: strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
        });

        return $rows;
    }

    private function categories(array $events): array
    {
        $counts = [];
        foreach ($events as $event) {
            $counts[$event['category']] = ($counts[$event['category']] ?? 0) + 1;
        }

        foreach ((new EventCategoryModel())->orderBy('name', 'ASC')->findAll() as $category) {
            $counts[$category['name']] = $counts[$category['name']] ?? 0;
        }

        $max = max(1, max($counts ?: [0]));
        $categories = [];
        ksort($counts);

        foreach ($counts as $name => $count) {
            $categories[] = [
                'name' => $name,
                'count' => $count,
                'percent' => (int) round(($count / $max) * 100),
            ];
        }

        return $categories;
    }

    private function activities(array $events, array $reports): array
    {
        $activities = [];
        foreach (array_slice($events, 0, 2) as $event) {
            $activities[] = ['title' => 'Event ' . $event['status'], 'meta' => $event['id'] . ' oleh ' . $event['created_by'], 'time' => $event['time']];
        }
        foreach (array_slice($reports, 0, 1) as $report) {
            $activities[] = ['title' => 'Laporan ' . $report['realization_status'], 'meta' => $report['id'], 'time' => date('H:i')];
        }

        return $activities;
    }

    private function nextCode(string $prefix, string $table): string
    {
        $reserved = [];

        return $this->nextAvailableCode($prefix, $table, $reserved);
    }

    private function nextAvailableCode(string $prefix, string $table, array &$reservedCodes): string
    {
        $rows = db_connect()
            ->table($table)
            ->select('code')
            ->like('code', $prefix . '-', 'after')
            ->get()
            ->getResultArray();
        $max = 900;

        foreach ($rows as $row) {
            $code = strtoupper((string) ($row['code'] ?? ''));
            $reservedCodes[$code] = true;
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/i', $code, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        foreach (array_keys($reservedCodes) as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/i', (string) $code, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        do {
            $max++;
            $code = $prefix . '-' . str_pad((string) $max, 3, '0', STR_PAD_LEFT);
        } while (isset($reservedCodes[strtoupper($code)]));

        $reservedCodes[strtoupper($code)] = true;

        return $code;
    }

    private function notifyAdmins(string $message): void
    {
        $admins = (new UserModel())->where('role', 'Admin Sistem')->findAll();
        $notificationModel = new NotificationModel();

        foreach ($admins as $admin) {
            $notificationModel->insert([
                'user_id' => $admin['id'],
                'type' => 'approval',
                'message' => $message,
            ]);
        }
    }

    private function sendUserActivationEmail(string $recipient, string $name): bool
    {
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $config = [
            'protocol' => env('email.protocol', 'mail'),
            'SMTPHost' => env('email.SMTPHost', ''),
            'SMTPUser' => env('email.SMTPUser', ''),
            'SMTPPass' => env('email.SMTPPass', ''),
            'SMTPPort' => (int) env('email.SMTPPort', 25),
            'SMTPCrypto' => env('email.SMTPCrypto', 'tls'),
            'mailType' => env('email.mailType', 'text'),
            'charset' => 'UTF-8',
            'newline' => "\r\n",
            'CRLF' => "\r\n",
        ];

        $email = service('email', $config, false);
        $email->setFrom(env('email.fromEmail', 'noreply@eventhub.local'), env('email.fromName', 'EventHub'));
        $email->setTo($recipient);
        $email->setSubject('Akun EventHub Anda telah aktif');
        $email->setMessage(implode("\n\n", [
            'Halo ' . $name . ',',
            'Akun EventHub Anda telah disetujui oleh Admin Sistem dan sekarang sudah aktif.',
            'Silakan login melalui: ' . site_url('login'),
            'Jika Anda tidak merasa mendaftar akun ini, abaikan email ini atau hubungi Admin Sistem.',
        ]));

        try {
            return $email->send(false);
        } catch (\Throwable $error) {
            log_message('error', 'Gagal mengirim email aktivasi user: ' . $error->getMessage());

            return false;
        }
    }

    private function calendarIntegrationsByEvent(array $items): array
    {
        $mapped = [];
        foreach ($items as $item) {
            $mapped[$this->eventCode((int) $item['event_id'])] = $item;
        }

        return $mapped;
    }

    private function selectedCalendarMonth(): string
    {
        $month = (string) $this->request->getGet('month');

        if (preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return $month;
        }

        return date('Y-m');
    }

    private function calendarState(string $month, array $events): array
    {
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $firstDay = new \DateTimeImmutable($month . '-01', $timezone);
        $previousMonth = $firstDay->modify('first day of previous month')->format('Y-m');
        $nextMonth = $firstDay->modify('first day of next month')->format('Y-m');
        $daysInMonth = (int) $firstDay->format('t');
        $leadingDays = (int) $firstDay->format('N') - 1;
        $totalCells = (int) ceil(($leadingDays + $daysInMonth) / 7) * 7;
        $eventsByCode = [];
        foreach ($events as $event) {
            $eventsByCode[strtoupper((string) ($event['id'] ?? ''))] = $event;
        }

        $googleEvents = [];
        foreach ((new GoogleCalendarEventModel())->eventsForMonth((int) session('user.id'), $month) as $event) {
            $text = implode(' ', [
                (string) ($event['summary'] ?? ''),
                (string) ($event['location'] ?? ''),
                (string) ($event['description'] ?? ''),
            ]);

            if (preg_match('/EventHub code:\s*([A-Z]+-\d+)/i', (string) ($event['description'] ?? ''), $matches) === 1) {
                $linkedEvent = $eventsByCode[strtoupper($matches[1])] ?? null;
                if ($linkedEvent !== null) {
                    continue;
                }
            }

            $event['calendar_campus'] = $this->calendarCampusKey($text);

            $googleEvents[] = $event;
        }
        $visibleEvents = array_values(array_filter($events, static function (array $event) use ($month): bool {
            $start = (string) ($event['date'] ?? '');
            $end = (string) (($event['end_date'] ?? '') ?: $start);

            return $start <= $month . '-31' && $end >= $month . '-01';
        }));

        $cells = [];
        for ($index = 0; $index < $totalCells; $index++) {
            $dayNumber = $index - $leadingDays + 1;
            if ($dayNumber < 1 || $dayNumber > $daysInMonth) {
                $cells[] = ['date' => null, 'day' => null, 'localEvents' => [], 'googleEvents' => []];
                continue;
            }

            $date = $firstDay->setDate((int) $firstDay->format('Y'), (int) $firstDay->format('m'), $dayNumber)->format('Y-m-d');
            $cells[] = [
                'date' => $date,
                'day' => $dayNumber,
                'localEvents' => array_values(array_filter($events, static function (array $event) use ($date): bool {
                    $start = (string) ($event['date'] ?? '');
                    $end = (string) (($event['end_date'] ?? '') ?: $start);

                    return $start <= $date && $end >= $date;
                })),
                'googleEvents' => array_values(array_filter($googleEvents, static fn (array $event): bool => substr((string) $event['start_at'], 0, 10) === $date)),
            ];
        }

        return [
            'key' => $month,
            'label' => $this->monthLabel($firstDay),
            'previous' => $previousMonth,
            'next' => $nextMonth,
            'cells' => $cells,
            'visibleEvents' => $visibleEvents,
            'googleEvents' => $googleEvents,
        ];
    }

    private function calendarCampusKey(string $text): string
    {
        $text = strtoupper($text);

        if (preg_match('/\bUNM\b|NUSA\s+MANDIRI/', $text) === 1) {
            return 'unm';
        }

        if (preg_match('/\bCU\b|CYBER\s+(?:UNIVERSITY|UNIV)/', $text) === 1) {
            return 'cu';
        }

        if (preg_match('/\bUBSI\b|\bBSI\b|BINA\s+SARANA\s+INFORMATIKA/', $text) === 1) {
            return 'ubsi';
        }

        return 'other';
    }

    private function monthLabel(\DateTimeImmutable $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }

    private function accessOptions(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'crm' => 'CRM',
            'events' => 'Event',
            'calendar' => 'Kalender',
            'approvals' => 'Approval',
            'documents' => 'Dokumen',
            'reports' => 'Laporan',
            'one-campus' => 'One Campus',
            'analytics' => 'Analytics',
            'notifications' => 'Notifikasi',
            'users' => 'Users',
        ];
    }

    private function selectedPermissions(string $field = 'access_permissions', ?array $fallback = null): array
    {
        $selected = $this->request->getPost($field);
        $selected = is_array($selected) ? $selected : ($fallback ?? []);
        $allowed = array_keys($this->accessOptions());
        $permissions = array_values(array_intersect($allowed, $selected));

        if (! in_array('dashboard', $permissions, true)) {
            array_unshift($permissions, 'dashboard');
        }

        return array_values(array_unique($permissions));
    }

    private function permissionsFromRole(array $role): array
    {
        if (($role['name'] ?? '') === 'Admin Sistem') {
            return array_keys($this->accessOptions());
        }

        $decoded = json_decode((string) ($role['default_permissions'] ?? '[]'), true);

        return $this->normalizePermissions(is_array($decoded) ? $decoded : []);
    }

    private function normalizePermissions(array $permissions): array
    {
        $allowed = array_keys($this->accessOptions());
        $permissions = array_values(array_intersect($allowed, $permissions));

        if (! in_array('dashboard', $permissions, true)) {
            array_unshift($permissions, 'dashboard');
        }

        return array_values(array_unique($permissions));
    }

    private function activeRoleByName(string $name): ?array
    {
        $name = $this->normalizedRoleName($name);
        if ($name === '') {
            return null;
        }

        return (new RoleModel())->where('name', $name)->first();
    }

    private function normalizedRoleName(string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $name));
    }

    private function normalizedCrmRole(string $role): string
    {
        return in_array($role, ['Admin', 'Agent'], true) ? $role : 'Agent';
    }

    private function normalizedCrmStatus(string $status): string
    {
        return in_array($status, ['Active', 'Inactive', 'Review'], true) ? $status : 'Active';
    }

    private function normalizedCampusName(string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $name));
    }

    private function normalizedCategoryName(string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $name));
    }

    private function activeAdminCount(?int $excludeId = null): int
    {
        $builder = (new UserModel())
            ->where('role', 'Admin Sistem')
            ->where('status', 'Aktif')
            ->where('deleted_at', null);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults();
    }
}
