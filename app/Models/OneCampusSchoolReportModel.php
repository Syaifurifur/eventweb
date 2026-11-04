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
        'npsn',
        'school_name',
        'city',
        'school_type',
        'school_status',
        'headmaster_name',
        'school_phone',
        'school_email',
        'contact_name',
        'contact_phone',
        'contact_email',
        'teacher_name',
        'phone',
        'position',
        'pmb_count',
        'status',
        'pmb_period',
        'mou_file',
        'mou_drive_link',
        'mou_uploaded_at',
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
