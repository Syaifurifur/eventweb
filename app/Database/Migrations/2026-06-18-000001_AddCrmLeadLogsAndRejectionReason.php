<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCrmLeadLogsAndRejectionReason extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('crm_leads') && ! $this->db->fieldExists('rejection_reason', 'crm_leads')) {
            $this->forge->addColumn('crm_leads', [
                'rejection_reason' => ['type' => 'TEXT', 'null' => true, 'after' => 'notes'],
            ]);
        }

        if (! $this->db->tableExists('crm_lead_logs')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'lead_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'action' => ['type' => 'VARCHAR', 'constraint' => 40],
                'old_stage' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'new_stage' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'old_label' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'new_label' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'old_agent_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'new_agent_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'old_rejection_reason' => ['type' => 'TEXT', 'null' => true],
                'new_rejection_reason' => ['type' => 'TEXT', 'null' => true],
                'summary' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('lead_id');
            $this->forge->addKey('user_id');
            $this->forge->addKey('created_at');
            $this->forge->addForeignKey('lead_id', 'crm_leads', 'id', 'CASCADE', 'SET NULL');
            $this->forge->addForeignKey('user_id', 'crm_users', 'id', 'SET NULL', 'SET NULL');
            $this->forge->createTable('crm_lead_logs', true);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('crm_lead_logs')) {
            $this->forge->dropTable('crm_lead_logs', true);
        }

        if ($this->db->tableExists('crm_leads') && $this->db->fieldExists('rejection_reason', 'crm_leads')) {
            $this->forge->dropColumn('crm_leads', 'rejection_reason');
        }
    }
}
