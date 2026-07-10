<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAfterEventDocumentationChecklist extends Migration
{
    private array $afterEventTemplates = [
        ['Link Upload Dokumentasi After Event', 'Pasca Event', 25],
        ['Link Dokumentasi Foto', 'Pasca Event', 26],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('event_checklist_templates')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        if ($this->db->tableExists('event_checklist_items')) {
            $this->db->table('event_checklist_items')
                ->where('name', 'Dokumentasi')
                ->where('phase', 'Event')
                ->delete();

            $this->db->table('event_checklist_items')
                ->where('name', 'Upload Dokumentasi After Event')
                ->where('phase', 'Pasca Event')
                ->update(['name' => 'Link Upload Dokumentasi After Event', 'updated_at' => $now]);

            $this->db->table('event_checklist_items')
                ->where('name', 'Dokumentasi Foto')
                ->where('phase', 'Pasca Event')
                ->update(['name' => 'Link Dokumentasi Foto', 'updated_at' => $now]);
        }

        $this->db->table('event_checklist_templates')
            ->where('name', 'Dokumentasi')
            ->where('phase', 'Event')
            ->delete();

        $this->db->table('event_checklist_templates')
            ->where('name', 'Upload Dokumentasi After Event')
            ->where('phase', 'Pasca Event')
            ->update(['name' => 'Link Upload Dokumentasi After Event', 'updated_at' => $now]);

        $this->db->table('event_checklist_templates')
            ->where('name', 'Dokumentasi Foto')
            ->where('phase', 'Pasca Event')
            ->update(['name' => 'Link Dokumentasi Foto', 'updated_at' => $now]);

        foreach ($this->afterEventTemplates as [$name, $phase, $sortOrder]) {
            $template = $this->db->table('event_checklist_templates')
                ->where('name', $name)
                ->where('phase', $phase)
                ->get()
                ->getRowArray();

            if (! $template) {
                $this->db->table('event_checklist_templates')->insert([
                    'name' => $name,
                    'phase' => $phase,
                    'sort_order' => $sortOrder,
                    'is_system' => 0,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $template = $this->db->table('event_checklist_templates')
                    ->where('name', $name)
                    ->where('phase', $phase)
                    ->get()
                    ->getRowArray();
            }

            if ($template) {
                $this->seedExistingEventsForTemplate($template, $now);
            }
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('event_checklist_templates')) {
            return;
        }

        foreach ($this->afterEventTemplates as [$name, $phase]) {
            if ($this->db->tableExists('event_checklist_items')) {
                $this->db->table('event_checklist_items')
                    ->where('name', $name)
                    ->where('phase', $phase)
                    ->delete();
            }

            $this->db->table('event_checklist_templates')
                ->where('name', $name)
                ->where('phase', $phase)
                ->delete();
        }
    }

    private function seedExistingEventsForTemplate(array $template, string $now): void
    {
        if (! $this->db->tableExists('events') || ! $this->db->tableExists('event_checklist_items')) {
            return;
        }

        $events = $this->db->table('events')->get()->getResultArray();

        foreach ($events as $event) {
            $exists = $this->db->table('event_checklist_items')
                ->where('event_id', $event['id'])
                ->where('template_id', $template['id'])
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('event_checklist_items')->insert([
                'event_id' => $event['id'],
                'template_id' => $template['id'],
                'name' => $template['name'],
                'phase' => $template['phase'],
                'pic_user_id' => $event['created_by'] ?? null,
                'is_assigned' => 0,
                'assigned_at' => null,
                'deadline' => null,
                'status' => 'pending',
                'notes' => null,
                'drive_link' => null,
                'submitted_by' => null,
                'submitted_at' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
