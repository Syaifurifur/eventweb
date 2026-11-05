<?php

namespace App\Services;

use DateTimeImmutable;
use Throwable;

class CrmAnalyticsService
{
    private const MINIMUM_SAMPLE = 20;

    public function build(array $leads): array
    {
        $duplicatePhones = $this->duplicatePhoneMap($leads);
        $summary = $this->summarize($leads, $duplicatePhones);

        return [
            'summary' => $summary,
            'funnel' => $this->funnel($leads),
            'trend' => $this->trend($leads),
            'aging' => $this->aging($leads),
            'alerts' => $this->alerts($summary),
            'quality' => $summary['quality'],
            'scorecards' => [
                'campuses' => $this->scorecard($leads, 'campus'),
                'events' => $this->scorecard($leads, 'event'),
                'agents' => $this->scorecard($leads, 'agent'),
                'schools' => $this->scorecard($leads, 'school'),
            ],
            'freshness' => $this->freshness($leads),
            'minimumSample' => self::MINIMUM_SAMPLE,
            'legacySla' => true,
            'legacyClosingVerification' => true,
        ];
    }

    public function applyOperationalFilters(array $leads, string $slaStatus, string $quality): array
    {
        if ($slaStatus === '' && $quality === '') {
            return $leads;
        }

        $duplicatePhones = $this->duplicatePhoneMap($leads);

        return array_values(array_filter($leads, function (array $lead) use ($slaStatus, $quality, $duplicatePhones): bool {
            if ($slaStatus !== '' && ! $this->matchesSlaStatus($lead, $slaStatus)) {
                return false;
            }

            return $quality === '' || $this->matchesQuality($lead, $quality, $duplicatePhones);
        }));
    }

    public function drilldown(array $leads, string $focus, int $limit = 25): array
    {
        $duplicatePhones = $this->duplicatePhoneMap($leads);
        $rows = array_values(array_filter(
            $leads,
            fn (array $lead): bool => $this->matchesFocus($lead, $focus, $duplicatePhones),
        ));

        usort($rows, static function (array $left, array $right): int {
            return strcmp((string) ($left['created_at'] ?? ''), (string) ($right['created_at'] ?? ''))
                ?: ((int) ($right['id'] ?? 0) <=> (int) ($left['id'] ?? 0));
        });

        return [
            'focus' => $focus,
            'label' => $this->focusLabel($focus),
            'total' => count($rows),
            'rows' => array_slice($rows, 0, max(1, $limit)),
        ];
    }

    public function focusLabel(string $focus): string
    {
        return [
            'total' => 'Seluruh leads pada filter aktif',
            'valid' => 'Leads dengan nomor WhatsApp valid',
            'assigned' => 'Leads yang sudah memiliki agent',
            'unassigned' => 'Leads belum diassign',
            'processed' => 'Leads yang sudah diproses',
            'fresh' => 'Leads Fresh',
            'closing' => 'Leads Closing',
            'backlog' => 'Backlog aktif',
            'sla_missed' => 'Kontak pertama melewati SLA 24 jam',
            'overdue' => 'Kontak pertama overdue',
            'fresh7' => 'Fresh lebih dari 7 hari',
            'data_quality' => 'Leads dengan isu kualitas data',
            'invalid_wa' => 'Nomor WhatsApp kosong atau tidak valid',
            'duplicate' => 'Nomor WhatsApp berulang',
            'missing_school' => 'Asal sekolah belum lengkap',
            'processed_unassigned' => 'Diproses tetapi belum diassign',
            'closing_unverified' => 'Closing belum terverifikasi PMB',
        ][$focus] ?? 'Detail leads';
    }

