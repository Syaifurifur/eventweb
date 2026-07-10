<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DedupeCalendarIntegrations extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('calendar_integrations')) {
            return;
        }

        $rows = $this->db->table('calendar_integrations')
            ->orderBy('event_id', 'ASC')
            ->orderBy('provider', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $keepByGroup = [];
        $deleteIds = [];

        foreach ($rows as $row) {
            $key = (string) $row['event_id'] . '|' . (string) $row['provider'];

            if (! isset($keepByGroup[$key])) {
                $keepByGroup[$key] = $row;
                continue;
            }

            if ($this->integrationScore($row) > $this->integrationScore($keepByGroup[$key])) {
                $deleteIds[] = (int) $keepByGroup[$key]['id'];
                $keepByGroup[$key] = $row;
                continue;
            }

            $deleteIds[] = (int) $row['id'];
        }

        foreach (array_chunk(array_values(array_unique($deleteIds)), 100) as $ids) {
            if ($ids !== []) {
                $this->db->table('calendar_integrations')->whereIn('id', $ids)->delete();
            }
        }

        $this->forge->addUniqueKey(['event_id', 'provider'], 'calendar_integrations_event_provider_unique');
        $this->forge->processIndexes('calendar_integrations');
    }

    public function down(): void
    {
        if ($this->db->tableExists('calendar_integrations')) {
            $this->forge->dropKey('calendar_integrations', 'calendar_integrations_event_provider_unique');
        }
    }

    private function integrationScore(array $row): int
    {
        $score = 0;

        if (($row['sync_status'] ?? '') === 'synced') {
            $score += 10_000_000_000;
        }

        if (trim((string) ($row['external_event_id'] ?? '')) !== '') {
            $score += 5_000_000_000;
        }

        $lastSyncedAt = strtotime((string) ($row['last_synced_at'] ?? ''));
        if ($lastSyncedAt !== false) {
            $score += $lastSyncedAt;
        }

        return $score;
    }
}
