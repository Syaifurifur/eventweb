<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoles extends Migration
{
    private array $permissionDefaults = [
        'Admin Sistem' => ['dashboard', 'events', 'calendar', 'campuses', 'approvals', 'documents', 'reports', 'analytics', 'notifications', 'users'],
        'Event Creator' => ['dashboard', 'events', 'calendar', 'documents', 'reports', 'notifications'],
        'Pimpinan' => ['dashboard', 'calendar', 'approvals', 'reports', 'analytics', 'notifications'],
        'Markom' => ['dashboard', 'crm', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications'],
        'Marketing Akademik' => ['dashboard', 'events', 'calendar', 'documents', 'reports', 'analytics', 'notifications'],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('roles')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 80],
                'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'is_system' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'default_permissions' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('roles', true);
        }

        if ($this->db->tableExists('users')) {
            $fields = $this->db->getFieldData('users');
            foreach ($fields as $field) {
                if ($field->name === 'role' && (int) $field->max_length < 80) {
                    $this->forge->modifyColumn('users', [
                        'role' => ['name' => 'role', 'type' => 'VARCHAR', 'constraint' => 80],
                    ]);
                    break;
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        $roleNames = array_keys($this->permissionDefaults);

        if ($this->db->tableExists('users')) {
            $existingUserRoles = $this->db->table('users')
                ->select('role')
                ->where('role IS NOT NULL')
                ->groupBy('role')
                ->get()
                ->getResultArray();

            foreach ($existingUserRoles as $row) {
                $role = trim((string) $row['role']);
                if ($role !== '' && ! in_array($role, $roleNames, true)) {
                    $roleNames[] = $role;
                }
            }
        }

        $rows = [];
        foreach ($roleNames as $roleName) {
            $permissions = $this->permissionDefaults[$roleName] ?? ['dashboard'];
            $rows[] = [
                'name' => $roleName,
                'description' => $this->descriptionForRole($roleName),
                'is_system' => $roleName === 'Admin Sistem' ? 1 : 0,
                'default_permissions' => json_encode($permissions),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            $this->db->table('roles')->ignore(true)->insertBatch($rows);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('roles', true);
    }

    private function descriptionForRole(string $roleName): string
    {
        return match ($roleName) {
            'Admin Sistem' => 'Role sistem dengan akses penuh dan tidak dapat dihapus.',
            'Event Creator' => 'Membuat event, dokumen, laporan, dan memantau kalender.',
            'Pimpinan' => 'Review approval, laporan, analytics, dan agenda kalender.',
            'Markom' => 'Mengelola event marketing dan data CRM sesuai kampus.',
            'Marketing Akademik' => 'Mengelola event promosi akademik, dokumen, dan laporan.',
            default => 'Kategori role custom dari data user yang sudah ada.',
        };
    }
}
