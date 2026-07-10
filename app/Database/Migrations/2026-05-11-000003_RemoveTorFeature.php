<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTorFeature extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('event_checklist_items')) {
            $this->db->table('event_checklist_items')->where('name', 'TOR')->delete();
        }

        if ($this->db->tableExists('event_checklist_templates')) {
            $this->db->table('event_checklist_templates')->where('name', 'TOR')->delete();
        }

        if ($this->db->tableExists('tors')) {
            $this->forge->dropTable('tors', true);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('tors')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'code' => ['type' => 'VARCHAR', 'constraint' => 30],
                'event_id' => ['type' => 'INT', 'unsigned' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 50],
                'content' => ['type' => 'MEDIUMTEXT', 'null' => true],
                'document_link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'template_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
                'template_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'generated_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'generated_at' => ['type' => 'DATETIME', 'null' => true],
                'ai_model' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'ai_notes' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code');
            $this->forge->addForeignKey('event_id', 'events', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('tors', true);
        }

        if ($this->db->tableExists('event_checklist_templates')) {
            $this->db->table('event_checklist_templates')->ignore(true)->insert([
                'name' => 'TOR',
                'phase' => 'Pra Event',
                'sort_order' => 20,
                'is_system' => 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
