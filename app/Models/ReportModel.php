<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'event_id', 'realization_status', 'score', 'notes', 'document_link', 'promotion_link', 'publication_link'];
    protected $useTimestamps = true;
}
