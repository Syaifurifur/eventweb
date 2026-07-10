<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameAfterEventDocumentationChecklistLinks extends Migration
{
    private array $renames = [
        'Upload Dokumentasi After Event' => 'Link Upload Dokumentasi After Event',
        'Dokumentasi Foto' => 'Link Dokumentasi Foto',
    ];

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->renames as $oldName => $newName) {
            if ($this->db->tableExists('event_checklist_templates')) {
                $this->db->table('event_checklist_templates')
                    ->where('name', $oldName)
                    ->where('phase', 'Pasca Event')
                    ->update(['name' => $newName, 'updated_at' => $now]);
            }

            if ($this->db->tableExists('event_checklist_items')) {
                $this->db->table('event_checklist_items')
                    ->where('name', $oldName)
                    ->where('phase', 'Pasca Event')
                    ->update(['name' => $newName, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->renames as $oldName => $newName) {
            if ($this->db->tableExists('event_checklist_templates')) {
                $this->db->table('event_checklist_templates')
                    ->where('name', $newName)
                    ->where('phase', 'Pasca Event')
                    ->update(['name' => $oldName, 'updated_at' => $now]);
            }

            if ($this->db->tableExists('event_checklist_items')) {
                $this->db->table('event_checklist_items')
                    ->where('name', $newName)
                    ->where('phase', 'Pasca Event')
                    ->update(['name' => $oldName, 'updated_at' => $now]);
            }
        }
    }
}
