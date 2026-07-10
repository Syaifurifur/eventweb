<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCrmSchema extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => 80],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Agent'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Active'],
            'last_login_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('crm_users', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'batch_code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'file_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'total_rows' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'uploaded_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'uploaded_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('batch_code');
        $this->forge->addForeignKey('uploaded_by', 'crm_users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('crm_upload_batches', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'batch_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'student_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'school_origin' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'whatsapp' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'stage' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Tahap 1'],
            'label' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'agent_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'responded_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('batch_id');
        $this->forge->addKey('agent_id');
        $this->forge->addForeignKey('batch_id', 'crm_upload_batches', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('agent_id', 'crm_users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('crm_leads', true);

        $this->seedInitialCrmData();
    }

    public function down(): void
    {
        foreach (['crm_leads', 'crm_upload_batches', 'crm_users'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }

    private function seedInitialCrmData(): void
    {
        $now = date('Y-m-d H:i:s');
        $password = password_hash('password', PASSWORD_DEFAULT);

        $this->db->table('crm_users')->ignore(true)->insertBatch([
            [
                'username' => 'crmadmin',
                'password_hash' => $password,
                'full_name' => 'Administrator',
                'role' => 'Admin',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username' => 'iqbal',
                'password_hash' => $password,
                'full_name' => 'Iqbal',
                'role' => 'Agent',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username' => 'bagas',
                'password_hash' => $password,
                'full_name' => 'Bagas',
                'role' => 'Agent',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username' => 'fifi',
                'password_hash' => $password,
                'full_name' => 'Fifi',
                'role' => 'Agent',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $users = $this->db->table('crm_users')->get()->getResultArray();
        $userByUsername = [];
        foreach ($users as $user) {
            $userByUsername[$user['username']] = (int) $user['id'];
        }

        $batchCode = 'BATCH_' . date('YmdHis') . '_173';
        $this->db->table('crm_upload_batches')->ignore(true)->insert([
            'batch_code' => $batchCode,
            'file_name' => 'sample-calon-pendaftar.xlsx',
            'total_rows' => 6,
            'uploaded_by' => $userByUsername['crmadmin'] ?? null,
            'uploaded_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $batch = $this->db->table('crm_upload_batches')->where('batch_code', $batchCode)->get()->getRowArray();
        $batchId = (int) ($batch['id'] ?? 0);

        $this->db->table('crm_leads')->insertBatch([
            ['batch_id' => $batchId, 'student_name' => 'Ikfina Nur Aini', 'school_origin' => 'SMA N 2 Purbalingga', 'whatsapp' => '6285641752730', 'stage' => 'Tahap 1', 'label' => null, 'agent_id' => $userByUsername['iqbal'] ?? null, 'created_at' => $now, 'updated_at' => $now],
            ['batch_id' => $batchId, 'student_name' => 'Gilang Ramadhan', 'school_origin' => "SMK Ma'arif NU Bobotsari", 'whatsapp' => '6283182346762', 'stage' => 'Tahap 1', 'label' => null, 'agent_id' => $userByUsername['iqbal'] ?? null, 'created_at' => $now, 'updated_at' => $now],
            ['batch_id' => $batchId, 'student_name' => 'Sania Zulfa Indana R', 'school_origin' => "SMK Ma'arif NU Paguyangan", 'whatsapp' => '6285325775841', 'stage' => 'Tahap 3', 'label' => 'Minat/Tertarik', 'agent_id' => $userByUsername['bagas'] ?? null, 'created_at' => $now, 'updated_at' => $now],
            ['batch_id' => $batchId, 'student_name' => 'Ghazan Nova', 'school_origin' => 'SMA N 1 Paguyangan', 'whatsapp' => '6285641513063', 'stage' => 'Tahap 1', 'label' => 'Menolak', 'agent_id' => $userByUsername['fifi'] ?? null, 'created_at' => $now, 'updated_at' => $now],
            ['batch_id' => $batchId, 'student_name' => 'Dawwas', 'school_origin' => 'SMA N 1 Banyumas', 'whatsapp' => '6285641570776', 'stage' => 'Tahap 1', 'label' => null, 'agent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['batch_id' => $batchId, 'student_name' => 'Rania Putri', 'school_origin' => 'SMK N 1 Purwokerto', 'whatsapp' => '6281222030101', 'stage' => 'Tahap 2', 'label' => 'Ragu-ragu', 'agent_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
