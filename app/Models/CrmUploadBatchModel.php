<?php

namespace App\Models;

use CodeIgniter\Model;

class CrmUploadBatchModel extends Model
{
    protected $table = 'crm_upload_batches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'batch_code',
        'file_name',
        'event_id',
        'total_rows',
        'uploaded_by',
        'uploaded_at',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'batch_code' => 'required|max_length[80]|is_unique[crm_upload_batches.batch_code,id,{id}]',
        'file_name' => 'permit_empty|max_length[255]',
        'event_id' => 'permit_empty|is_natural_no_zero',
        'total_rows' => 'required|is_natural',
        'uploaded_by' => 'permit_empty|is_natural_no_zero',
        'uploaded_at' => 'required|valid_date[Y-m-d H:i:s]',
    ];
}
