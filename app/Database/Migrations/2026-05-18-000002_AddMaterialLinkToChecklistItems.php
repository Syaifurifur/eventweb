<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaterialLinkToChecklistItems extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('event_checklist_items') || $this->db->fieldExists('material_drive_link', 'event_checklist_items')) {
            return;
        }

        $this->forge->addColumn('event_checklist_items', [
            'material_drive_link' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'notes',
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->tableExists('event_checklist_items') && $this->db->fieldExists('material_drive_link', 'event_checklist_items')) {
            $this->forge->dropColumn('event_checklist_items', 'material_drive_link');
        }
    }
}
