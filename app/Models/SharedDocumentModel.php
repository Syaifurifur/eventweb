<?php

namespace App\Models;

use CodeIgniter\Model;

class SharedDocumentModel extends Model
{
    protected $table = 'shared_documents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'category',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'mime_type',
        'download_count',
        'status',
        'uploaded_by',
        'uploader_name',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

