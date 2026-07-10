<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultCrmAdminAccount extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $payload = [
            'username' => 'admin',
            'password_hash' => password_hash('12345', PASSWORD_DEFAULT),
            'full_name' => 'Admin CRM',
            'role' => 'Admin',
            'status' => 'Active',
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        $existing = $this->db->table('crm_users')->where('username', 'admin')->get()->getRowArray();
        if ($existing) {
            $this->db->table('crm_users')->where('id', $existing['id'])->update($payload);

            return;
        }

        $payload['created_at'] = $now;
        $this->db->table('crm_users')->insert($payload);
    }

    public function down(): void
    {
        $this->db->table('crm_users')->where('username', 'admin')->delete();
    }
}
