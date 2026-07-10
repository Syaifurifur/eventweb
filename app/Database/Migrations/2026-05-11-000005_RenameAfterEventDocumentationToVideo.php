<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameAfterEventDocumentationToVideo extends Migration
{
    private string $oldName = 'Link Upload Dokumentasi After Event';
    private string $newName = 'Link Dokumentasi Video';

    public function up(): void
    {
        if (! $this->db->tableExists('event_checklist_templates')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $oldTemplate = $this->db->table('event_checklist_templates')
            ->where('name', $this->oldName)
            ->where('phase', 'Pasca Event')
            ->get()
            ->getRowArray();
        $newTemplate = $this->db->table('event_checklist_templates')
            ->where('name', $this->newName)
            ->where('phase', 'Pasca Event')
            ->get()
            ->getRowArray();

        if ($oldTemplate && $newTemplate) {
            if ($this->db->tableExists('event_checklist_items')) {
                $this->db->table('event_checklist_items')
                    ->where('template_id', $oldTemplate['id'])
                    ->update([
                        'template_id' => $newTemplate['id'],
                        'name' => $this->newName,
                        'updated_at' => $now,
                    ]);
            }

            $this->db->table('event_checklist_templates')->where('id', $oldTemplate['id'])->delete();
        } elseif ($oldTemplate) {
            $this->db->table('event_checklist_templates')
                ->where('id', $oldTemplate['id'])
                ->update([
                    'name' => $this->newName,
                    'updated_at' => $now,
                ]);
        } else {
            $this->db->table('event_checklist_templates')->insert([
                'name' => $this->newName,
                'phase' => 'Pasca Event',
                'sort_order' => 27,
                'is_system' => 0,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($this->db->tableExists('event_checklist_items')) {
            $this->db->table('event_checklist_items')
                ->where('name', $this->oldName)
                ->update([
                    'name' => $this->newName,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('event_checklist_templates')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('event_checklist_templates')
            ->where('name', $this->newName)
            ->where('phase', 'Pasca Event')
            ->update([
                'name' => $this->oldName,
                'updated_at' => $now,
            ]);

        if ($this->db->tableExists('event_checklist_items')) {
            $this->db->table('event_checklist_items')
                ->where('name', $this->newName)
                ->update([
                    'name' => $this->oldName,
                    'updated_at' => $now,
                ]);
        }
    }
}
