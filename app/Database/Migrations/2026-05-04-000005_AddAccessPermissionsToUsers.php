<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccessPermissionsToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('access_permissions', 'users')) {
            $this->forge->addColumn('users', [
                'access_permissions' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'role',
                ],
            ]);
        }

        $users = $this->db->table('users')->select('id, role, access_permissions')->get()->getResultArray();
        foreach ($users as $user) {
            if (! empty($user['access_permissions'])) {
                continue;
            }

            $this->db->table('users')->where('id', $user['id'])->update([
                'access_permissions' => json_encode($this->defaultPermissions((string) $user['role'])),
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('access_permissions', 'users')) {
            $this->forge->dropColumn('users', 'access_permissions');
        }
    }

    private function defaultPermissions(string $role): array
    {
        return match ($role) {
            'Admin Sistem' => ['dashboard', 'events', 'calendar', 'campuses', 'approvals', 'documents', 'reports', 'analytics', 'notifications', 'users'],
            'Pimpinan' => ['dashboard', 'calendar', 'approvals', 'reports', 'analytics', 'notifications'],
            'Marketing Akademik' => ['dashboard', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications'],
            default => ['dashboard', 'events', 'calendar', 'documents', 'reports', 'notifications'],
        };
    }
}
