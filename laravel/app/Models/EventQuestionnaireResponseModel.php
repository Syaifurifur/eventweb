<?php

namespace App\Models;

use CodeIgniter\Model;

class EventQuestionnaireResponseModel extends Model
{
    protected $table = 'event_questionnaire_responses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'questionnaire_id',
        'event_id',
        'crm_lead_id',
        'prospect_status',
        'student_name',
        'school_origin',
        'whatsapp',
        'answers_json',
        'submitted_at',
    ];
    protected $useTimestamps = false;
}

