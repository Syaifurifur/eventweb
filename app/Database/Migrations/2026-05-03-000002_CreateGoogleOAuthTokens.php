<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoogleOAuthTokens extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'provider' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'google_calendar'],
            'access_token' => ['type' => 'TEXT'],
            'refresh_token' => ['type' => 'TEXT', 'null' => true],
            'scope' => ['type' => 'TEXT', 'null' => true],
            'token_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'Bearer'],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'provider']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('google_oauth_tokens', true);

        $fields = $this->db->getFieldNames('calendar_integrations');
        if (! in_array('external_link', $fields, true)) {
            $this->forge->addColumn('calendar_integrations', [
                'external_link' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'external_event_id'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('google_oauth_tokens', true);

        if ($this->db->fieldExists('external_link', 'calendar_integrations')) {
            $this->forge->dropColumn('calendar_integrations', 'external_link');
        }
    }
}
