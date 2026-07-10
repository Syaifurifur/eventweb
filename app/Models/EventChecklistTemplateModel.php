<?php

namespace App\Models;

use CodeIgniter\Model;

class EventChecklistTemplateModel extends Model
{
    protected $table = 'event_checklist_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'phase',
        'sort_order',
        'is_system',
        'is_active',
    ];
    protected $useTimestamps = true;
}
