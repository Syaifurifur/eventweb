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
        'bjs_status',
        'bjs_verified_at',
        'bjs_verified_by',
        'bjs_notes',
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
            ->select('one_campus_school_reports.*, campuses.name AS campus_name, campuses.kode_ubsi AS campus_kode_ubsi, campuses.campus_code AS campus_kode_kampus, users.name AS creator_name, verifiers.name AS bjs_verifier_name')
            ->join('campuses', 'campuses.id = one_campus_school_reports.campus_id', 'left')
            ->join('users', 'users.id = one_campus_school_reports.created_by', 'left')
            ->join('users AS verifiers', 'verifiers.id = one_campus_school_reports.bjs_verified_by', 'left');
    }
}
