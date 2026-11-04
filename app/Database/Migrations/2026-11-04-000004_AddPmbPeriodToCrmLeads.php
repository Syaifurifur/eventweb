<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPmbPeriodToCrmLeads extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('crm_leads') && ! $this->db->fieldExists('pmb_period', 'crm_leads')) {
            $this->forge->addColumn('crm_leads', [
                'pmb_period' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'default'    => 'September 2025 - September 2026',
                    'null'       => false,
                    'after'      => 'stage',
                ],
            ]);

            $this->db->query("ALTER TABLE crm_leads ADD INDEX idx_crm_leads_pmb (pmb_period)");
            $this->db->query("UPDATE crm_leads SET pmb_period = 'September 2025 - September 2026' WHERE pmb_period IS NULL OR pmb_period = ''");
        }

        if ($this->db->tableExists('crm_upload_batches') && ! $this->db->fieldExists('pmb_period', 'crm_upload_batches')) {
            $this->forge->addColumn('crm_upload_batches', [
                'pmb_period' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'default'    => 'September 2025 - September 2026',
                    'null'       => false,
                    'after'      => 'total_rows',
                ],
            ]);

            $this->db->query("UPDATE crm_upload_batches SET pmb_period = 'September 2025 - September 2026' WHERE pmb_period IS NULL OR pmb_period = ''");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('crm_leads') && $this->db->fieldExists('pmb_period', 'crm_leads')) {
            $this->forge->dropColumn('crm_leads', 'pmb_period');
        }

        if ($this->db->tableExists('crm_upload_batches') && $this->db->fieldExists('pmb_period', 'crm_upload_batches')) {
            $this->forge->dropColumn('crm_upload_batches', 'pmb_period');
        }
    }
}

