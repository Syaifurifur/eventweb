<?php

namespace App\Models;

use CodeIgniter\Model;

class EventChecklistItemModel extends Model
{
    protected $table = 'event_checklist_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'event_id',
        'template_id',
        'name',
        'phase',
        'pic_user_id',
        'is_assigned',
        'assigned_at',
        'deadline',
        'status',
        'notes',
        'material_drive_link',
        'drive_link',
        'submitted_by',
        'submitted_at',
        'completed_at',
    ];
    protected $useTimestamps = true;

    public function forEvent(int $eventId): array
    {
        return $this
            ->select('event_checklist_items.*, users.name AS pic_name')
            ->join('users', 'users.id = event_checklist_items.pic_user_id', 'left')
            ->where('event_checklist_items.event_id', $eventId)
            ->orderBy("FIELD(event_checklist_items.phase, 'Pra Event', 'Event', 'Pasca Event')", '', false)
            ->orderBy('event_checklist_items.id', 'ASC')
            ->findAll();
    }

    public function assignedToUser(int $userId, int $limit = 8, ?int $campusId = null): array
    {
        return $this->assignedToUserBuilder($userId, $campusId)
            ->orderBy("FIELD(event_checklist_items.status, 'pending', 'submitted', 'done')", '', false)
            ->orderBy('event_checklist_items.deadline', 'ASC')
            ->orderBy('events.event_date', 'ASC')
            ->findAll($limit);
    }

    public function allAssignedToUser(int $userId, ?int $campusId = null): array
    {
        return $this->assignedToUserBuilder($userId, $campusId)
            ->orderBy("FIELD(event_checklist_items.status, 'pending', 'submitted', 'done')", '', false)
            ->orderBy('event_checklist_items.deadline', 'ASC')
            ->orderBy('events.event_date', 'ASC')
            ->findAll();
    }

    public function allAssignedTasks(?int $campusId = null): array
    {
        return $this->assignedTasksBuilder($campusId)
            ->orderBy("FIELD(event_checklist_items.status, 'pending', 'submitted', 'done')", '', false)
            ->orderBy('event_checklist_items.deadline', 'ASC')
            ->orderBy('events.event_date', 'ASC')
            ->findAll();
    }

    private function assignedToUserBuilder(int $userId, ?int $campusId = null): self
    {
        return $this->assignedTasksBuilder($campusId)
            ->where('event_checklist_items.pic_user_id', $userId);
    }

    private function assignedTasksBuilder(?int $campusId = null): self
    {
        $builder = $this
            ->select('event_checklist_items.*, events.code AS event_code, events.title AS event_title, events.event_date, events.location, events.campus_id AS event_campus_id, users.name AS pic_name')
            ->join('events', 'events.id = event_checklist_items.event_id', 'inner')
            ->join('users', 'users.id = event_checklist_items.pic_user_id', 'left')
            ->where('event_checklist_items.is_assigned', 1)
            ->where('events.deleted_at', null);

        if ($campusId !== null) {
            $builder->where('events.campus_id', $campusId);
        }

        return $builder;
    }
}
