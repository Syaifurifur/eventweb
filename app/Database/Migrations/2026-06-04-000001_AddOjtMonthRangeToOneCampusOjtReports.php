<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOjtMonthRangeToOneCampusOjtReports extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('one_campus_ojt_reports')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('ojt_start_month', 'one_campus_ojt_reports')) {
            $fields['ojt_start_month'] = ['type' => 'DATE', 'null' => true, 'after' => 'ojt_period'];
        }

        if (! $this->db->fieldExists('ojt_end_month', 'one_campus_ojt_reports')) {
            $fields['ojt_end_month'] = ['type' => 'DATE', 'null' => true, 'after' => 'ojt_start_month'];
        }

        if ($fields !== []) {
            $this->forge->addColumn('one_campus_ojt_reports', $fields);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('one_campus_ojt_reports')) {
            return;
        }

        if ($this->db->fieldExists('ojt_end_month', 'one_campus_ojt_reports')) {
            $this->forge->dropColumn('one_campus_ojt_reports', 'ojt_end_month');
        }

        if ($this->db->fieldExists('ojt_start_month', 'one_campus_ojt_reports')) {
            $this->forge->dropColumn('one_campus_ojt_reports', 'ojt_start_month');
        }
    }
}
