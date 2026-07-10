<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChecklistAssignmentFields extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('event_checklist_items')) {
            return;
        }

        $fields = $this->db->getFieldNames('event_checklist_items');
        $add = [];

        if (! in_array('is_assigned', $fields, true)) {
            $add['is_assigned'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'pic_user_id'];
        }
        if (! in_array('assigned_at', $fields, true)) {
            $add['assigned_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'is_assigned'];
        }

        if ($add !== []) {
            $this->forge->addColumn('event_checklist_items', $add);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('event_checklist_items')) {
            return;
        }

        foreach (['assigned_at', 'is_assigned'] as $field) {
            if ($this->db->fieldExists($field, 'event_checklist_items')) {
                $this->forge->dropColumn('event_checklist_items', $field);
            }
        }
    }
}
