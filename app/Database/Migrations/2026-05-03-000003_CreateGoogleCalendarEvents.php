<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoogleCalendarEvents extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'google_event_id' => ['type' => 'VARCHAR', 'constraint' => 191],
            'summary' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'start_at' => ['type' => 'DATETIME'],
            'end_at' => ['type' => 'DATETIME', 'null' => true],
            'is_all_day' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'html_link' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'confirmed'],
            'raw_payload' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'google_event_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('google_calendar_events', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('google_calendar_events', true);
    }
}
