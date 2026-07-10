<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampuses extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('campuses')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 160],
                'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('campuses', true);
        }

        if ($this->db->tableExists('events') && ! $this->db->fieldExists('campus_id', 'events')) {
            $this->forge->addColumn('events', [
                'campus_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'category'],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('events') && $this->db->fieldExists('campus_id', 'events')) {
            $this->forge->dropColumn('events', 'campus_id');
        }

        $this->forge->dropTable('campuses', true);
    }
}
