<?php

namespace App\Models;

use CodeIgniter\Model;

class ProposalModel extends Model
{
    protected $table = 'proposals';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'code',
        'event_id',
        'status',
        'content',
        'document_link',
        'template_name',
        'template_path',
        'generated_path',
        'generated_at',
        'ai_model',
        'ai_notes',
    ];
    protected $useTimestamps = true;
}
