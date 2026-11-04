<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccreditationDocuments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'campus_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'event_year' => [
                'type' => 'INT',
                'constraint' => 4,
            ],
            'accreditation_standard' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'Kriteria 9 - Luaran & Capaian Tridharma',
            ],
            'doc_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'all',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'menunggu_kelengkapan',
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'campus_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'additional_evidence_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'additional_evidence_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'external_evidence_link' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'designated_by' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'designated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_by' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'verified_by' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('campus_id');
        $this->forge->addKey('event_year');
        $this->forge->addKey('status');
        $this->forge->addKey('accreditation_standard');

        $this->forge->createTable('accreditation_documents', true);
    }

    public function down()
    {
        $this->forge->dropTable('accreditation_documents', true);
    }
}

