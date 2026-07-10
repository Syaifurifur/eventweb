<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentLinksAndRemoveProposalSeminarChecklist extends Migration
{
    public function up(): void
    {
        foreach (['proposals', 'tors'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            if (! $this->db->fieldExists('document_link', $table)) {
                $this->forge->addColumn($table, [
                    'document_link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'content'],
                ]);
            }
        }

        if ($this->db->tableExists('event_checklist_items')) {
            $this->db->table('event_checklist_items')
                ->where('name', 'Proposal Seminar')
                ->delete();
        }

        if ($this->db->tableExists('event_checklist_templates')) {
            $this->db->table('event_checklist_templates')
                ->where('name', 'Proposal Seminar')
                ->delete();
        }
    }

    public function down(): void
    {
        foreach (['proposals', 'tors'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            if ($this->db->fieldExists('document_link', $table)) {
                $this->forge->dropColumn($table, 'document_link');
            }
        }
    }
}
