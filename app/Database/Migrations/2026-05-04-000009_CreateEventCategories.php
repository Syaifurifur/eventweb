<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventCategories extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('event_categories')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 80],
                'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('event_categories', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('event_categories', true);
    }
}
