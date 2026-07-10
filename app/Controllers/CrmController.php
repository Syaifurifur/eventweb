<?php

namespace App\Controllers;

use App\Libraries\EventHubCrmBridge;
use App\Models\CampusModel;
use App\Models\CrmLeadLogModel;
use App\Models\CrmLeadModel;
use App\Models\CrmUploadBatchModel;
use App\Models\CrmUserModel;
use App\Models\EventModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class CrmController extends BaseController
{
    public function login()
    {
        if (session('crmLoggedIn') || (new EventHubCrmBridge())->signIn()) {
            return redirect()->to(site_url('crm'));
        }

        return view('pages/crm_login', [
            'title' => 'Login CRM',
            'canRegisterCrmUser' => session('isLoggedIn') && session('user.role') === 'Kepala Kampus' && (int) session('user.campus_id') > 0,
            'registeringCampus' => $this->currentEventHubCampus(),
        ]);
    }

    public function attempt()
    {
        $username = strtolower(trim((string) $this->request->getPost('username')));
        $password = (string) $this->request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $userModel = new CrmUserModel();
        $user = $userModel->where('username', $username)->first();

        if (! $user || ! password_verify($password, (string) $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password CRM salah.');
        }

        if (($user['status'] ?? '') === 'Review') {
            return redirect()->back()->withInput()->with('error', 'Akun CRM masih menunggu review admin.');
        }

        if (($user['status'] ?? '') !== 'Active') {
            return redirect()->back()->withInput()->with('error', 'Akun CRM tidak aktif.');
        }

        session()->regenerate(true);
        session()->set([
            'crmLoggedIn' => true,
            'crmUser' => [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'name' => $user['full_name'],
                'role' => $user['role'],
                'campus_id' => $user['campus_id'] ?? null,
                'registered_by' => $user['registered_by'] ?? null,
            ],
        ]);

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to(site_url('crm'))->with('success', 'Login CRM berhasil.');
    }

    public function storeRegistration()
    {
        if (session('crmLoggedIn')) {
            return redirect()->to(site_url('crm'));
        }

        if (! session('isLoggedIn') || session('user.role') !== 'Kepala Kampus') {
            return redirect()->to(site_url('crm/login'))->with('error', 'Pendaftaran user CRM hanya dapat dilakukan oleh Kepala Kampus.');
        }

        $campusId = (int) session('user.campus_id');
        $campus = $campusId > 0 ? (new CampusModel())->find($campusId) : null;
        if (! $campus) {
            return redirect()->to(site_url('crm/login'))->with('error', 'Kepala Kampus belum memiliki kampus pelaksana.');
        }

        $password = (string) $this->request->getPost('register_password');
        $confirmation = (string) $this->request->getPost('register_password_confirmation');

        if (strlen($password) < 6) {
            return redirect()->to(site_url('crm/login'))->withInput()->with('error', 'Password daftar CRM minimal 6 karakter.');
        }

        if ($password !== $confirmation) {
            return redirect()->to(site_url('crm/login'))->withInput()->with('error', 'Konfirmasi password CRM tidak sama.');
        }

        $fullName = trim((string) $this->request->getPost('register_full_name'));
        $model = new CrmUserModel();
        $ok = $model->insert([
            'username' => strtolower(trim((string) $this->request->getPost('register_username'))),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'role' => 'Agent',
            'status' => 'Review',
            'campus_id' => $campusId,
            'registered_by' => (int) session('user.id'),
        ]);

        if (! $ok) {
            return redirect()->to(site_url('crm/login'))->withInput()->with('error', implode(' ', $model->errors()));
        }

        $this->notifySystemAdmins('Pendaftaran akun CRM baru menunggu review: ' . $fullName . ' dari ' . session('user.name') . ' - ' . $campus['name'] . '.');

        return redirect()->to(site_url('crm/login'))->with('success', 'Pendaftaran CRM berhasil dikirim. Akun dapat digunakan setelah disetujui admin.');
    }

    public function logout()
    {
        session()->remove(['crmLoggedIn', 'crmUser']);

        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'))->with('success', 'Anda sudah keluar dari CRM. Selama session EventHub masih aktif, akses CRM dapat dibuka otomatis.');
        }

        return redirect()->to(site_url('crm/login'))->with('success', 'Anda sudah logout dari CRM.');
    }

    public function index(): string
    {
        $leadModel = new CrmLeadModel();
        $userModel = new CrmUserModel();
        $isAdmin = $this->isCrmAdmin();
        $currentUserId = (int) session('crmUser.id');
        $currentCrmCampusId = $this->currentCrmCampusId();

        $builder = $leadModel
            ->select('crm_leads.*, crm_users.full_name AS agent_name, crm_upload_batches.batch_code, events.code AS event_code, events.title AS event_title, campuses.name AS campus_name')
            ->join('crm_users', 'crm_users.id = crm_leads.agent_id', 'left')
            ->join('crm_upload_batches', 'crm_upload_batches.id = crm_leads.batch_id', 'left')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left')
            ->orderBy('crm_leads.id', 'ASC');

        if (! $isAdmin) {
            $builder->where('crm_leads.agent_id', $currentUserId);
        } elseif ($currentCrmCampusId !== null) {
            $builder->where('events.campus_id', $currentCrmCampusId);
        }

        $this->applyLeadFilters($builder);
        $allLeadBuilder = (new CrmLeadModel())
            ->select('crm_leads.*, events.campus_id')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left');
        if (! $isAdmin) {
            $allLeadBuilder->where('agent_id', $currentUserId);
        } elseif ($currentCrmCampusId !== null) {
            $allLeadBuilder->where('events.campus_id', $currentCrmCampusId);
        }
        $this->applyLeadFilters($allLeadBuilder);
        $allLeads = $allLeadBuilder->findAll();
        $perPage = 25;
        $totalLeads = count($allLeads);
        $totalPages = max(1, (int) ceil($totalLeads / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $leads = $builder->findAll($perPage, $offset);
        $events = $this->crmEventOptions();
        $crmUsersBuilder = $userModel->withDeleted()->orderBy('role', 'ASC')->orderBy('full_name', 'ASC');
        if ($currentCrmCampusId !== null) {
            $crmUsersBuilder->where('campus_id', $currentCrmCampusId);
        }
        $filters = [
            'q' => (string) $this->request->getGet('q'),
            'event_id' => (string) $this->request->getGet('event_id'),
            'campus_id' => (string) $this->request->getGet('campus_id'),
            'stage' => (string) $this->request->getGet('stage'),
            'label' => (string) $this->request->getGet('label'),
            'agent_id' => (string) $this->request->getGet('agent_id'),
            'school' => (string) $this->request->getGet('school'),
            'batch_id' => (string) $this->request->getGet('batch_id'),
            'from' => (string) $this->request->getGet('from'),
            'to' => (string) $this->request->getGet('to'),
            'unassigned' => (string) $this->request->getGet('unassigned'),
        ];
        $paginationQuery = array_filter($filters, static fn (string $value): bool => $value !== '');

        return view('pages/crm_dashboard', [
            'title' => 'CRM Admin',
            'crmUser' => session('crmUser'),
            'isAdmin' => $isAdmin,
            'stats' => $this->crmStats($allLeads),
            'leads' => $leads,
            'agents' => $this->crmAgentOptions($currentCrmCampusId),
            'users' => $crmUsersBuilder->findAll(),
            'batches' => $this->crmUploadBatches(),
            'events' => $events,
            'campuses' => $this->crmCampusOptions($currentCrmCampusId),
            'filters' => $filters,
            'leadPagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $totalLeads,
                'totalPages' => $totalPages,
                'from' => $totalLeads === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $totalLeads),
                'query' => $paginationQuery,
            ],
            'schools' => $this->schoolOptions($allLeads),
            'leadLogState' => $this->crmLeadLogState(),
        ]);
    }

    public function agentMonitoring(): string
    {
        $isAdmin = $this->isCrmAdmin();
        $currentUserId = (int) session('crmUser.id');
        $currentCrmCampusId = $this->currentCrmCampusId();

        $leadBuilder = (new CrmLeadModel())
            ->select('crm_leads.*, events.campus_id, campuses.name AS campus_name')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left');

        if (! $isAdmin) {
            $leadBuilder->where('crm_leads.agent_id', $currentUserId);
        } elseif ($currentCrmCampusId !== null) {
            $leadBuilder->where('events.campus_id', $currentCrmCampusId);
        }

        $leads = $leadBuilder->findAll();
        $agents = $this->crmAgentOptions($currentCrmCampusId);
        if (! $isAdmin) {
            $agents = array_values(array_filter($agents, static fn (array $agent): bool => (int) ($agent['id'] ?? 0) === $currentUserId));
        }

        return view('pages/crm_agent_monitoring', [
            'title' => 'Monitoring Agent',
            'crmUser' => session('crmUser'),
            'isAdmin' => $isAdmin,
            'campusScope' => $currentCrmCampusId,
            'agentMonitoring' => $this->crmAgentMonitoring($leads, $agents),
        ]);
    }

    public function storeUser()
    {
        $this->requireCrmAdmin();

        $password = (string) $this->request->getPost('password');
        if (strlen($password) < 6) {
            return redirect()->to(site_url('crm'))->withInput()->with('error', 'Password minimal 6 karakter.');
        }

        $model = new CrmUserModel();
        $ok = $model->insert([
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'role' => (string) $this->request->getPost('role') ?: 'Agent',
            'status' => (string) $this->request->getPost('status') ?: 'Active',
            'campus_id' => $this->currentCrmCampusId(),
        ]);

        if (! $ok) {
            return redirect()->to(site_url('crm'))->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(site_url('crm'))->with('success', 'User CRM berhasil dibuat.');
    }

    public function deleteUser(int $id)
    {
        $this->requireCrmAdmin();

        if ((int) session('crmUser.id') === $id) {
            return redirect()->to(site_url('crm'))->with('error', 'Akun CRM yang sedang dipakai tidak dapat dihapus.');
        }

        (new CrmUserModel())->delete($id);

        return redirect()->to(site_url('crm'))->with('success', 'User CRM berhasil dinonaktifkan.');
    }

    public function uploadLeads()
    {
        $this->requireCrmAdmin();

        $eventId = (int) $this->request->getPost('event_id');
        $event = $eventId > 0 ? (new EventModel())->find($eventId) : null;
        if (! $event) {
            return redirect()->to(site_url('crm'))->with('error', 'Pilih event sumber data CRM terlebih dahulu.');
        }

        if (! $this->crmAdminCanUseCampus((int) ($event['campus_id'] ?? 0))) {
            return redirect()->to(site_url('crm'))->with('error', 'Anda hanya dapat upload data CRM untuk kampus sendiri.');
        }

        $file = $this->request->getFile('lead_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('crm'))->with('error', 'File data calon pendaftar belum dipilih.');
        }

        $extension = strtolower($file->getClientExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            return redirect()->to(site_url('crm'))->with('error', 'Format file harus Excel .xlsx atau CSV.');
        }

        $rows = $extension === 'xlsx'
            ? $this->readXlsxRows($file->getTempName())
            : $this->readCsvRows($file->getTempName());

        if ($rows === []) {
            return redirect()->to(site_url('crm'))->with('error', 'File tidak berisi data yang bisa dibaca.');
        }

        $batchCode = 'BATCH_' . date('YmdHis') . '_' . random_int(100, 999);
        $batchId = (new CrmUploadBatchModel())->insert([
            'batch_code' => $batchCode,
            'file_name' => $file->getClientName(),
            'event_id' => $eventId,
            'total_rows' => count($rows),
            'uploaded_by' => (int) session('crmUser.id'),
            'uploaded_at' => date('Y-m-d H:i:s'),
        ], true);

        $payload = [];
        foreach ($rows as $row) {
            $payload[] = [
                'batch_id' => $batchId,
                'event_id' => $eventId,
                'student_name' => $row['student_name'],
                'school_origin' => $row['school_origin'] ?: null,
                'whatsapp' => $row['whatsapp'] ?: null,
                'stage' => 'Tahap 1',
                'label' => null,
                'agent_id' => null,
            ];
        }

        if ($payload === []) {
            (new CrmUploadBatchModel())->delete($batchId);

            return redirect()->to(site_url('crm'))->with('error', 'Tidak ada baris valid yang bisa disimpan. Pastikan kolom Nama Siswa terisi.');
        }

        (new CrmLeadModel())->insertBatch($payload);

        return redirect()->to(site_url('crm?event_id=' . $eventId))->with('success', count($payload) . ' data calon pendaftar berhasil diupload untuk event ' . $event['code'] . '.');
    }

    public function assignLeads()
    {
        $this->requireCrmAdmin();

        $ids = $this->selectedLeadIds();
        $agentId = (int) $this->request->getPost('agent_id');
        $agent = (new CrmUserModel())->where('status', 'Active')->whereIn('role', ['Agent', 'Admin'])->find($agentId);

        if ($ids === [] || ! $agent) {
            return redirect()->to(site_url('crm'))->with('error', 'Pilih data dan agent yang valid.');
        }

        $leads = $this->crmLeadsWithCampus($ids);
        if (count($leads) !== count($ids)) {
            return redirect()->to(site_url('crm'))->with('error', 'Sebagian data CRM tidak ditemukan.');
        }

        foreach ($leads as $lead) {
            $campusId = $this->leadCampusId($lead);
            if (! $this->canManageLead($lead)) {
                return redirect()->to(site_url('crm'))->with('error', 'Anda tidak memiliki akses ke sebagian data CRM yang dipilih.');
            }

            if (! $this->agentCanHandleCampus($agent, $campusId)) {
                return redirect()->to(site_url('crm'))->with('error', 'Agent hanya dapat diberi tugas dari kampus yang sama.');
            }
        }

        (new CrmLeadModel())->whereIn('id', array_column($leads, 'id'))->set(['agent_id' => $agentId])->update();
        foreach ($leads as $lead) {
            if ((int) ($lead['agent_id'] ?? 0) !== $agentId) {
                $this->logCrmLeadChange($lead, ['agent_id' => $agentId], 'assign', 'Assign ke ' . $agent['full_name']);
            }
        }

        return redirect()->to(site_url('crm'))->with('success', count($ids) . ' data berhasil diassign ke ' . $agent['full_name'] . '.');
    }

    public function autoAssignLeads()
    {
        $this->requireCrmAdmin();

        $agents = $this->crmAgentOptions($this->currentCrmCampusId(), true);
        if ($agents === []) {
            return redirect()->to(site_url('crm'))->with('error', 'Belum ada user Agent aktif.');
        }

        $leadBuilder = (new CrmLeadModel())
            ->select('crm_leads.*, events.campus_id AS event_campus_id')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->where('crm_leads.agent_id', null)
            ->orderBy('crm_leads.id', 'ASC');
        if ($this->currentCrmCampusId() !== null) {
            $leadBuilder->where('events.campus_id', $this->currentCrmCampusId());
        }
        $leads = $leadBuilder->findAll();
        if ($leads === []) {
            return redirect()->to(site_url('crm'))->with('error', 'Tidak ada data unassigned.');
        }

        $model = new CrmLeadModel();
        $agentsByCampus = [];
        foreach ($agents as $agent) {
            $agentsByCampus[(int) ($agent['campus_id'] ?? 0)][] = $agent;
        }

        $assigned = 0;
        $campusIndexes = [];
        foreach ($leads as $lead) {
            $campusKey = (int) ($lead['event_campus_id'] ?? 0);
            $campusAgents = $agentsByCampus[$campusKey] ?? [];
            if ($campusAgents === []) {
                continue;
            }

            $index = $campusIndexes[$campusKey] ?? 0;
            $agent = $campusAgents[$index % count($campusAgents)];
            $model->update($lead['id'], ['agent_id' => $agent['id']]);
            $this->logCrmLeadChange($lead, ['agent_id' => $agent['id']], 'auto_assign', 'Auto assign ke ' . $agent['full_name']);
            $campusIndexes[$campusKey] = $index + 1;
            $assigned++;
        }

        if ($assigned === 0) {
            return redirect()->to(site_url('crm'))->with('error', 'Tidak ada agent aktif yang sesuai dengan kampus data CRM.');
        }

        return redirect()->to(site_url('crm'))->with('success', $assigned . ' data unassigned berhasil dibagi otomatis sesuai kampus.');
    }

    public function updateLead(int $id)
    {
        $leadModel = new CrmLeadModel();
        $lead = $leadModel
            ->select('crm_leads.*, events.campus_id AS event_campus_id')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->find($id);

        if (! $lead) {
            return redirect()->to(site_url('crm'))->with('error', 'Data calon pendaftar tidak ditemukan.');
        }

        if (! $this->canManageLead($lead)) {
            return redirect()->to(site_url('crm'))->with('error', 'Anda tidak memiliki akses ke data ini.');
        }

        $payload = [
            'stage' => (string) $this->request->getPost('stage') ?: $lead['stage'],
            'label' => (string) $this->request->getPost('label') ?: null,
            'notes' => trim((string) $this->request->getPost('notes')) ?: null,
            'rejection_reason' => trim((string) $this->request->getPost('rejection_reason')) ?: null,
        ];
        if ($payload['label'] === 'Closing') {
            $payload['stage'] = 'Tahap 3';
        }

        $supportsLabelReason = in_array($payload['stage'], ['Tahap 2', 'Tahap 3'], true)
            && in_array($payload['label'], ['Minat/Tertarik', 'Ragu-ragu', 'Menolak'], true);
        if (! $supportsLabelReason) {
            $payload['rejection_reason'] = null;
        }

        if (
            $payload['stage'] !== ($lead['stage'] ?? '')
            || $payload['label'] !== ($lead['label'] ?? null)
            || $payload['rejection_reason'] !== ($lead['rejection_reason'] ?? null)
        ) {
            $payload['responded_at'] = date('Y-m-d H:i:s');
        }

        if ($this->isCrmAdmin()) {
            $agentId = (int) $this->request->getPost('agent_id');
            if ($agentId > 0) {
                $agent = (new CrmUserModel())->where('status', 'Active')->whereIn('role', ['Agent', 'Admin'])->find($agentId);
                if (! $agent || ! $this->agentCanHandleCampus($agent, $this->leadCampusId($lead))) {
                    return redirect()->to(site_url('crm'))->with('error', 'Agent hanya dapat diberi tugas dari kampus yang sama.');
                }
            }
            $payload['agent_id'] = $agentId > 0 ? $agentId : null;
        }

        $changedFields = $this->crmLeadChangedFields($lead, $payload);
        if (! $leadModel->update($id, $payload)) {
            return redirect()->to(site_url('crm'))->with('error', implode(' ', $leadModel->errors()) ?: 'Data calon pendaftar gagal diperbarui.');
        }
        if ($changedFields !== []) {
            $this->logCrmLeadChange($lead, $payload, 'update', $this->crmLeadLogSummary($changedFields, $lead, $payload));
        }

        return redirect()->to(site_url('crm'))->with('success', 'Data calon pendaftar berhasil diperbarui.');
    }

    public function deleteLead(int $id)
    {
        $this->requireCrmAdmin();

        $lead = (new CrmLeadModel())
            ->select('crm_leads.*, events.campus_id AS event_campus_id')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->find($id);
        if ($lead && ! $this->canManageLead($lead)) {
            return redirect()->to(site_url('crm'))->with('error', 'Anda tidak memiliki akses ke data ini.');
        }

        if ($lead) {
            $this->logCrmLeadChange($lead, [], 'delete', 'Data CRM dihapus.');
        }
        (new CrmLeadModel())->delete($id);

        return redirect()->to(site_url('crm'))->with('success', 'Data calon pendaftar berhasil dihapus.');
    }

    public function deleteBatch(int $id)
    {
        $this->requireCrmAdmin();

        $leads = (new CrmLeadModel())
            ->select('crm_leads.*, events.campus_id AS event_campus_id')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->where('batch_id', $id)
            ->findAll();
        foreach ($leads as $lead) {
            if ($this->canManageLead($lead)) {
                $this->logCrmLeadChange($lead, [], 'batch_delete', 'Data CRM dihapus bersama batch upload.');
            }
        }
        (new CrmLeadModel())->where('batch_id', $id)->delete();
        (new CrmUploadBatchModel())->delete($id);

        return redirect()->to(site_url('crm'))->with('success', 'Batch upload dan datanya berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $csv = "No,Nama Siswa,Asal Sekolah,No WA\n1,Contoh Siswa,SMA Contoh,6281234567890\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="template-crm-calon-pendaftar.csv"')
            ->setBody($csv);
    }

    private function applyLeadFilters($builder): void
    {
        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $builder
                ->groupStart()
                ->like('student_name', $q)
                ->orLike('school_origin', $q)
                ->orLike('whatsapp', $q)
                ->groupEnd();
        }

        foreach (['stage', 'label'] as $field) {
            $value = trim((string) $this->request->getGet($field));
            if ($value !== '') {
                $builder->where('crm_leads.' . $field, $value);
            }
        }

        $school = trim((string) $this->request->getGet('school'));
        if ($school !== '') {
            $builder->where('school_origin', $school);
        }

        $eventId = (int) $this->request->getGet('event_id');
        if ($eventId > 0) {
            $builder->where('crm_leads.event_id', $eventId);
        }

        $campusScope = $this->currentCrmCampusId();
        $campusId = $campusScope ?? (int) $this->request->getGet('campus_id');
        if ($campusId > 0) {
            $builder->where('events.campus_id', $campusId);
        }

        $batchId = (int) $this->request->getGet('batch_id');
        if ($batchId > 0) {
            $builder->where('batch_id', $batchId);
        }

        $agentId = (int) $this->request->getGet('agent_id');
        if ($agentId > 0) {
            $builder->where('agent_id', $agentId);
        }

        if ((string) $this->request->getGet('unassigned') === '1') {
            $builder->where('agent_id', null);
        }

        $from = (string) $this->request->getGet('from');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
            $builder->where('DATE(crm_leads.created_at) >=', $from);
        }

        $to = (string) $this->request->getGet('to');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1) {
            $builder->where('DATE(crm_leads.created_at) <=', $to);
        }
    }

    private function crmStats(array $leads): array
    {
        $stats = [
            'total' => count($leads),
            'unassigned' => 0,
            'stage1' => 0,
            'stage2' => 0,
            'stage3' => 0,
            'closing' => 0,
            'interested' => 0,
            'hesitant' => 0,
            'rejected' => 0,
            'no_response' => 0,
        ];

        foreach ($leads as $lead) {
            if (empty($lead['agent_id'])) {
                $stats['unassigned']++;
            }

            if (($lead['stage'] ?? '') === 'Tahap 1') {
                $stats['stage1']++;
            } elseif (($lead['stage'] ?? '') === 'Tahap 2') {
                $stats['stage2']++;
            } elseif (($lead['stage'] ?? '') === 'Tahap 3') {
                $stats['stage3']++;
            }

            if (($lead['label'] ?? '') === 'Closing') {
                $stats['closing']++;
            } elseif (($lead['label'] ?? '') === 'Minat/Tertarik') {
                $stats['interested']++;
            } elseif (($lead['label'] ?? '') === 'Ragu-ragu') {
                $stats['hesitant']++;
            } elseif (($lead['label'] ?? '') === 'Menolak') {
                $stats['rejected']++;
            } elseif (($lead['label'] ?? '') === 'Tidak Merespon') {
                $stats['no_response']++;
            }
        }

        return $stats;
    }

    private function crmLeadLogState(int $perPage = 8): array
    {
        $db = db_connect();
        if (! $db->tableExists('crm_lead_logs')) {
            return [
                'rows' => [],
                'selectedLabel' => '',
                'pagination' => [
                    'current' => 1,
                    'perPage' => $perPage,
                    'total' => 0,
                    'totalPages' => 1,
                    'from' => 0,
                    'to' => 0,
                    'query' => $this->crmLeadLogQuery(),
                ],
            ];
        }

        $selectedLabel = trim((string) $this->request->getGet('log_label'));
        $countBuilder = $this->crmLeadLogBuilder($db, $selectedLabel, false);
        $total = $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('log_page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $rows = $this->crmLeadLogBuilder($db, $selectedLabel, true)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'rows' => $rows,
            'selectedLabel' => $selectedLabel,
            'pagination' => [
                'current' => $currentPage,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => min($offset + $perPage, $total),
                'query' => $this->crmLeadLogQuery(),
            ],
        ];
    }

    private function crmLeadLogBuilder($db, string $selectedLabel, bool $withOrder)
    {
        $builder = $db->table('crm_lead_logs')
            ->select('crm_lead_logs.*, crm_leads.student_name, crm_leads.school_origin, crm_users.full_name AS actor_name, events.campus_id AS event_campus_id')
            ->join('crm_leads', 'crm_leads.id = crm_lead_logs.lead_id', 'left')
            ->join('crm_users', 'crm_users.id = crm_lead_logs.user_id', 'left')
            ->join('events', 'events.id = crm_leads.event_id', 'left');

        if ($selectedLabel !== '') {
            $builder->where('crm_lead_logs.new_label', $selectedLabel);
        }

        if (! $this->isCrmAdmin()) {
            $builder->where('crm_leads.agent_id', (int) session('crmUser.id'));
        } elseif ($this->currentCrmCampusId() !== null) {
            $builder->where('events.campus_id', $this->currentCrmCampusId());
        }

        if ($withOrder) {
            $builder
                ->orderBy('crm_lead_logs.created_at', 'DESC')
                ->orderBy('crm_lead_logs.id', 'DESC');
        }

        return $builder;
    }

    private function crmLeadLogQuery(): array
    {
        $query = $this->request->getGet();
        unset($query['log_page']);

        return array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);
    }

    private function logCrmLeadChange(array $lead, array $newData, string $action, string $summary): void
    {
        $db = db_connect();
        if (! $db->tableExists('crm_lead_logs')) {
            return;
        }

        (new CrmLeadLogModel())->insert([
            'lead_id' => (int) ($lead['id'] ?? 0) ?: null,
            'user_id' => (int) session('crmUser.id') ?: null,
            'action' => $action,
            'old_stage' => $lead['stage'] ?? null,
            'new_stage' => array_key_exists('stage', $newData) ? ($newData['stage'] ?: null) : ($lead['stage'] ?? null),
            'old_label' => $lead['label'] ?? null,
            'new_label' => array_key_exists('label', $newData) ? ($newData['label'] ?: null) : ($lead['label'] ?? null),
            'old_agent_id' => empty($lead['agent_id']) ? null : (int) $lead['agent_id'],
            'new_agent_id' => array_key_exists('agent_id', $newData)
                ? (empty($newData['agent_id']) ? null : (int) $newData['agent_id'])
                : (empty($lead['agent_id']) ? null : (int) $lead['agent_id']),
            'old_rejection_reason' => $lead['rejection_reason'] ?? null,
            'new_rejection_reason' => array_key_exists('rejection_reason', $newData)
                ? ($newData['rejection_reason'] ?: null)
                : ($lead['rejection_reason'] ?? null),
            'summary' => $summary,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function crmLeadChangedFields(array $lead, array $payload): array
    {
        $fields = ['stage', 'label', 'agent_id', 'notes', 'rejection_reason'];
        $changed = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            if ($this->crmLeadComparableValue($lead[$field] ?? null) !== $this->crmLeadComparableValue($payload[$field] ?? null)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    private function crmLeadComparableValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function crmLeadLogSummary(array $changedFields, array $oldLead, array $newLead): string
    {
        $parts = [];

        if (in_array('stage', $changedFields, true)) {
            $parts[] = 'Tahap: ' . $this->crmLeadDisplayValue($oldLead['stage'] ?? null) . ' -> ' . $this->crmLeadDisplayValue($newLead['stage'] ?? null);
        }
        if (in_array('label', $changedFields, true)) {
            $parts[] = 'Label: ' . $this->crmLeadDisplayValue($oldLead['label'] ?? null) . ' -> ' . $this->crmLeadDisplayValue($newLead['label'] ?? null);
        }
        if (in_array('agent_id', $changedFields, true)) {
            $parts[] = 'Agent diperbarui';
        }
        if (in_array('notes', $changedFields, true)) {
            $parts[] = 'Catatan diperbarui';
        }
        if (in_array('rejection_reason', $changedFields, true)) {
            $parts[] = 'Alasan/keterangan label diperbarui';
        }

        return implode('; ', $parts) ?: 'Data CRM diperbarui.';
    }

    private function crmLeadDisplayValue($value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? '-' : $value;
    }

    private function crmAgentMonitoring(array $leads, array $agents): array
    {
        $rows = [];
        foreach ($agents as $agent) {
            $agentId = (int) ($agent['id'] ?? 0);
            if ($agentId <= 0) {
                continue;
            }

            $rows[$agentId] = [
                'id' => $agentId,
                'name' => (string) ($agent['full_name'] ?? 'Agent'),
                'campus' => (string) ($agent['campus_name'] ?? ''),
                'assigned' => 0,
                'followed_up' => 0,
                'stage2' => 0,
                'stage3' => 0,
                'closing' => 0,
                'interested' => 0,
                'hesitant' => 0,
                'rejected' => 0,
                'no_response' => 0,
                'score' => 0,
                'grade' => 'E',
                'grade_class' => 'crm-grade-e',
                'status' => 'Belum ada data',
            ];
        }

        foreach ($leads as $lead) {
            $agentId = (int) ($lead['agent_id'] ?? 0);
            if ($agentId <= 0 || ! isset($rows[$agentId])) {
                continue;
            }

            $stage = (string) ($lead['stage'] ?? '');
            $label = (string) ($lead['label'] ?? '');
            $rows[$agentId]['assigned']++;

            if ($label !== '' || $stage !== 'Tahap 1' || ! empty($lead['responded_at'])) {
                $rows[$agentId]['followed_up']++;
            }

            if ($stage === 'Tahap 2') {
                $rows[$agentId]['stage2']++;
            } elseif ($stage === 'Tahap 3') {
                $rows[$agentId]['stage3']++;
            }

            if ($label === 'Closing') {
                $rows[$agentId]['closing']++;
            } elseif ($label === 'Minat/Tertarik') {
                $rows[$agentId]['interested']++;
            } elseif ($label === 'Ragu-ragu') {
                $rows[$agentId]['hesitant']++;
            } elseif ($label === 'Menolak') {
                $rows[$agentId]['rejected']++;
            } elseif ($label === 'Tidak Merespon') {
                $rows[$agentId]['no_response']++;
            }
        }

        foreach ($rows as &$row) {
            $assigned = max(1, (int) $row['assigned']);
            $followUpRate = $row['followed_up'] / $assigned;
            $progressRate = ($row['stage2'] + $row['stage3']) / $assigned;
            $advancedRate = $row['stage3'] / $assigned;
            $closingRate = $row['closing'] / $assigned;
            $positiveRate = $row['interested'] / $assigned;
            $rejectedRate = $row['rejected'] / $assigned;
            $noResponseRate = $row['no_response'] / $assigned;

            $score = (int) round(
                ($closingRate * 60)
                + ($followUpRate * 20)
                + ($progressRate * 10)
                + ($positiveRate * 5)
                + ($advancedRate * 5)
                - ($rejectedRate * 10)
                - ($noResponseRate * 5)
            );
            $row['score'] = max(0, min(100, $score));
            $row['follow_up_rate'] = (int) round($followUpRate * 100);
            $row['conversion_rate'] = (int) round($closingRate * 100);
            $row['grade'] = $this->crmAgentGrade($row['score']);
            $row['grade_class'] = 'crm-grade-' . strtolower($row['grade']);
            $row['status'] = $this->crmAgentPerformanceStatus($row['score'], (int) $row['assigned']);
        }
        unset($row);

        usort($rows, static function (array $left, array $right): int {
            return ((int) $right['score'] <=> (int) $left['score'])
                ?: ((int) $right['assigned'] <=> (int) $left['assigned'])
                ?: strcmp((string) $left['name'], (string) $right['name']);
        });

        return array_values($rows);
    }

    private function crmAgentGrade(int $score): string
    {
        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 70) {
            return 'B';
        }
        if ($score >= 55) {
            return 'C';
        }
        if ($score >= 40) {
            return 'D';
        }

        return 'E';
    }

    private function crmAgentPerformanceStatus(int $score, int $assigned): string
    {
        if ($assigned === 0) {
            return 'Belum ada data';
        }
        if ($score >= 85) {
            return 'Sangat baik';
        }
        if ($score >= 70) {
            return 'Baik';
        }
        if ($score >= 55) {
            return 'Perlu dipantau';
        }
        if ($score >= 40) {
            return 'Butuh pendampingan';
        }

        return 'Prioritas evaluasi';
    }

    private function schoolOptions(array $leads): array
    {
        $schools = [];
        foreach ($leads as $lead) {
            $school = trim((string) ($lead['school_origin'] ?? ''));
            if ($school !== '') {
                $schools[$school] = $school;
            }
        }

        ksort($schools);

        return array_values($schools);
    }

    private function crmEventOptions(): array
    {
        $builder = (new EventModel())
            ->select('events.id, events.code, events.title, events.event_date, campuses.name AS campus_name')
            ->join('campuses', 'campuses.id = events.campus_id', 'left')
            ->orderBy('campuses.name', 'ASC')
            ->orderBy('events.event_date', 'DESC');

        if ($this->currentCrmCampusId() !== null) {
            $builder->where('events.campus_id', $this->currentCrmCampusId());
        }

        return $builder->findAll();
    }

    private function crmUploadBatches(): array
    {
        $builder = db_connect()
            ->table('crm_upload_batches')
            ->select('crm_upload_batches.*, events.code AS event_code, events.title AS event_title, campuses.name AS campus_name')
            ->join('events', 'events.id = crm_upload_batches.event_id', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left')
            ->orderBy('crm_upload_batches.uploaded_at', 'DESC')
            ->limit(8);

        if ($this->currentCrmCampusId() !== null) {
            $builder->where('events.campus_id', $this->currentCrmCampusId());
        }

        return $builder
            ->get()
            ->getResultArray();
    }

    private function selectedLeadIds(): array
    {
        $ids = $this->request->getPost('lead_ids');
        $ids = is_array($ids) ? $ids : [];

        return array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return [];
        }

        $rows = [];
        $headers = [];
        $delimiter = null;
        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === []) {
                $delimiter = $this->detectCsvDelimiter($line);
                if ($delimiter !== ',' && count($line) === 1) {
                    $line = str_getcsv((string) $line[0], $delimiter);
                }
                $headers = $this->normalizeHeaders($line);
                continue;
            }

            if ($delimiter !== null && $delimiter !== ',' && count($line) === 1) {
                $line = str_getcsv((string) $line[0], $delimiter);
            }

            $mapped = $this->mapLeadRow($headers, $line);
            if ($mapped !== null) {
                $rows[] = $mapped;
            }
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        $sharedStrings = $this->xlsxSharedStringsFromXml($this->xlsxEntryContents($path, 'xl/sharedStrings.xml'));
        $sheetXml = $this->xlsxEntryContents($path, 'xl/worksheets/sheet1.xml');

        if (! is_string($sheetXml) || $sheetXml === '') {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        if (! $xml) {
            return [];
        }

        $headers = [];
        $rows = [];
        $rowNumber = 0;
        foreach ($xml->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $value = $this->xlsxCellValue($cell, $sharedStrings);
                $columnIndex = $this->xlsxColumnIndex((string) $cell['r']);
                $values[$columnIndex] = trim($value);
            }

            ksort($values);
            $values = $this->denseRowValues($values);

            if ($rowNumber === 0) {
                $headers = $this->normalizeHeaders($values);
                $rowNumber++;
                continue;
            }

            $mapped = $this->mapLeadRow($headers, $values);
            if ($mapped !== null) {
                $rows[] = $mapped;
            }
            $rowNumber++;
        }

        return $rows;
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

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            return $sharedStrings[(int) ((string) $cell->v)] ?? '';
        }

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return (string) $cell->is->t;
            }

            $text = '';
            foreach ($cell->is->r as $run) {
                $text .= (string) $run->t;
            }

            return $text;
        }

        return (string) $cell->v;
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

    private function denseRowValues(array $values): array
    {
        $max = $values === [] ? -1 : max(array_keys($values));
        $dense = [];

        for ($index = 0; $index <= $max; $index++) {
            $dense[] = $values[$index] ?? '';
        }

        return $dense;
    }

    private function xlsxSharedStringsFromXml(?string $xml): array
    {
        if (! is_string($xml) || $xml === '') {
            return [];
        }

        $shared = simplexml_load_string($xml);
        if (! $shared) {
            return [];
        }

        $strings = [];
        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = trim((string) $item->t);
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = trim($text);
        }

        return $strings;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(static function ($header): string {
            $header = strtolower(trim((string) $header));
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;

            return trim($header, '_');
        }, $headers);
    }

    private function detectCsvDelimiter(array $firstLine): string
    {
        if (count($firstLine) > 1) {
            return ',';
        }

        $raw = (string) ($firstLine[0] ?? '');
        $delimiters = [',', ';', "\t"];
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = substr_count($raw, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function mapLeadRow(array $headers, array $values): ?array
    {
        $row = [];
        foreach ($headers as $index => $header) {
            $row[$header] = trim((string) ($values[$index] ?? ''));
        }

        $name = $row['nama_siswa'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? $row['student_name'] ?? $row['name'] ?? '';
        $school = $row['asal_sekolah'] ?? $row['sekolah_asal'] ?? $row['sekolah'] ?? $row['school_origin'] ?? $row['school'] ?? '';
        $wa = $row['no_wa']
            ?? $row['nomor_wa']
            ?? $row['no_whatsapp']
            ?? $row['nomor_whatsapp']
            ?? $row['whatsapp']
            ?? $row['wa']
            ?? $row['hp']
            ?? $row['no_hp']
            ?? $row['nomor_hp']
            ?? $row['no_telp']
            ?? $row['nomor_telp']
            ?? $row['no_telepon']
            ?? $row['nomor_telepon']
            ?? $row['telp']
            ?? $row['telepon']
            ?? $row['phone']
            ?? $row['phone_number']
            ?? '';

        if (trim($name) === '') {
            return null;
        }

        return [
            'student_name' => trim($name),
            'school_origin' => trim($school),
            'whatsapp' => $this->normalizeWhatsappNumber($wa),
        ];
    }

    private function normalizeWhatsappNumber(string $value): string
    {
        $number = preg_replace('/\D+/', '', trim($value)) ?? '';

        if ($number === '') {
            return '';
        }

        if (str_starts_with($number, '00')) {
            $number = substr($number, 2);
        }

        if (str_starts_with($number, '0')) {
            return '62' . substr($number, 1);
        }

        if (str_starts_with($number, '8')) {
            return '62' . $number;
        }

        return $number;
    }

    private function canManageLead(array $lead): bool
    {
        if (! $this->isCrmAdmin()) {
            return (int) ($lead['agent_id'] ?? 0) === (int) session('crmUser.id');
        }

        $campusScope = $this->currentCrmCampusId();
        if ($campusScope === null) {
            return true;
        }

        return $this->leadCampusId($lead) === $campusScope;
    }

    private function isCrmAdmin(): bool
    {
        return (string) session('crmUser.role') === 'Admin';
    }

    private function requireCrmAdmin(): void
    {
        if (! $this->isCrmAdmin()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman CRM tidak ditemukan.');
        }
    }

    private function currentCrmCampusId(): ?int
    {
        $campusId = (int) session('crmUser.campus_id');

        return $campusId > 0 ? $campusId : null;
    }

    private function currentEventHubCampus(): ?array
    {
        $campusId = (int) session('user.campus_id');
        if (! session('isLoggedIn') || $campusId <= 0) {
            return null;
        }

        return (new CampusModel())->find($campusId) ?: null;
    }

    private function crmAdminCanUseCampus(int $campusId): bool
    {
        $scope = $this->currentCrmCampusId();

        return $scope === null || $scope === $campusId;
    }

    private function leadCampusId(array $lead): ?int
    {
        $campusId = (int) ($lead['event_campus_id'] ?? $lead['campus_id'] ?? 0);
        if ($campusId > 0) {
            return $campusId;
        }

        $eventId = (int) ($lead['event_id'] ?? 0);
        if ($eventId <= 0) {
            return null;
        }

        $event = (new EventModel())->find($eventId);
        $campusId = (int) ($event['campus_id'] ?? 0);

        return $campusId > 0 ? $campusId : null;
    }

    private function agentCanHandleCampus(array $agent, ?int $campusId): bool
    {
        $agentCampusId = (int) ($agent['campus_id'] ?? 0);

        return $campusId !== null && $agentCampusId === $campusId;
    }

    private function crmLeadsWithCampus(array $ids): array
    {
        return (new CrmLeadModel())
            ->select('crm_leads.*, events.campus_id AS event_campus_id')
            ->join('events', 'events.id = crm_leads.event_id', 'left')
            ->whereIn('crm_leads.id', $ids)
            ->findAll();
    }

    private function crmAgentOptions(?int $campusId = null, bool $agentsOnly = false): array
    {
        $builder = (new CrmUserModel())
            ->select('crm_users.*, campuses.name AS campus_name')
            ->join('campuses', 'campuses.id = crm_users.campus_id', 'left')
            ->where('crm_users.status', 'Active');

        if ($agentsOnly) {
            $builder->where('crm_users.role', 'Agent');
        } else {
            $builder->whereIn('crm_users.role', ['Agent', 'Admin']);
        }

        if ($campusId !== null) {
            $builder->where('crm_users.campus_id', $campusId);
        }

        return $builder
            ->orderBy('campuses.name', 'ASC')
            ->orderBy('crm_users.full_name', 'ASC')
            ->findAll();
    }

    private function crmCampusOptions(?int $campusId = null): array
    {
        $builder = (new CampusModel())->orderBy('name', 'ASC');
        if ($campusId !== null) {
            $builder->where('id', $campusId);
        }

        return $builder->findAll();
    }

    private function notifySystemAdmins(string $message): void
    {
        $admins = (new UserModel())->where('role', 'Admin Sistem')->where('status', 'Aktif')->findAll();
        $notificationModel = new NotificationModel();

        foreach ($admins as $admin) {
            $notificationModel->insert([
                'user_id' => $admin['id'],
                'type' => 'user',
                'message' => $message,
            ]);
        }
    }
}
