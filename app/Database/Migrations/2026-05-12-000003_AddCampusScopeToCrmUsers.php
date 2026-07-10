<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCampusScopeToCrmUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('crm_users')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('campus_id', 'crm_users')) {
            $fields['campus_id'] = [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'status',
            ];
        }

        if (! $this->db->fieldExists('registered_by', 'crm_users')) {
            $fields['registered_by'] = [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'campus_id',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('crm_users', $fields);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('crm_users')) {
            return;
        }

        foreach (['registered_by', 'campus_id'] as $field) {
            if ($this->db->fieldExists($field, 'crm_users')) {
                $this->forge->dropColumn('crm_users', $field);
            }
        }
    }
}
