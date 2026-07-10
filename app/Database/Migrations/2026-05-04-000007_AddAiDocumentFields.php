<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAiDocumentFields extends Migration
{
    public function up(): void
    {
        foreach (['proposals', 'tors'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            $fields = $this->db->getFieldNames($table);
            $add = [];

            if (! in_array('template_name', $fields, true)) {
                $add['template_name'] = ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true, 'after' => 'content'];
            }
            if (! in_array('template_path', $fields, true)) {
                $add['template_path'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'template_name'];
            }
            if (! in_array('generated_path', $fields, true)) {
                $add['generated_path'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'template_path'];
            }
            if (! in_array('generated_at', $fields, true)) {
                $add['generated_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'generated_path'];
            }
            if (! in_array('ai_model', $fields, true)) {
                $add['ai_model'] = ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'generated_at'];
            }
            if (! in_array('ai_notes', $fields, true)) {
                $add['ai_notes'] = ['type' => 'TEXT', 'null' => true, 'after' => 'ai_model'];
            }

            if ($add !== []) {
                $this->forge->addColumn($table, $add);
            }
        }
    }

    public function down(): void
    {
        foreach (['proposals', 'tors'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            foreach (['ai_notes', 'ai_model', 'generated_at', 'generated_path', 'template_path', 'template_name'] as $field) {
                if ($this->db->fieldExists($field, $table)) {
                    $this->forge->dropColumn($table, $field);
                }
            }
        }
    }
}
