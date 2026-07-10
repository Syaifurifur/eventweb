<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'code',
        'title',
        'category',
        'campus_id',
        'event_date',
        'event_end_date',
        'event_time',
        'location',
        'status',
        'created_by',
        'participants_count',
        'budget',
        'progress',
        'description',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'title' => 'required|min_length[5]|max_length[180]',
        'category' => 'required|max_length[80]',
        'campus_id' => 'required|is_natural_no_zero',
        'event_date' => 'required|valid_date',
        'event_end_date' => 'permit_empty|valid_date',
        'event_time' => 'required',
        'location' => 'required|max_length[180]',
        'status' => 'required|in_list[draft,diajukan,disetujui,ditolak,selesai]',
        'created_by' => 'required|is_natural_no_zero',
    ];

    public function withCreator(): self
    {
        return $this
            ->select('events.*, users.name AS creator_name, campuses.name AS campus_name')
            ->join('users', 'users.id = events.created_by', 'left')
            ->join('campuses', 'campuses.id = events.campus_id', 'left');
    }

    public function findByCode(string $code): ?array
    {
        return $this->withCreator()->where('events.code', $code)->first();
    }
}
