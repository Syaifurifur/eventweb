<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOneCampusReports extends Migration
{
    public function up(): void
    {
        $this->createSchoolReports();
        $this->createBannerReports();
        $this->createOjtReports();
        $this->grantOneCampusAccess();
    }

    public function down(): void
    {
        $this->forge->dropTable('one_campus_ojt_reports', true);
        $this->forge->dropTable('one_campus_banner_reports', true);
        $this->forge->dropTable('one_campus_school_reports', true);
    }

    private function createSchoolReports(): void
    {
        if ($this->db->tableExists('one_campus_school_reports')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'campus_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'school_name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'teacher_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40],
            'position' => ['type' => 'VARCHAR', 'constraint' => 120],
            'pmb_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('campus_id', 'campuses', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('one_campus_school_reports', true);
    }

    private function createBannerReports(): void
    {
        if ($this->db->tableExists('one_campus_banner_reports')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'campus_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 220],
            'installed_at' => ['type' => 'DATETIME'],
            'theme' => ['type' => 'VARCHAR', 'constraint' => 180],
            'drive_photo_link' => ['type' => 'VARCHAR', 'constraint' => 500],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('campus_id', 'campuses', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('one_campus_banner_reports', true);
    }

    private function createOjtReports(): void
    {
        if ($this->db->tableExists('one_campus_ojt_reports')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'campus_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'student_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'origin_school' => ['type' => 'VARCHAR', 'constraint' => 180],
            'major' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'ojt_period' => ['type' => 'VARCHAR', 'constraint' => 120],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('campus_id', 'campuses', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('one_campus_ojt_reports', true);
    }

    private function grantOneCampusAccess(): void
    {
        foreach (['roles', 'users'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            $field = $table === 'roles' ? 'default_permissions' : 'access_permissions';
            $rows = $this->db->table($table)->get()->getResultArray();
            foreach ($rows as $row) {
                $permissions = json_decode((string) ($row[$field] ?? '[]'), true);
                $permissions = is_array($permissions) ? $permissions : [];

                $roleName = $table === 'roles' ? ($row['name'] ?? '') : ($row['role'] ?? '');

                if ($roleName === 'Admin Sistem' || $roleName === 'Kepala Kampus') {
                    if (! in_array('one-campus', $permissions, true)) {
                        $permissions[] = 'one-campus';
                    }

                    $this->db->table($table)
                        ->where('id', $row['id'])
                        ->update([
                            $field => json_encode(array_values(array_unique($permissions))),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }
        }
    }
}
