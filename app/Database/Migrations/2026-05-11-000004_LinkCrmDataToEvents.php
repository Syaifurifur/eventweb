<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LinkCrmDataToEvents extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('crm_upload_batches') && ! $this->db->fieldExists('event_id', 'crm_upload_batches')) {
            $this->forge->addColumn('crm_upload_batches', [
                'event_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'file_name'],
            ]);
        }

        if ($this->db->tableExists('crm_leads') && ! $this->db->fieldExists('event_id', 'crm_leads')) {
            $this->forge->addColumn('crm_leads', [
                'event_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'batch_id'],
            ]);
        }

        $this->linkExistingSampleData();
    }

    public function down(): void
    {
        if ($this->db->tableExists('crm_leads') && $this->db->fieldExists('event_id', 'crm_leads')) {
            $this->forge->dropColumn('crm_leads', 'event_id');
        }

        if ($this->db->tableExists('crm_upload_batches') && $this->db->fieldExists('event_id', 'crm_upload_batches')) {
            $this->forge->dropColumn('crm_upload_batches', 'event_id');
        }
    }

    private function linkExistingSampleData(): void
    {
        if (! $this->db->tableExists('events') || ! $this->db->tableExists('crm_leads')) {
            return;
        }

        $event = $this->db->table('events')->orderBy('event_date', 'ASC')->get(1)->getRowArray();
        if (! $event) {
            return;
        }

        $eventId = (int) $event['id'];

        if ($this->db->tableExists('crm_upload_batches')) {
            $this->db->table('crm_upload_batches')
                ->where('event_id', null)
                ->update(['event_id' => $eventId]);
        }

        $this->db->table('crm_leads')
            ->where('event_id', null)
            ->update(['event_id' => $eventId]);
    }
}
