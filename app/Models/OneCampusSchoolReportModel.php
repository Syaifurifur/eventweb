<?php

namespace App\Models;

use CodeIgniter\Model;

class OneCampusSchoolReportModel extends Model
{
    protected $table = 'one_campus_school_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'campus_id',
        'school_name',
        'teacher_name',
        'phone',
        'position',
        'pmb_count',
        'notes',
        'created_by',
    ];
    protected $useTimestamps = true;

    public function withCampus(): self
    {
        return $this
            ->select('one_campus_school_reports.*, campuses.name AS campus_name, users.name AS creator_name')
            ->join('campuses', 'campuses.id = one_campus_school_reports.campus_id', 'left')
            ->join('users', 'users.id = one_campus_school_reports.created_by', 'left');
    }
}
