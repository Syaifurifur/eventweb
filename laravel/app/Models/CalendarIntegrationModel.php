<?php

namespace App\Models;

use CodeIgniter\Model;

class CalendarIntegrationModel extends Model
{
    protected $table = 'calendar_integrations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['event_id', 'provider', 'external_event_id', 'external_link', 'sync_status', 'last_synced_at'];
    protected $useTimestamps = true;
}
