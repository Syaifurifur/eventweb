<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChecklistReportingFields extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('event_checklist_items')) {
            return;
        }

        $fields = $this->db->getFieldNames('event_checklist_items');
        $add = [];

        if (! in_array('drive_link', $fields, true)) {
            $add['drive_link'] = ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'notes'];
        }
        if (! in_array('submitted_by', $fields, true)) {
            $add['submitted_by'] = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'drive_link'];
        }
        if (! in_array('submitted_at', $fields, true)) {
            $add['submitted_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'submitted_by'];
        }

        if ($add !== []) {
            $this->forge->addColumn('event_checklist_items', $add);
        }

        if ($this->db->fieldExists('submitted_by', 'event_checklist_items')) {
            $this->forge->addForeignKey('submitted_by', 'users', 'id', 'SET NULL', 'SET NULL');
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('event_checklist_items')) {
            return;
        }

        foreach (['submitted_at', 'submitted_by', 'drive_link'] as $field) {
            if ($this->db->fieldExists($field, 'event_checklist_items')) {
                $this->forge->dropColumn('event_checklist_items', $field);
            }
        }
    }
}
