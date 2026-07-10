<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDmerRoleForSpecialAnalytics extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('roles')) {
            return;
        }

        $existing = $this->db->table('roles')->where('name', 'DMER')->get()->getRowArray();
        $payload = [
            'name' => 'DMER',
            'description' => 'Monitoring analisis khusus target event setiap kampus.',
            'is_system' => 0,
            'default_permissions' => json_encode(['dashboard', 'analytics', 'notifications']),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->table('roles')->where('id', $existing['id'])->update($payload);
            return;
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('roles')->insert($payload);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('roles')) {
            return;
        }

        $usedByUsers = $this->db->table('users')->where('role', 'DMER')->countAllResults();
        if ($usedByUsers === 0) {
            $this->db->table('roles')->where('name', 'DMER')->delete();
        }
    }
}
