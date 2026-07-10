<?php

namespace App\Models;

use CodeIgniter\Model;

class CrmLeadLogModel extends Model
{
    protected $table = 'crm_lead_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'lead_id',
        'user_id',
        'action',
        'old_stage',
        'new_stage',
        'old_label',
        'new_label',
        'old_agent_id',
        'new_agent_id',
        'old_rejection_reason',
        'new_rejection_reason',
        'summary',
        'created_at',
    ];
}
