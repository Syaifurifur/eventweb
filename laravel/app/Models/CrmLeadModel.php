<?php

namespace App\Models;

use CodeIgniter\Model;

class CrmLeadModel extends Model
{
    protected $table = 'crm_leads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'batch_id',
        'event_id',
        'student_name',
        'school_origin',
        'whatsapp',
        'stage',
        'prospect_status',
        'label',
        'agent_id',
        'notes',
        'rejection_reason',
        'responded_at',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'event_id' => 'permit_empty|is_natural_no_zero',
        'student_name' => 'required|min_length[2]|max_length[160]',
        'school_origin' => 'permit_empty|max_length[180]',
        'whatsapp' => 'permit_empty|max_length[40]',
        'stage' => 'required|in_list[Tahap 1,Tahap 2,Tahap 3]',
        'label' => 'permit_empty|in_list[Closing,Minat/Tertarik,Ragu-ragu,Menolak,Tidak Merespon]',
        'agent_id' => 'permit_empty|is_natural_no_zero',
    ];
}
