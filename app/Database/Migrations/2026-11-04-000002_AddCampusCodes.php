<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCampusCodes extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('kode_ubsi', 'campuses')) {
            $this->forge->addColumn('campuses', [
                'kode_ubsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'name',
                ],
                'campus_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'kode_ubsi',
                ],
            ]);
        }

        if (! $this->db->fieldExists('kode_ubsi', 'one_campus_pmb_students')) {
            $this->forge->addColumn('one_campus_pmb_students', [
                'kode_ubsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'campus_id',
                ],
                'campus_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'kode_ubsi',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('campus_code', 'campuses')) {
            $this->forge->dropColumn('campuses', 'campus_code');
        }
        if ($this->db->fieldExists('kode_ubsi', 'campuses')) {
            $this->forge->dropColumn('campuses', 'kode_ubsi');
        }
        if ($this->db->fieldExists('campus_code', 'one_campus_pmb_students')) {
            $this->forge->dropColumn('one_campus_pmb_students', 'campus_code');
        }
        if ($this->db->fieldExists('kode_ubsi', 'one_campus_pmb_students')) {
            $this->forge->dropColumn('one_campus_pmb_students', 'kode_ubsi');
        }
    }
}

