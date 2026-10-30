<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentTemplateModel extends Model
{
    protected $table = 'document_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'doc_type',
        'event_category',
        'description',
        'file_path',
        'file_format',
        'thumbnail_path',
        'structure_json',
        'status',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

