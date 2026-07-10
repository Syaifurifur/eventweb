<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EventHubSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $password = password_hash('password', PASSWORD_DEFAULT);
        $roleRows = [
            ['name' => 'Admin Sistem', 'description' => 'Role sistem dengan akses penuh dan tidak dapat dihapus.', 'is_system' => 1, 'default_permissions' => json_encode(['dashboard', 'crm', 'events', 'calendar', 'campuses', 'approvals', 'documents', 'reports', 'analytics', 'notifications', 'users']), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Event Creator', 'description' => 'Membuat event, dokumen, laporan, dan memantau kalender.', 'is_system' => 0, 'default_permissions' => json_encode(['dashboard', 'events', 'calendar', 'documents', 'reports', 'notifications']), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pimpinan', 'description' => 'Review approval, laporan, analytics, dan agenda kalender.', 'is_system' => 0, 'default_permissions' => json_encode(['dashboard', 'calendar', 'approvals', 'reports', 'analytics', 'notifications']), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Markom', 'description' => 'Mengelola event marketing dan data CRM sesuai kampus.', 'is_system' => 0, 'default_permissions' => json_encode(['dashboard', 'crm', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications']), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Marketing Akademik', 'description' => 'Mengelola event promosi akademik, dokumen, dan laporan.', 'is_system' => 0, 'default_permissions' => json_encode(['dashboard', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications']), 'created_at' => $now, 'updated_at' => $now],
        ];

        if ($this->db->tableExists('roles')) {
            $this->db->table('roles')->ignore(true)->insertBatch($roleRows);
        }

        $users = [
            ['name' => 'Nadia Putri', 'username' => 'admin', 'email' => 'admin@eventhub.test', 'password_hash' => $password, 'role' => 'Admin Sistem', 'access_permissions' => json_encode(['dashboard', 'crm', 'events', 'calendar', 'campuses', 'approvals', 'documents', 'reports', 'analytics', 'notifications', 'users']), 'status' => 'Aktif', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Raka Wibowo', 'username' => 'raka', 'email' => 'raka@eventhub.test', 'password_hash' => $password, 'role' => 'Event Creator', 'access_permissions' => json_encode(['dashboard', 'events', 'calendar', 'documents', 'reports', 'notifications']), 'status' => 'Aktif', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dimas Arya', 'username' => 'dimas', 'email' => 'dimas@eventhub.test', 'password_hash' => $password, 'role' => 'Pimpinan', 'access_permissions' => json_encode(['dashboard', 'calendar', 'approvals', 'reports', 'analytics', 'notifications']), 'status' => 'Aktif', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maya Sari', 'username' => 'maya', 'email' => 'maya@eventhub.test', 'password_hash' => $password, 'role' => 'Marketing Akademik', 'access_permissions' => json_encode(['dashboard', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications']), 'status' => 'Review', 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('users')->ignore(true)->insertBatch($users);

        $userRows = $this->db->table('users')->get()->getResultArray();
        $userByEmail = [];
        foreach ($userRows as $user) {
            $userByEmail[$user['email']] = (int) $user['id'];
        }

        if ($this->db->tableExists('campuses')) {
            $this->db->table('campuses')->ignore(true)->insertBatch([
                ['name' => 'Kampus Jakarta', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Kampus Bandung', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Kampus Surabaya', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Kampus Online', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if ($this->db->tableExists('event_categories')) {
            $this->db->table('event_categories')->ignore(true)->insertBatch([
                ['name' => 'Akademik', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Lomba', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Seminar', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Workshop', 'created_by' => $userByEmail['admin@eventhub.test'], 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        $campusRows = $this->db->tableExists('campuses') ? $this->db->table('campuses')->get()->getResultArray() : [];
        $campusByName = [];
        foreach ($campusRows as $campus) {
            $campusByName[$campus['name']] = (int) $campus['id'];
        }

        $events = [
            [
                'code' => 'EVT-2401',
                'title' => 'Seminar Transformasi Digital Kampus',
                'category' => 'Seminar',
                'campus_id' => $campusByName['Kampus Jakarta'] ?? null,
                'event_date' => '2026-05-12',
                'event_time' => '09:00:00',
                'location' => 'Auditorium Utama',
                'status' => 'disetujui',
                'created_by' => $userByEmail['raka@eventhub.test'],
                'participants_count' => 420,
                'budget' => 28500000,
                'progress' => 78,
                'description' => 'Forum akademik untuk membahas otomasi layanan kampus dan integrasi data.',
                'approved_by' => $userByEmail['admin@eventhub.test'],
                'approved_at' => '2026-05-01 09:00:00',
                'rejected_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EVT-2402',
                'title' => 'Workshop Proposal Riset Mahasiswa',
                'category' => 'Workshop',
                'campus_id' => $campusByName['Kampus Bandung'] ?? null,
                'event_date' => '2026-05-18',
                'event_time' => '13:30:00',
                'location' => 'Lab Komputer B',
                'status' => 'diajukan',
                'created_by' => $userByEmail['maya@eventhub.test'],
                'participants_count' => 85,
                'budget' => 8200000,
                'progress' => 46,
                'description' => 'Pelatihan penyusunan proposal dan rencana anggaran kegiatan.',
                'approved_by' => null,
                'approved_at' => null,
                'rejected_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EVT-2403',
                'title' => 'Kompetisi Inovasi Bisnis Mahasiswa',
                'category' => 'Lomba',
                'campus_id' => $campusByName['Kampus Online'] ?? null,
                'event_date' => '2026-05-23',
                'event_time' => '08:00:00',
                'location' => 'Online via Zoom',
                'status' => 'draft',
                'created_by' => $userByEmail['raka@eventhub.test'],
                'participants_count' => 160,
                'budget' => 16750000,
                'progress' => 24,
                'description' => 'Kompetisi pitching ide bisnis untuk mahasiswa lintas fakultas.',
                'approved_by' => null,
                'approved_at' => null,
                'rejected_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EVT-2404',
                'title' => 'Open House Program Pascasarjana',
                'category' => 'Akademik',
                'campus_id' => $campusByName['Kampus Surabaya'] ?? null,
                'event_date' => '2026-06-02',
                'event_time' => '10:00:00',
                'location' => 'Gedung Pascasarjana',
                'status' => 'selesai',
                'created_by' => $userByEmail['maya@eventhub.test'],
                'participants_count' => 310,
                'budget' => 21000000,
                'progress' => 100,
                'description' => 'Kegiatan promosi akademik dan konsultasi program studi pascasarjana.',
                'approved_by' => $userByEmail['admin@eventhub.test'],
                'approved_at' => '2026-04-26 10:30:00',
                'rejected_reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('events')->ignore(true)->insertBatch($events);

        $eventRows = $this->db->table('events')->get()->getResultArray();
        $eventByCode = [];
        foreach ($eventRows as $event) {
            $eventByCode[$event['code']] = (int) $event['id'];
        }

        $this->db->table('proposals')->ignore(true)->insertBatch([
            ['code' => 'PRP-901', 'event_id' => $eventByCode['EVT-2401'], 'status' => 'siap export', 'content' => 'Proposal otomatis seminar transformasi digital.', 'created_at' => $now, 'updated_at' => '2026-05-01 08:00:00'],
            ['code' => 'PRP-902', 'event_id' => $eventByCode['EVT-2402'], 'status' => 'review admin', 'content' => 'Proposal workshop riset sedang direview.', 'created_at' => $now, 'updated_at' => '2026-05-02 10:00:00'],
            ['code' => 'PRP-903', 'event_id' => $eventByCode['EVT-2403'], 'status' => 'draft', 'content' => 'Draft proposal kompetisi inovasi bisnis.', 'created_at' => $now, 'updated_at' => '2026-05-03 11:00:00'],
        ]);

        $this->db->table('reports')->ignore(true)->insertBatch([
            ['code' => 'RPT-551', 'event_id' => $eventByCode['EVT-2404'], 'realization_status' => 'Lengkap', 'score' => 92, 'notes' => 'Realisasi sesuai rencana, dokumentasi lengkap.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'RPT-552', 'event_id' => $eventByCode['EVT-2401'], 'realization_status' => 'Menunggu pelaksanaan', 'score' => 0, 'notes' => 'Laporan dibuka otomatis setelah event selesai.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'RPT-553', 'event_id' => $eventByCode['EVT-2402'], 'realization_status' => 'Belum tersedia', 'score' => 0, 'notes' => 'Event masih menunggu approval.', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('notifications')->ignore(true)->insertBatch([
            ['user_id' => $userByEmail['admin@eventhub.test'], 'type' => 'approval', 'message' => 'Workshop Proposal Riset Mahasiswa menunggu approval admin.', 'read_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $userByEmail['raka@eventhub.test'], 'type' => 'calendar', 'message' => 'Seminar Transformasi Digital Kampus tersinkron ke Google Calendar.', 'read_at' => '2026-05-02 08:15:00', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $userByEmail['dimas@eventhub.test'], 'type' => 'report', 'message' => 'Laporan Open House Program Pascasarjana siap direview.', 'read_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('calendar_integrations')->ignore(true)->insertBatch([
            ['event_id' => $eventByCode['EVT-2401'], 'provider' => 'google_calendar', 'external_event_id' => 'gcal_evt_2401', 'sync_status' => 'synced', 'last_synced_at' => '2026-05-01 09:30:00', 'created_at' => $now, 'updated_at' => $now],
            ['event_id' => $eventByCode['EVT-2402'], 'provider' => 'google_calendar', 'external_event_id' => null, 'sync_status' => 'pending_approval', 'last_synced_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
