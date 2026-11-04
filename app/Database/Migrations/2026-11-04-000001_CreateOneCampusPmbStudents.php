<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOneCampusPmbStudents extends Migration
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
            'school_report_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'school_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'npsn' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'pmb_period' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'default' => 'September 2026 - September 2027',
            ],
            'student_name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'registration_no' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true,
            ],
            'student_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'student_email' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
                'null' => true,
            ],
            'study_program' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'campus_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Mahasiswa Baru',
            ],
            'batch_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
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
        $this->forge->addKey('school_report_id');
        $this->forge->addKey('pmb_period');
        $this->forge->addKey('campus_id');
        $this->forge->createTable('one_campus_pmb_students', true);
    }

    public function down()
    {
        $this->forge->dropTable('one_campus_pmb_students', true);
    }
}

