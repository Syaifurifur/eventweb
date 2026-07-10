<?php

namespace App\Models;

use CodeIgniter\Model;

class OneCampusBannerReportModel extends Model
{
    protected $table = 'one_campus_banner_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'campus_id',
        'location',
        'installed_at',
        'theme',
        'drive_photo_link',
        'created_by',
    ];
    protected $useTimestamps = true;

    public function withCampus(): self
    {
        return $this
            ->select('one_campus_banner_reports.*, campuses.name AS campus_name, users.name AS creator_name')
            ->join('campuses', 'campuses.id = one_campus_banner_reports.campus_id', 'left')
            ->join('users', 'users.id = one_campus_banner_reports.created_by', 'left');
    }
}
