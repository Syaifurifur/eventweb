<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCampusIdToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users') || $this->db->fieldExists('campus_id', 'users')) {
            return;
        }

        $this->forge->addColumn('users', [
            'campus_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'role',
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('campus_id', 'users')) {
            $this->forge->dropColumn('users', 'campus_id');
        }
    }
}
