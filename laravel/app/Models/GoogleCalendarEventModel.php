<?php

namespace App\Models;

use CodeIgniter\Model;

class GoogleCalendarEventModel extends Model
{
    protected $table = 'google_calendar_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'google_event_id',
        'summary',
        'description',
        'location',
        'start_at',
        'end_at',
        'is_all_day',
        'html_link',
        'status',
        'raw_payload',
    ];
    protected $useTimestamps = true;

    public function eventsForMonth(int $userId, string $month): array
    {
        $start = new \DateTimeImmutable($month . '-01 00:00:00');
        $end = $start->modify('first day of next month');

        return $this
            ->where('user_id', $userId)
            ->where('start_at >=', $start->format('Y-m-d H:i:s'))
            ->where('start_at <', $end->format('Y-m-d H:i:s'))
            ->where('status !=', 'cancelled')
            ->orderBy('start_at', 'ASC')
            ->findAll();
    }

    public function deleteMissingForRange(int $userId, \DateTimeInterface $start, \DateTimeInterface $end, array $googleEventIds): int
    {
        $builder = $this->builder()
            ->where('user_id', $userId)
            ->where('start_at >=', $start->format('Y-m-d H:i:s'))
            ->where('start_at <', $end->format('Y-m-d H:i:s'));

        if ($googleEventIds !== []) {
            $builder->whereNotIn('google_event_id', array_values(array_unique($googleEventIds)));
        }

        return $builder->delete() ? $this->db->affectedRows() : 0;
    }

    public function deleteByGoogleEventId(string $googleEventId): int
    {
        $googleEventId = trim($googleEventId);
        if ($googleEventId === '') {
            return 0;
        }

        return $this->builder()
            ->where('google_event_id', $googleEventId)
            ->delete() ? $this->db->affectedRows() : 0;
    }
}
