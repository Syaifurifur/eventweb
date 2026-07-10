<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportPromotionLinks extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('reports')) {
            return;
        }

        $fields = $this->db->getFieldNames('reports');
        $add = [];

        if (! in_array('promotion_link', $fields, true)) {
            $add['promotion_link'] = ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'notes'];
        }

        if (! in_array('publication_link', $fields, true)) {
            $add['publication_link'] = ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'promotion_link'];
        }

        if ($add !== []) {
            $this->forge->addColumn('reports', $add);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('reports')) {
            return;
        }

        foreach (['publication_link', 'promotion_link'] as $field) {
            if ($this->db->fieldExists($field, 'reports')) {
                $this->forge->dropColumn('reports', $field);
            }
        }
    }
}
