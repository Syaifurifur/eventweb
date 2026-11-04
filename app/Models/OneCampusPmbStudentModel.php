<?php

namespace App\Models;

use CodeIgniter\Model;

class OneCampusPmbStudentModel extends Model
{
    protected $table = 'one_campus_pmb_students';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'school_report_id',
        'school_name',
        'npsn',
        'pmb_period',
        'student_name',
        'registration_no',
        'student_phone',
        'student_email',
        'study_program',
        'campus_id',
        'kode_ubsi',
        'campus_code',
        'status',
        'batch_name',
        'created_by',
    ];
    protected $useTimestamps = true;

    public function withDetails(): self
    {
        return $this
            ->select('one_campus_pmb_students.*, campuses.name AS campus_name, campuses.kode_ubsi AS campus_kode_ubsi, campuses.campus_code AS campus_kode_kampus, one_campus_school_reports.city AS school_city')
            ->join('campuses', 'campuses.id = one_campus_pmb_students.campus_id', 'left')
            ->join('one_campus_school_reports', 'one_campus_school_reports.id = one_campus_pmb_students.school_report_id', 'left');
    }
}

