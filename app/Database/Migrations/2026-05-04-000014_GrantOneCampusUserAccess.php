<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GrantOneCampusUserAccess extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $users = $this->db->table('users')
            ->whereIn('role', ['Admin Sistem', 'Kepala Kampus'])
            ->get()
            ->getResultArray();

        foreach ($users as $user) {
            $permissions = json_decode((string) ($user['access_permissions'] ?? '[]'), true);
            $permissions = is_array($permissions) ? $permissions : [];

            if (! in_array('one-campus', $permissions, true)) {
                $permissions[] = 'one-campus';
            }

            $this->db->table('users')
                ->where('id', $user['id'])
                ->update([
                    'access_permissions' => json_encode(array_values(array_unique($permissions))),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $users = $this->db->table('users')->get()->getResultArray();
        foreach ($users as $user) {
            $permissions = json_decode((string) ($user['access_permissions'] ?? '[]'), true);
            $permissions = is_array($permissions) ? array_values(array_diff($permissions, ['one-campus'])) : [];

            $this->db->table('users')
                ->where('id', $user['id'])
                ->update([
                    'access_permissions' => json_encode($permissions),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }
}
