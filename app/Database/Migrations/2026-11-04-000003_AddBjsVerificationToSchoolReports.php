<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBjsVerificationToSchoolReports extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('bjs_status', 'one_campus_school_reports')) {
            $this->forge->addColumn('one_campus_school_reports', [
                'bjs_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'default' => 'Belum Aktif',
                    'after' => 'status',
                ],
                'bjs_verified_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'bjs_status',
                ],
                'bjs_verified_by' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'bjs_verified_at',
                ],
                'bjs_notes' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'bjs_verified_by',
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = ['bjs_notes', 'bjs_verified_by', 'bjs_verified_at', 'bjs_status'];
        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'one_campus_school_reports')) {
                $this->forge->dropColumn('one_campus_school_reports', $field);
            }
        }
    }
}

