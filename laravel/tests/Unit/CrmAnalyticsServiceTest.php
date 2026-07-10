<?php

namespace Tests\Unit;

use App\Services\CrmAnalyticsService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CrmAnalyticsServiceTest extends TestCase
{
    public function test_it_normalizes_indonesian_whatsapp_numbers(): void
    {
        $service = new CrmAnalyticsService();

        $this->assertSame('628123456789', $service->normalizeWhatsapp('0812-3456-789'));
        $this->assertSame('628123456789', $service->normalizeWhatsapp('+62 812 3456 789'));
        $this->assertTrue($service->isValidWhatsapp('0812-3456-789'));
        $this->assertFalse($service->isValidWhatsapp('12345'));
    }

    public function test_it_calculates_core_kpis_and_quality_issues_consistently(): void
    {
        $service = new CrmAnalyticsService();
        $now = new DateTimeImmutable();
        $createdClosing = $now->modify('-2 days');

        $analytics = $service->build([
            $this->lead([
                'id' => 1,
                'whatsapp' => '08123456789',
                'agent_id' => 10,
                'label' => 'Closing',
                'stage' => 'Tahap 3',
                'created_at' => $createdClosing->format('Y-m-d H:i:s'),
                'responded_at' => $createdClosing->modify('+12 hours')->format('Y-m-d H:i:s'),
            ]),
            $this->lead([
                'id' => 2,
                'whatsapp' => '628123456789',
                'school_origin' => '-',
                'created_at' => $now->modify('-8 days')->format('Y-m-d H:i:s'),
            ]),
            $this->lead([
                'id' => 3,
                'whatsapp' => '12345',
                'label' => 'Minat/Tertarik',
                'stage' => 'Tahap 2',
                'created_at' => $now->modify('-3 days')->format('Y-m-d H:i:s'),
            ]),
        ]);

        $summary = $analytics['summary'];
        $this->assertSame(3, $summary['total']);
        $this->assertSame(1, $summary['unique']);
        $this->assertSame(2, $summary['valid']);
        $this->assertSame(1, $summary['assigned']);
        $this->assertSame(2, $summary['processed']);
        $this->assertSame(1, $summary['fresh']);
        $this->assertSame(1, $summary['closing']);
        $this->assertSame(1, $summary['quality']['duplicate']);
        $this->assertSame(1, $summary['quality']['invalid_wa']);
        $this->assertSame(1, $summary['quality']['missing_school']);
        $this->assertSame(1, $summary['quality']['processed_unassigned']);
        $this->assertSame(100.0, $summary['sla_rate']);
    }

    public function test_sla_filter_and_drilldown_return_the_same_population(): void
    {
        $service = new CrmAnalyticsService();
        $now = new DateTimeImmutable();
        $leads = [
            $this->lead([
                'id' => 1,
                'agent_id' => 10,
                'created_at' => $now->modify('-3 days')->format('Y-m-d H:i:s'),
            ]),
            $this->lead([
                'id' => 2,
                'agent_id' => 10,
                'created_at' => $now->modify('-2 hours')->format('Y-m-d H:i:s'),
            ]),
        ];

        $filtered = $service->applyOperationalFilters($leads, 'missed', '');
        $drilldown = $service->drilldown($leads, 'sla_missed');

        $this->assertCount(1, $filtered);
        $this->assertSame(1, $filtered[0]['id']);
        $this->assertSame(1, $drilldown['total']);
        $this->assertSame(1, $drilldown['rows'][0]['id']);
    }

    private function lead(array $overrides): array
    {
        return array_merge([
            'id' => 0,
            'student_name' => 'Lead Test',
            'school_origin' => 'SMA Test',
            'whatsapp' => '08123456789',
            'stage' => 'Tahap 1',
            'label' => null,
            'agent_id' => null,
            'responded_at' => null,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'campus_id' => 1,
            'campus_name' => 'Kampus Test',
            'event_id' => 1,
            'event_code' => 'EVT-TEST',
            'event_title' => 'Event Test',
            'event_category' => 'Edufair',
            'agent_name' => null,
        ], $overrides);
    }
}
