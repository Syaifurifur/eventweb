<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventChecklists extends Migration
{
    private array $templates = [
        ['Proposal', 'Pra Event'],
        ['TOR', 'Pra Event'],
        ['Flyer', 'Pra Event'],
        ['Spanduk', 'Pra Event'],
        ['Backdrop', 'Pra Event'],
        ['Rundown', 'Pra Event'],
        ['Figura', 'Pra Event'],
        ['Souvenir', 'Pra Event'],
        ['Teaser', 'Pra Event'],
        ['Cue Card', 'Pra Event'],
        ['Naskah MC', 'Pra Event'],
        ['Nampan', 'Pra Event'],
        ['2 Earpeace Markom', 'Pra Event'],
        ['2 Camera Markom', 'Pra Event'],
        ['Batre by Kampus', 'Pra Event'],
        ['Sertifikat Penghargaan', 'Pra Event'],
        ['Desain Sertifikat Peserta', 'Pra Event'],
        ['Briefing MC', 'Pra Event'],
        ['Briefing Panitia', 'Pra Event'],
        ['Setting Aula', 'Pra Event'],
        ['Materi Narasumber', 'Pra Event'],
        ['Release Pra Event', 'Pra Event'],
        ['Konsumsi', 'Event'],
        ['Link Dokumentasi Foto', 'Pasca Event'],
        ['Link Dokumentasi Video', 'Pasca Event'],
        ['Laporan', 'Pasca Event'],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('event_checklist_templates')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 160],
                'phase' => ['type' => 'VARCHAR', 'constraint' => 50],
                'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'is_system' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['name', 'phase']);
            $this->forge->createTable('event_checklist_templates', true);
        }

        if (! $this->db->tableExists('event_checklist_items')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'event_id' => ['type' => 'INT', 'unsigned' => true],
                'template_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 160],
                'phase' => ['type' => 'VARCHAR', 'constraint' => 50],
                'pic_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'is_assigned' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'assigned_at' => ['type' => 'DATETIME', 'null' => true],
                'deadline' => ['type' => 'DATE', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'drive_link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'submitted_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'submitted_at' => ['type' => 'DATETIME', 'null' => true],
                'completed_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['event_id', 'template_id']);
            $this->forge->addForeignKey('event_id', 'events', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('template_id', 'event_checklist_templates', 'id', 'SET NULL', 'SET NULL');
            $this->forge->addForeignKey('pic_user_id', 'users', 'id', 'SET NULL', 'SET NULL');
            $this->forge->addForeignKey('submitted_by', 'users', 'id', 'SET NULL', 'SET NULL');
            $this->forge->createTable('event_checklist_items', true);
        }

        $this->seedTemplates();
        $this->seedExistingEvents();
    }

    public function down(): void
    {
        $this->forge->dropTable('event_checklist_items', true);
        $this->forge->dropTable('event_checklist_templates', true);
    }

    private function seedTemplates(): void
    {
        $now = date('Y-m-d H:i:s');
        $builder = $this->db->table('event_checklist_templates');

        foreach ($this->templates as $index => [$name, $phase]) {
            $exists = $builder
                ->where('name', $name)
                ->where('phase', $phase)
                ->countAllResults(false);
            $builder->resetQuery();

            if ($exists > 0) {
                continue;
            }

            $builder->insert([
                'name' => $name,
                'phase' => $phase,
                'sort_order' => $index + 1,
                'is_system' => in_array($name, ['Proposal', 'TOR'], true) ? 1 : 0,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedExistingEvents(): void
    {
        if (! $this->db->tableExists('events')) {
            return;
        }

        $templates = $this->db->table('event_checklist_templates')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
        $events = $this->db->table('events')->get()->getResultArray();
        $now = date('Y-m-d H:i:s');

        foreach ($events as $event) {
            foreach ($templates as $template) {
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
                    'completed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
