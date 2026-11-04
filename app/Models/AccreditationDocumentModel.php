<?php

namespace App\Models;

use CodeIgniter\Model;

class AccreditationDocumentModel extends Model
{
    protected $table = 'accreditation_documents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'event_id',
        'campus_id',
        'title',
        'event_year',
        'accreditation_standard',
        'doc_type',
        'status',
        'admin_notes',
        'campus_notes',
        'additional_evidence_path',
        'additional_evidence_name',
        'external_evidence_link',
        'designated_by',
        'designated_at',
        'completed_by',
        'completed_at',
        'verified_by',
        'verified_at',
    ];
    protected $useTimestamps = true;

    public function withDetails(): self
    {
        return $this->select('accreditation_documents.*, 
            events.code AS event_code, 
            events.title AS event_title, 
            events.category AS event_category, 
            events.event_date, 
            events.event_end_date, 
            events.location AS event_location, 
            events.status AS event_status,
            campuses.name AS campus_name,
            campuses.campus_code,
            campuses.kode_ubsi,
            des_user.name AS designated_by_name,
            comp_user.name AS completed_by_name,
            ver_user.name AS verified_by_name')
            ->join('events', 'events.id = accreditation_documents.event_id', 'left')
            ->join('campuses', 'campuses.id = accreditation_documents.campus_id', 'left')
            ->join('users AS des_user', 'des_user.id = accreditation_documents.designated_by', 'left')
            ->join('users AS comp_user', 'comp_user.id = accreditation_documents.completed_by', 'left')
            ->join('users AS ver_user', 'ver_user.id = accreditation_documents.verified_by', 'left');
    }
}

