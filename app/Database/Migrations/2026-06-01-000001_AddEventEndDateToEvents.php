<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEventEndDateToEvents extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('event_end_date', 'events')) {
            $this->forge->addColumn('events', [
                'event_end_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'event_date',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('event_end_date', 'events')) {
            $this->forge->dropColumn('events', 'event_end_date');
        }
    }
}
