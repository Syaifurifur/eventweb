<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportDocumentLink extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('reports')) {
            return;
        }

        if (! $this->db->fieldExists('document_link', 'reports')) {
            $this->forge->addColumn('reports', [
                'document_link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'notes'],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('reports') && $this->db->fieldExists('document_link', 'reports')) {
            $this->forge->dropColumn('reports', 'document_link');
        }
    }
}
