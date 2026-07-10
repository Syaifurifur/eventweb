<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarkomCampusScopedRole extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('roles')) {
            return;
        }

        $existing = $this->db->table('roles')
            ->where('name', 'Markom')
            ->get()
            ->getRowArray();

        if ($existing) {
            if (! empty($existing['deleted_at'])) {
                $this->db->table('roles')->where('id', $existing['id'])->update([
                    'description' => 'Mengelola event marketing dan data CRM sesuai kampus.',
                    'default_permissions' => json_encode(['dashboard', 'crm', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications']),
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('roles')->insert([
            'name' => 'Markom',
            'description' => 'Mengelola event marketing dan data CRM sesuai kampus.',
            'is_system' => 0,
            'default_permissions' => json_encode(['dashboard', 'crm', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('roles')) {
            return;
        }

        $usedByUsers = 0;
        if ($this->db->tableExists('users')) {
            $usedByUsers = $this->db->table('users')->where('role', 'Markom')->countAllResults();
        }

        if ($usedByUsers === 0) {
            $this->db->table('roles')->where('name', 'Markom')->delete();
        }
    }
}