    public function normalizeWhatsapp(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    public function isValidWhatsapp(?string $value): bool
    {
        return preg_match('/^628\d{7,11}$/', $this->normalizeWhatsapp($value)) === 1;
    }

    private function summarize(array $leads, array $duplicatePhones): array
    {
        $summary = [
            'total' => count($leads),
            'unique' => 0,
            'valid' => 0,
            'assigned' => 0,
            'assigned_valid' => 0,
            'unassigned' => 0,
            'processed' => 0,
            'processed_assigned' => 0,
            'fresh' => 0,
            'closing' => 0,
            'closing_valid' => 0,
            'interested' => 0,
            'backlog' => 0,
            'overdue' => 0,
            'fresh7' => 0,
            'sla_eligible' => 0,
            'sla_within' => 0,
            'quality_records' => 0,
            'quality' => [
                'invalid_wa' => 0,
                'duplicate' => 0,
                'missing_school' => 0,
                'processed_unassigned' => 0,
                'closing_unverified' => 0,
            ],
        ];
        $uniquePhones = [];
        $now = new DateTimeImmutable();

        foreach ($leads as $lead) {
            $phone = $this->normalizeWhatsapp((string) ($lead['whatsapp'] ?? ''));
            $validWhatsapp = $this->isValidWhatsapp($phone);
            $processed = $this->isProcessed($lead);
            $assigned = ! empty($lead['agent_id']);
            $active = $this->isBacklog($lead);
            $ageHours = $this->ageHours($lead, $now);
            $hasQualityIssue = false;

            if ($validWhatsapp) {
                $summary['valid']++;
                $uniquePhones[$phone] = true;
            } else {
                $summary['quality']['invalid_wa']++;
                $hasQualityIssue = true;
            }

            if ($phone !== '' && isset($duplicatePhones[$phone])) {
                $hasQualityIssue = true;
            }

            if ($assigned) {
                $summary['assigned']++;
                $summary['assigned_valid'] += $validWhatsapp ? 1 : 0;
            } else {
                $summary['unassigned']++;
            }

            if ($processed) {
                $summary['processed']++;
                $summary['processed_assigned'] += $assigned ? 1 : 0;
            } else {
                $summary['fresh']++;
            }

            if (($lead['label'] ?? '') === 'Closing') {
                $summary['closing']++;
                $summary['closing_valid'] += $validWhatsapp ? 1 : 0;
                $summary['quality']['closing_unverified']++;
                $hasQualityIssue = true;
            }
            if (in_array(($lead['label'] ?? ''), ['Minat/Tertarik', 'Closing'], true)) {
                $summary['interested']++;
            }

            if ($active) {
                $summary['backlog']++;
            }
            if ($assigned && ! $processed && $ageHours !== null && $ageHours > 24) {
                $summary['overdue']++;
            }
            if (! $processed && $ageHours !== null && $ageHours > 168) {
                $summary['fresh7']++;
            }

            $sla = $this->slaState($lead, $now);
            if ($sla !== 'pending' && $sla !== 'unknown') {
                $summary['sla_eligible']++;
                if ($sla === 'within') {
                    $summary['sla_within']++;
                }
            }

            $school = trim((string) ($lead['school_origin'] ?? ''));
            if ($school === '' || $school === '-') {
                $summary['quality']['missing_school']++;
                $hasQualityIssue = true;
            }
            if ($processed && ! $assigned) {
                $summary['quality']['processed_unassigned']++;
                $hasQualityIssue = true;
            }
            if ($hasQualityIssue) {
                $summary['quality_records']++;
            }
        }

        $summary['unique'] = count($uniquePhones);
        $summary['quality']['duplicate'] = array_sum(array_map(
            static fn (int $count): int => max(0, $count - 1),
            $duplicatePhones,
        ));
        $total = max(1, $summary['total']);
        $summary['assignment_rate'] = $this->rate($summary['assigned_valid'], $summary['valid']);
        $summary['processing_rate'] = $this->rate($summary['processed_assigned'], $summary['assigned']);
        $summary['closing_rate_all'] = $this->rate($summary['closing_valid'], $summary['valid']);
        $summary['closing_rate_processed'] = $this->rate($summary['closing'], $summary['processed']);
        $summary['sla_rate'] = $this->rate($summary['sla_within'], $summary['sla_eligible']);
        $summary['quality_score'] = round(max(0, 100 - (($summary['quality_records'] / $total) * 100)), 1);

        return $summary;
    }

    private function funnel(array $leads): array
    {
        $total = count($leads);
        $assigned = 0;
        $processed = 0;
        $interested = 0;
        $closing = 0;

        foreach ($leads as $lead) {
            $assigned += ! empty($lead['agent_id']) ? 1 : 0;
            $processed += $this->isProcessed($lead) ? 1 : 0;
            $interested += in_array(($lead['label'] ?? ''), ['Minat/Tertarik', 'Closing'], true) ? 1 : 0;
            $closing += ($lead['label'] ?? '') === 'Closing' ? 1 : 0;
        }

        return array_map(function (array $row) use ($total): array {
            $row['rate'] = $this->rate($row['value'], $total);

            return $row;
        }, [
            ['label' => 'Input', 'value' => $total, 'focus' => 'total'],
            ['label' => 'Assigned', 'value' => $assigned, 'focus' => 'assigned'],
            ['label' => 'Processed', 'value' => $processed, 'focus' => 'processed'],
            ['label' => 'Minat', 'value' => $interested, 'focus' => 'processed'],
            ['label' => 'Closing', 'value' => $closing, 'focus' => 'closing'],
        ]);
    }

    private function trend(array $leads): array
    {
        $latest = null;
        foreach ($leads as $lead) {
            $date = $this->date((string) ($lead['created_at'] ?? ''));
            if ($date !== null && ($latest === null || $date > $latest)) {
                $latest = $date;
            }
        }
        $latest ??= new DateTimeImmutable();
        $cursor = $latest->modify('first day of this month')->modify('-5 months');
        $buckets = [];
        for ($index = 0; $index < 6; $index++) {
            $key = $cursor->format('Y-m');
            $buckets[$key] = [
                'key' => $key,
                'label' => $cursor->format('M Y'),
                'input' => 0,
                'processed' => 0,
                'closing' => 0,
            ];
            $cursor = $cursor->modify('+1 month');
        }

        foreach ($leads as $lead) {
            $date = $this->date((string) ($lead['created_at'] ?? ''));
            $key = $date?->format('Y-m');
            if ($key === null || ! isset($buckets[$key])) {
                continue;
            }
            $buckets[$key]['input']++;
            $buckets[$key]['processed'] += $this->isProcessed($lead) ? 1 : 0;
            $buckets[$key]['closing'] += ($lead['label'] ?? '') === 'Closing' ? 1 : 0;
        }

        $max = 1;
        foreach ($buckets as $bucket) {
            $max = max($max, $bucket['input'], $bucket['processed'], $bucket['closing']);
        }
        foreach ($buckets as &$bucket) {
            foreach (['input', 'processed', 'closing'] as $metric) {
                $bucket[$metric . '_height'] = round(($bucket[$metric] / $max) * 100, 2);
            }
        }
        unset($bucket);

        return array_values($buckets);
    }

    private function aging(array $leads): array
    {
        $buckets = [
            ['label' => '< 24 jam', 'min' => 0, 'max' => 24, 'count' => 0, 'class' => 'safe'],
            ['label' => '1-3 hari', 'min' => 24, 'max' => 72, 'count' => 0, 'class' => 'watch'],
            ['label' => '4-7 hari', 'min' => 72, 'max' => 168, 'count' => 0, 'class' => 'risk'],
            ['label' => '8-14 hari', 'min' => 168, 'max' => 336, 'count' => 0, 'class' => 'danger'],
            ['label' => '> 14 hari', 'min' => 336, 'max' => null, 'count' => 0, 'class' => 'critical'],
        ];
        $now = new DateTimeImmutable();

        foreach ($leads as $lead) {
            if (! $this->isBacklog($lead)) {
                continue;
            }
            $hours = $this->ageHours($lead, $now);
            if ($hours === null) {
                continue;
            }
            foreach ($buckets as &$bucket) {
                if ($hours >= $bucket['min'] && ($bucket['max'] === null || $hours < $bucket['max'])) {
                    $bucket['count']++;
                    break;
                }
            }
            unset($bucket);
        }

        $max = max(1, ...array_column($buckets, 'count'));
        foreach ($buckets as &$bucket) {
            $bucket['width'] = round(($bucket['count'] / $max) * 100, 2);
        }
        unset($bucket);

        return $buckets;
    }

    private function alerts(array $summary): array
    {
        return [
            ['label' => 'Belum diassign', 'value' => $summary['unassigned'], 'focus' => 'unassigned', 'severity' => 'critical', 'detail' => 'Belum memiliki owner CRM.'],
            ['label' => 'Kontak overdue', 'value' => $summary['overdue'], 'focus' => 'overdue', 'severity' => 'critical', 'detail' => 'Assigned, belum diproses, usia >24 jam.'],
            ['label' => 'Fresh >7 hari', 'value' => $summary['fresh7'], 'focus' => 'fresh7', 'severity' => 'high', 'detail' => 'Risiko kehilangan minat.'],
            ['label' => 'WA invalid', 'value' => $summary['quality']['invalid_wa'], 'focus' => 'invalid_wa', 'severity' => 'medium', 'detail' => 'Perlu koreksi kanal kontak.'],
            ['label' => 'Processed tanpa owner', 'value' => $summary['quality']['processed_unassigned'], 'focus' => 'processed_unassigned', 'severity' => 'high', 'detail' => 'Histori assignment perlu diperbaiki.'],
            ['label' => 'Closing belum verified', 'value' => $summary['quality']['closing_unverified'], 'focus' => 'closing_unverified', 'severity' => 'medium', 'detail' => 'Belum ada referensi PMB pada skema legacy.'],
        ];
    }

    private function scorecard(array $leads, string $dimension): array
    {
        $groups = [];
        foreach ($leads as $lead) {
            [$key, $label, $meta] = $this->dimension($lead, $dimension);
            if ($key === '') {
                continue;
            }
            if (! isset($groups[$key])) {
                $groups[$key] = ['label' => $label, 'meta' => $meta, 'leads' => []];
            }
            $groups[$key]['leads'][] = $lead;
        }

        $rows = [];
        foreach ($groups as $group) {
            $row = $this->groupSummary($group['leads']);
            $row['label'] = $group['label'];
            $row['meta'] = $group['meta'];
            if ($dimension === 'school') {
                $row['events'] = count(array_unique(array_filter(array_map(
                    static fn (array $lead): string => (string) ($lead['event_id'] ?? ''),
                    $group['leads'],
                ))));
            }
            $rows[] = $row;
        }

        usort($rows, static function (array $left, array $right): int {
            return ((int) $right['total'] <=> (int) $left['total'])
                ?: strcmp((string) $left['label'], (string) $right['label']);
        });

        return array_slice($rows, 0, 10);
    }

    private function groupSummary(array $leads): array
    {
        $total = count($leads);
        $assigned = 0;
        $processed = 0;
        $closing = 0;
        $backlog = 0;
        $valid = 0;
        $slaEligible = 0;
        $slaWithin = 0;
        $now = new DateTimeImmutable();

        foreach ($leads as $lead) {
            $assigned += ! empty($lead['agent_id']) ? 1 : 0;
            $processed += $this->isProcessed($lead) ? 1 : 0;
            $closing += ($lead['label'] ?? '') === 'Closing' ? 1 : 0;
            $backlog += $this->isBacklog($lead) ? 1 : 0;
            $valid += $this->isValidWhatsapp((string) ($lead['whatsapp'] ?? '')) ? 1 : 0;
            $sla = $this->slaState($lead, $now);
            if ($sla !== 'pending' && $sla !== 'unknown') {
                $slaEligible++;
                $slaWithin += $sla === 'within' ? 1 : 0;
            }
        }

        return [
            'total' => $total,
            'assigned' => $assigned,
            'processed' => $processed,
            'closing' => $closing,
            'backlog' => $backlog,
            'valid_rate' => $this->rate($valid, $total),
            'process_rate' => $this->rate($processed, $assigned),
            'closing_rate' => $processed >= self::MINIMUM_SAMPLE ? $this->rate($closing, $processed) : null,
            'sla_rate' => $this->rate($slaWithin, $slaEligible),
            'sample_ok' => $processed >= self::MINIMUM_SAMPLE,
        ];
    }

    private function dimension(array $lead, string $dimension): array
    {
        if ($dimension === 'campus') {
            $label = trim((string) ($lead['campus_name'] ?? ''));

            return [$label !== '' ? (string) ($lead['campus_id'] ?? $label) : '', $label, 'Kampus'];
        }
        if ($dimension === 'event') {
            $code = trim((string) ($lead['event_code'] ?? ''));
            $title = trim((string) ($lead['event_title'] ?? ''));
            $label = trim($code . ($title !== '' ? ' - ' . $title : ''));

            return [$label !== '' ? (string) ($lead['event_id'] ?? $label) : '', $label, (string) ($lead['event_category'] ?? 'Tanpa kategori')];
        }
        if ($dimension === 'agent') {
            $label = trim((string) ($lead['agent_name'] ?? ''));

            return [$label !== '' ? (string) ($lead['agent_id'] ?? $label) : '', $label, (string) ($lead['campus_name'] ?? '')];
        }

        $label = trim((string) ($lead['school_origin'] ?? ''));
        if ($label === '-' || $label === '') {
            return ['', '', ''];
        }

        return [mb_strtolower($label), $label, (string) ($lead['campus_name'] ?? '')];
    }

    private function matchesFocus(array $lead, string $focus, array $duplicatePhones): bool
    {
        $processed = $this->isProcessed($lead);
        $assigned = ! empty($lead['agent_id']);
        $ageHours = $this->ageHours($lead, new DateTimeImmutable());
        $phone = $this->normalizeWhatsapp((string) ($lead['whatsapp'] ?? ''));

        return match ($focus) {
            'valid' => $this->isValidWhatsapp($phone),
            'assigned' => $assigned,
            'unassigned' => ! $assigned,
            'processed' => $processed,
            'fresh' => ! $processed,
            'closing' => ($lead['label'] ?? '') === 'Closing',
            'backlog' => $this->isBacklog($lead),
            'sla_missed' => $this->slaState($lead, new DateTimeImmutable()) === 'missed',
            'overdue' => $assigned && ! $processed && $ageHours !== null && $ageHours > 24,
            'fresh7' => ! $processed && $ageHours !== null && $ageHours > 168,
            'invalid_wa' => ! $this->isValidWhatsapp($phone),
            'duplicate' => $phone !== '' && isset($duplicatePhones[$phone]),
            'missing_school' => in_array(trim((string) ($lead['school_origin'] ?? '')), ['', '-'], true),
            'processed_unassigned' => $processed && ! $assigned,
            'closing_unverified' => ($lead['label'] ?? '') === 'Closing',
            'data_quality' => $this->matchesQuality($lead, 'any', $duplicatePhones),
            default => true,
        };
    }

    private function matchesQuality(array $lead, string $quality, array $duplicatePhones): bool
    {
        $phone = $this->normalizeWhatsapp((string) ($lead['whatsapp'] ?? ''));
        $missingSchool = in_array(trim((string) ($lead['school_origin'] ?? '')), ['', '-'], true);
        $processedUnassigned = $this->isProcessed($lead) && empty($lead['agent_id']);
        $closingUnverified = ($lead['label'] ?? '') === 'Closing';
        $duplicate = $phone !== '' && isset($duplicatePhones[$phone]);
        $invalid = ! $this->isValidWhatsapp($phone);

        return match ($quality) {
            'invalid_wa' => $invalid,
            'duplicate' => $duplicate,
            'missing_school' => $missingSchool,
            'processed_unassigned' => $processedUnassigned,
            'closing_unverified' => $closingUnverified,
            default => $invalid || $duplicate || $missingSchool || $processedUnassigned || $closingUnverified,
        };
    }

    private function matchesSlaStatus(array $lead, string $status): bool
    {
        return $this->slaState($lead, new DateTimeImmutable()) === $status;
    }

    private function duplicatePhoneMap(array $leads): array
    {
        $counts = [];
        foreach ($leads as $lead) {
            $phone = $this->normalizeWhatsapp((string) ($lead['whatsapp'] ?? ''));
            if ($phone !== '' && $this->isValidWhatsapp($phone)) {
                $counts[$phone] = ($counts[$phone] ?? 0) + 1;
            }
        }

        return array_filter($counts, static fn (int $count): bool => $count > 1);
    }

    private function isProcessed(array $lead): bool
    {
        return ! empty($lead['responded_at'])
            || trim((string) ($lead['label'] ?? '')) !== ''
            || (($lead['stage'] ?? 'Tahap 1') !== 'Tahap 1');
    }

    private function isBacklog(array $lead): bool
    {
        return ! in_array(($lead['label'] ?? ''), ['Closing', 'Menolak'], true);
    }

    private function slaState(array $lead, DateTimeImmutable $now): string
    {
        if (empty($lead['agent_id'])) {
            return 'unknown';
        }
        $created = $this->date((string) ($lead['created_at'] ?? ''));
        if ($created === null) {
            return 'unknown';
        }
        $responded = $this->date((string) ($lead['responded_at'] ?? ''));
        if ($responded !== null) {
            $hours = ($responded->getTimestamp() - $created->getTimestamp()) / 3600;

            return $hours >= 0 && $hours <= 24 ? 'within' : 'missed';
        }

        return (($now->getTimestamp() - $created->getTimestamp()) / 3600) > 24 ? 'missed' : 'pending';
    }

    private function ageHours(array $lead, DateTimeImmutable $now): ?float
    {
        $created = $this->date((string) ($lead['created_at'] ?? ''));
        if ($created === null) {
            return null;
        }

        return max(0, ($now->getTimestamp() - $created->getTimestamp()) / 3600);
    }

    private function freshness(array $leads): ?string
    {
        $latest = null;
        foreach ($leads as $lead) {
            $date = $this->date((string) ($lead['updated_at'] ?? $lead['created_at'] ?? ''));
            if ($date !== null && ($latest === null || $date > $latest)) {
                $latest = $date;
            }
        }

        return $latest?->format('Y-m-d H:i:s');
    }

    private function rate(int $numerator, int $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 1) : 0.0;
    }

    private function date(string $value): ?DateTimeImmutable
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
