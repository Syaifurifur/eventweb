<?php

namespace App\Models;

use CodeIgniter\Model;

class EventQuestionnaireModel extends Model
{
    protected $table = 'event_questionnaires';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'event_id',
        'event_code',
        'title',
        'description',
        'is_active',
        'questions_json',
        'crm_batch_id',
        'created_by',
    ];
    protected $useTimestamps = true;
}

