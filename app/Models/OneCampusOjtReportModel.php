<?php

namespace App\Models;

use CodeIgniter\Model;

class OneCampusOjtReportModel extends Model
{
    protected $table = 'one_campus_ojt_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'campus_id',
        'student_name',
        'origin_school',
        'major',
        'phone',
        'ojt_period',
        'ojt_start_month',
        'ojt_end_month',
        'notes',
        'created_by',
    ];
    protected $useTimestamps = true;

    public function withCampus(): self
    {
        return $this
            ->select('one_campus_ojt_reports.*, campuses.name AS campus_name, users.name AS creator_name')
            ->join('campuses', 'campuses.id = one_campus_ojt_reports.campus_id', 'left')
            ->join('users', 'users.id = one_campus_ojt_reports.created_by', 'left');
    }
}
