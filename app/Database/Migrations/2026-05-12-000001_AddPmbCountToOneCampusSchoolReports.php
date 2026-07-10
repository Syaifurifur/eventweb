<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPmbCountToOneCampusSchoolReports extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('one_campus_school_reports') || $this->db->fieldExists('pmb_count', 'one_campus_school_reports')) {
            return;
        }

        $this->forge->addColumn('one_campus_school_reports', [
            'pmb_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'position',
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->tableExists('one_campus_school_reports') && $this->db->fieldExists('pmb_count', 'one_campus_school_reports')) {
            $this->forge->dropColumn('one_campus_school_reports', 'pmb_count');
        }
    }
}
