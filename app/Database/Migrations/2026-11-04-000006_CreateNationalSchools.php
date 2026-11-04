<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNationalSchools extends Migration
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
            'npsn' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'school_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'SMA',
            ],
            'education_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'school_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'NEGERI',
            ],
            'stage' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'district' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'subdistrict' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'track' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'supervising_ministry' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
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
        $this->forge->addKey('npsn');
        $this->forge->addKey('city');
        $this->forge->createTable('national_schools', true);
    }

    public function down()
    {
        $this->forge->dropTable('national_schools', true);
    }
}

