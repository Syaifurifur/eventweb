<?php

namespace App\Services;

use App\Models\CampusModel;
use Illuminate\Support\Facades\DB;
use Throwable;

class CrmRealtimeReportService
{
    /**
     * Build comprehensive real-time CRM report payload.
     *
     * @param string|null $requestedCampusParam
     * @param bool $canViewAllCampuses
     * @param string $userCampusFullName
     * @return array
     */
    public function getReportData(?string $requestedCampusParam = null, bool $canViewAllCampuses = true, string $userCampusFullName = 'UBSI KAMPUS YOGYAKARTA'): array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        $timestamp = $now->format('d M Y, H:i:s') . ' WIB';

        // 1. Fetch all campuses registered in database
        $dbCampuses = [];
        try {
            $campusRows = (new CampusModel())->orderBy('name', 'ASC')->findAll();
            foreach ($campusRows as $row) {
                $cName = strtoupper(trim((string) ($row['name'] ?? '')));
                if ($cName !== '') {
                    $dbCampuses[$cName] = [
                        'id' => (int) ($row['id'] ?? 0),
                        'name' => $cName,
                        'code' => (string) ($row['campus_code'] ?? ''),
                    ];
                }
            }
        } catch (Throwable $e) {
            // fallback
        }

        // 2. Fetch all live leads from database
        $liveLeads = [];
        try {
            $liveLeads = DB::table('crm_leads as l')
                ->join('events as e', 'l.event_id', '=', 'e.id')
                ->leftJoin('campuses as c', 'e.campus_id', '=', 'c.id')
                ->leftJoin('crm_users as u', 'l.agent_id', '=', 'u.id')
                ->select([
                    'l.id', 'l.batch_id', 'l.event_id', 'l.student_name', 'l.school_origin',
                    'l.whatsapp', 'l.email', 'l.stage', 'l.label', 'l.agent_id', 'l.rejection_reason',
                    'l.responded_at', 'l.pmb_period', 'l.created_at', 'l.updated_at',
                    'e.id as event_db_id', 'e.code as event_code', 'e.title as event_title', 'e.category as event_category',
                    'e.event_date', 'e.location as event_location', 'e.campus_id',
                    'c.name as campus_name', 'c.campus_code',
                    'u.full_name as agent_name'
                ])
                ->get();
        } catch (Throwable $e) {
            $liveLeads = collect([]);
        }

        // 3. Fetch latest follow up logs
        $recentLogs = [];
        try {
            $logRows = DB::table('crm_lead_logs as lg')
                ->leftJoin('crm_leads as l', 'lg.lead_id', '=', 'l.id')
                ->leftJoin('events as e', 'l.event_id', '=', 'e.id')
                ->leftJoin('campuses as c', 'e.campus_id', '=', 'c.id')
                ->leftJoin('crm_users as u', 'lg.user_id', '=', 'u.id')
                ->select([
                    'lg.id',
                    'lg.lead_id',
                    'l.student_name',
                    'e.code as event_code',
                    'e.title as event_title',
                    'c.id as campus_id',
                    'c.name as campus_name',
                    'lg.action',
                    'lg.new_stage',
                    'lg.new_label',
                    'lg.summary',
                    'u.full_name as agent_name',
                    'lg.created_at',
                ])
                ->orderByDesc('lg.created_at')
                ->limit(35)
                ->get();

            foreach ($logRows as $lg) {
                $recentLogs[] = [
                    'id' => (int) $lg->id,
                    'lead_id' => (int) $lg->lead_id,
                    'student_name' => (string) ($lg->student_name ?: 'Calon Mahasiswa #' . $lg->lead_id),
                    'event_code' => (string) ($lg->event_code ?: '-'),
                    'event_title' => (string) ($lg->event_title ?: 'Kegiatan Kampus'),
                    'campus_name' => strtoupper(trim((string) ($lg->campus_name ?: 'Kampus'))),
                    'agent_name' => (string) ($lg->agent_name ?: 'Tim CRM'),
                    'action' => (string) ($lg->action ?: 'update'),
                    'new_stage' => (string) ($lg->new_stage ?: '-'),
                    'new_label' => (string) ($lg->new_label ?: '-'),
                    'summary' => (string) ($lg->summary ?: 'Follow up prospek'),
                    'created_at' => (string) $lg->created_at,
                    'time_ago' => $this->timeElapsedString((string) $lg->created_at),
                ];
            }
        } catch (Throwable $e) {
            $recentLogs = [];
        }

        // 4. Group live leads by campus and events
        $liveCampusAggregates = [];
        foreach ($liveLeads as $lead) {
            $cName = strtoupper(trim((string) ($lead->campus_name ?: 'CYBER UNIVERSITY')));
            if (!isset($liveCampusAggregates[$cName])) {
                $liveCampusAggregates[$cName] = [
                    'campus_name' => $cName,
                    'campus_id' => (int) ($lead->campus_id ?? 0),
                    'total' => 0,
                    'assigned' => 0,
                    'unassigned' => 0,
                    'fresh' => 0,
                    'freshAssigned' => 0,
                    'freshUnassigned' => 0,
                    'closing' => 0,
                    'minat' => 0,
                    'ragu' => 0,
                    'menolak' => 0,
                    'noResp' => 0,
                    't1' => 0,
                    't2' => 0,
                    't3' => 0,
                    'events' => [],
                    'agents' => [],
                    'schools' => [],
                    'reasons' => [],
                ];
            }

            $liveCampusAggregates[$cName]['total']++;
            $isAssigned = !empty($lead->agent_id) || !empty($lead->agent_name);
            if ($isAssigned) {
                $liveCampusAggregates[$cName]['assigned']++;
            } else {
                $liveCampusAggregates[$cName]['unassigned']++;
            }

            $label = trim((string) $lead->label);
            $stage = trim((string) $lead->stage);
            if ($stage === 'Tahap 3' || strcasecmp($label, 'Closing') === 0) {
                $liveCampusAggregates[$cName]['closing']++;
                $liveCampusAggregates[$cName]['t3']++;
            } elseif (stripos($label, 'Minat') !== false) {
                $liveCampusAggregates[$cName]['minat']++;
                $liveCampusAggregates[$cName]['t2']++;
            } elseif (stripos($label, 'Ragu') !== false) {
                $liveCampusAggregates[$cName]['ragu']++;
                $liveCampusAggregates[$cName]['t2']++;
            } elseif (strcasecmp($label, 'Menolak') === 0) {
                $liveCampusAggregates[$cName]['menolak']++;
                $liveCampusAggregates[$cName]['t1']++;
            } elseif (strcasecmp($label, 'Tidak Merespon') === 0) {
                $liveCampusAggregates[$cName]['noResp']++;
                $liveCampusAggregates[$cName]['t1']++;
            } else {
                $liveCampusAggregates[$cName]['fresh']++;
                $liveCampusAggregates[$cName]['t1']++;
                if ($isAssigned) {
                    $liveCampusAggregates[$cName]['freshAssigned']++;
                } else {
                    $liveCampusAggregates[$cName]['freshUnassigned']++;
                }
            }

            // Events breakdown
            $evCode = trim((string) $lead->event_code);
            if ($evCode === '') {
                $evCode = 'EVT-' . ($lead->event_db_id ?: $lead->event_id);
            }
            if (!isset($liveCampusAggregates[$cName]['events'][$evCode])) {
                $liveCampusAggregates[$cName]['events'][$evCode] = [
                    'code' => $evCode,
                    'name' => (string) ($lead->event_title ?: 'Kegiatan Promosi PMB'),
                    'category' => (string) ($lead->event_category ?: 'Promosi'),
                    'date' => (string) ($lead->event_date ?: '2026'),
                    'location' => (string) ($lead->event_location ?: 'Lokasi Kegiatan'),
                    'total' => 0,
                    'unassigned' => 0,
                    'fresh' => 0,
                    'noResp' => 0,
                    'minat' => 0,
                    'ragu' => 0,
                    'menolak' => 0,
                    'closing' => 0,
                    't2Unlabeled' => 0,
                ];
            }
            $liveCampusAggregates[$cName]['events'][$evCode]['total']++;
            if (!$isAssigned) {
                $liveCampusAggregates[$cName]['events'][$evCode]['unassigned']++;
            }
            if ($stage === 'Tahap 3' || strcasecmp($label, 'Closing') === 0) {
                $liveCampusAggregates[$cName]['events'][$evCode]['closing']++;
            } elseif (stripos($label, 'Minat') !== false) {
                $liveCampusAggregates[$cName]['events'][$evCode]['minat']++;
            } elseif (stripos($label, 'Ragu') !== false) {
                $liveCampusAggregates[$cName]['events'][$evCode]['ragu']++;
            } elseif (strcasecmp($label, 'Menolak') === 0) {
                $liveCampusAggregates[$cName]['events'][$evCode]['menolak']++;
            } elseif (strcasecmp($label, 'Tidak Merespon') === 0) {
                $liveCampusAggregates[$cName]['events'][$evCode]['noResp']++;
            } else {
                $liveCampusAggregates[$cName]['events'][$evCode]['fresh']++;
            }

            // Agent breakdown
            $agName = trim((string) ($lead->agent_name ?: ($isAssigned ? 'Agent CRM' : 'Belum diassign')));
            if (!isset($liveCampusAggregates[$cName]['agents'][$agName])) {
                $liveCampusAggregates[$cName]['agents'][$agName] = [
                    'name' => $agName,
                    'total' => 0,
                    'fresh' => 0,
                    'noResp' => 0,
                    'minat' => 0,
                    'ragu' => 0,
                    'menolak' => 0,
                    'closing' => 0,
                    't2Unlabeled' => 0,
                ];
            }
            $liveCampusAggregates[$cName]['agents'][$agName]['total']++;
            if ($stage === 'Tahap 3' || strcasecmp($label, 'Closing') === 0) {
                $liveCampusAggregates[$cName]['agents'][$agName]['closing']++;
            } elseif (stripos($label, 'Minat') !== false) {
                $liveCampusAggregates[$cName]['agents'][$agName]['minat']++;
            } elseif (stripos($label, 'Ragu') !== false) {
                $liveCampusAggregates[$cName]['agents'][$agName]['ragu']++;
            } elseif (strcasecmp($label, 'Menolak') === 0) {
                $liveCampusAggregates[$cName]['agents'][$agName]['menolak']++;
            } elseif (strcasecmp($label, 'Tidak Merespon') === 0) {
                $liveCampusAggregates[$cName]['agents'][$agName]['noResp']++;
            } else {
                $liveCampusAggregates[$cName]['agents'][$agName]['fresh']++;
            }

            // Schools
            $sch = trim((string) $lead->school_origin);
            if ($sch !== '' && $sch !== '-') {
                if (!isset($liveCampusAggregates[$cName]['schools'][$sch])) {
                    $liveCampusAggregates[$cName]['schools'][$sch] = [
                        'name' => $sch,
                        'total' => 0,
                        'closing' => 0,
                        'warm' => 0,
                        'fresh' => 0,
                        'noResp' => 0,
                    ];
                }
                $liveCampusAggregates[$cName]['schools'][$sch]['total']++;
                if ($stage === 'Tahap 3' || strcasecmp($label, 'Closing') === 0) {
                    $liveCampusAggregates[$cName]['schools'][$sch]['closing']++;
                } elseif (stripos($label, 'Minat') !== false || stripos($label, 'Ragu') !== false) {
                    $liveCampusAggregates[$cName]['schools'][$sch]['warm']++;
                } elseif (strcasecmp($label, 'Tidak Merespon') === 0) {
                    $liveCampusAggregates[$cName]['schools'][$sch]['noResp']++;
                } else {
                    $liveCampusAggregates[$cName]['schools'][$sch]['fresh']++;
                }
            }

            // Reasons
            $r = trim((string) $lead->rejection_reason);
            if ($r !== '' && $r !== '-') {
                $liveCampusAggregates[$cName]['reasons'][$r] = ($liveCampusAggregates[$cName]['reasons'][$r] ?? 0) + 1;
            }
        }

        // 5. Load base historical benchmark campuses
        $baseCampuses = $this->getBaseBenchmarkCampuses();

        // 6. Merge live database data into campuses
        $combinedCampuses = [];
        $seenCampusNames = [];

        foreach ($baseCampuses as $base) {
            $cName = strtoupper(trim((string) $base['kampus']));
            $seenCampusNames[$cName] = true;

            if (isset($liveCampusAggregates[$cName])) {
                $live = $liveCampusAggregates[$cName];
                // Augment stats
                $base['total'] += $live['total'];
                $base['assigned'] += $live['assigned'];
                $base['unassigned'] += $live['unassigned'];
                $base['fresh'] += $live['fresh'];
                $base['freshAssigned'] += $live['freshAssigned'];
                $base['freshUnassigned'] += $live['freshUnassigned'];
                $base['closing'] += $live['closing'];
                $base['minat'] += $live['minat'];
                $base['ragu'] += $live['ragu'];
                $base['menolak'] += $live['menolak'];
                $base['noResp'] += $live['noResp'];
                $base['t1'] += $live['t1'];
                $base['t2'] += $live['t2'];
                $base['t3'] += $live['t3'];

                // Merge events (prepend live events)
                $mergedEvents = array_values($live['events']);
                $liveEventCodes = array_column($mergedEvents, 'code');
                foreach ($base['events'] as $bev) {
                    if (!in_array($bev['code'], $liveEventCodes, true)) {
                        $mergedEvents[] = $bev;
                    }
                }
                $base['events'] = $mergedEvents;

                // Merge agents
                $existingAgents = [];
                foreach ($base['agents'] as $ag) {
                    $existingAgents[$ag['name']] = $ag;
                }
                foreach ($live['agents'] as $agName => $agData) {
                    if (isset($existingAgents[$agName])) {
                        $existingAgents[$agName]['total'] += $agData['total'];
                        $existingAgents[$agName]['fresh'] += $agData['fresh'];
                        $existingAgents[$agName]['noResp'] += $agData['noResp'];
                        $existingAgents[$agName]['minat'] += $agData['minat'];
                        $existingAgents[$agName]['ragu'] += $agData['ragu'];
                        $existingAgents[$agName]['menolak'] += $agData['menolak'];
                        $existingAgents[$agName]['closing'] += $agData['closing'];
                    } else {
                        $existingAgents[$agName] = $agData;
                    }
                }
                $base['agents'] = array_values($existingAgents);

                // Merge reasons
                $reasonMap = [];
                foreach ($base['reasons'] as $rs) {
                    $reasonMap[$rs['reason']] = $rs['count'];
                }
                foreach ($live['reasons'] as $rText => $rCount) {
                    $reasonMap[$rText] = ($reasonMap[$rText] ?? 0) + $rCount;
                }
                $mergedReasons = [];
                foreach ($reasonMap as $rText => $rCount) {
                    $mergedReasons[] = ['reason' => $rText, 'count' => $rCount];
                }
                usort($mergedReasons, fn ($a, $b) => $b['count'] <=> $a['count']);
                $base['reasons'] = $mergedReasons;

                // Merge top schools
                $schoolMap = [];
                foreach ($base['topSchools'] as $sc) {
                    $schoolMap[$sc['name']] = $sc;
                }
                foreach ($live['schools'] as $scName => $scData) {
                    if (isset($schoolMap[$scName])) {
                        $schoolMap[$scName]['total'] += $scData['total'];
                        $schoolMap[$scName]['closing'] += $scData['closing'];
                        $schoolMap[$scName]['warm'] += $scData['warm'];
                        $schoolMap[$scName]['fresh'] += $scData['fresh'];
                        $schoolMap[$scName]['noResp'] += $scData['noResp'];
                    } else {
                        $schoolMap[$scName] = $scData;
                    }
                }
                $mergedSchools = array_values($schoolMap);
                usort($mergedSchools, fn ($a, $b) => $b['total'] <=> $a['total']);
                $base['topSchools'] = array_slice($mergedSchools, 0, 10);

                // Recompute average score
                $safeTot = max(1, $base['total']);
                $base['avgScore'] = round(
                    (($base['closing'] * 100) + (($base['minat'] + $base['ragu']) * 50) + ($base['fresh'] * 15) + ($base['noResp'] * 5)) / $safeTot,
                    2
                );
            }

            $combinedCampuses[] = $base;
        }

        // Add campuses that only exist in live leads (e.g. CYBER UNIVERSITY, KRAMAT 98, etc.)
        foreach ($liveCampusAggregates as $cName => $live) {
            if (!isset($seenCampusNames[$cName])) {
                $group = 'UBSI';
                if (stripos($cName, 'CYBER') !== false) {
                    $group = 'CYBER';
                } elseif (stripos($cName, 'UNM') !== false) {
                    $group = 'UNM';
                }

                $topSchools = array_values($live['schools']);
                usort($topSchools, fn ($a, $b) => $b['total'] <=> $a['total']);
                $topSchools = array_slice($topSchools, 0, 10);

                $reasons = [];
                foreach ($live['reasons'] as $rText => $rCount) {
                    $reasons[] = ['reason' => $rText, 'count' => $rCount];
                }
                usort($reasons, fn ($a, $b) => $b['count'] <=> $a['count']);

                $safeTot = max(1, $live['total']);
                $avgScore = round(
                    (($live['closing'] * 100) + (($live['minat'] + $live['ragu']) * 50) + ($live['fresh'] * 15) + ($live['noResp'] * 5)) / $safeTot,
                    2
                );

                $combinedCampuses[] = [
                    'no' => count($combinedCampuses) + 1,
                    'kampus' => $cName,
                    'group' => $group,
                    'total' => $live['total'],
                    'assigned' => $live['assigned'],
                    'unassigned' => $live['unassigned'],
                    'fresh' => $live['fresh'],
                    'freshAssigned' => $live['freshAssigned'],
                    'freshUnassigned' => $live['freshUnassigned'],
                    'noResp' => $live['noResp'],
                    'minat' => $live['minat'],
                    'ragu' => $live['ragu'],
                    'menolak' => $live['menolak'],
                    'closing' => $live['closing'],
                    't2Unlabeled' => 0,
                    'anomUnassigned' => 0,
                    't1' => $live['t1'],
                    't2' => $live['t2'],
                    't3' => $live['t3'],
                    'avgScore' => $avgScore,
                    'fuCount' => $live['total'] - $live['fresh'],
                    'logCount' => count($live['events']),
                    'batchCount' => max(1, count($live['events'])),
                    'schoolCount' => count($live['schools']),
                    'missingSchool' => 0,
                    'events' => array_values($live['events']),
                    'agents' => array_values($live['agents']),
                    'topSchools' => $topSchools,
                    'reasons' => $reasons,
                ];
                $seenCampusNames[$cName] = true;
            }
        }

        // Also ensure any remaining campuses from 33 DB campuses are available
        foreach ($dbCampuses as $cName => $info) {
            if (!isset($seenCampusNames[$cName])) {
                $group = 'UBSI';
                if (stripos($cName, 'CYBER') !== false) {
                    $group = 'CYBER';
                } elseif (stripos($cName, 'UNM') !== false) {
                    $group = 'UNM';
                }
                $combinedCampuses[] = [
                    'no' => count($combinedCampuses) + 1,
                    'kampus' => $cName,
                    'group' => $group,
                    'total' => 0,
                    'assigned' => 0,
                    'unassigned' => 0,
                    'fresh' => 0,
                    'freshAssigned' => 0,
                    'freshUnassigned' => 0,
                    'noResp' => 0,
                    'minat' => 0,
                    'ragu' => 0,
                    'menolak' => 0,
                    'closing' => 0,
                    't2Unlabeled' => 0,
                    'anomUnassigned' => 0,
                    't1' => 0,
                    't2' => 0,
                    't3' => 0,
                    'avgScore' => 0.0,
                    'fuCount' => 0,
                    'logCount' => 0,
                    'batchCount' => 0,
                    'schoolCount' => 0,
                    'missingSchool' => 0,
                    'events' => [],
                    'agents' => [],
                    'topSchools' => [],
                    'reasons' => [],
                ];
                $seenCampusNames[$cName] = true;
            }
        }

        // Sort campuses: high total leads first, then by name
        usort($combinedCampuses, function ($a, $b) {
            if ($b['total'] !== $a['total']) {
                return $b['total'] <=> $a['total'];
            }
            return strcmp($a['kampus'], $b['kampus']);
        });

        // Reassign clean 'no' index
        foreach ($combinedCampuses as $idx => &$camp) {
            $camp['no'] = $idx + 1;
        }
        unset($camp);

        // 7. Extract global agents list
        $globalAgents = [];
        foreach ($combinedCampuses as $c) {
            foreach ($c['agents'] as $ag) {
                if ($ag['name'] === 'Belum diassign') {
                    continue;
                }
                $safeTot = max(1, $ag['total']);
                $convPct = round(($ag['closing'] / $safeTot) * 100, 1);
                $warm = $ag['minat'] + $ag['ragu'] + ($ag['t2Unlabeled'] ?? 0);
                $globalAgents[] = [
                    'name' => $ag['name'],
                    'kampus' => $c['kampus'],
                    'total' => $ag['total'],
                    'fresh' => $ag['fresh'],
                    'noResp' => $ag['noResp'],
                    'warm' => $warm,
                    'minat' => $ag['minat'],
                    'ragu' => $ag['ragu'],
                    'menolak' => $ag['menolak'],
                    'closing' => $ag['closing'],
                    'convPct' => $convPct,
                ];
            }
        }
        usort($globalAgents, function ($a, $b) {
            if ($b['closing'] !== $a['closing']) {
                return $b['closing'] <=> $a['closing'];
            }
            return $b['total'] <=> $a['total'];
        });

        // 8. Calculate Macro Summaries
        $macro = [
            'totalLeads' => array_sum(array_column($combinedCampuses, 'total')),
            'totalAssigned' => array_sum(array_column($combinedCampuses, 'assigned')),
            'totalUnassigned' => array_sum(array_column($combinedCampuses, 'unassigned')),
            'totalFresh' => array_sum(array_column($combinedCampuses, 'fresh')),
            'totalClosing' => array_sum(array_column($combinedCampuses, 'closing')),
            'totalMinat' => array_sum(array_column($combinedCampuses, 'minat')),
            'totalRagu' => array_sum(array_column($combinedCampuses, 'ragu')),
            'totalMenolak' => array_sum(array_column($combinedCampuses, 'menolak')),
            'totalNoResp' => array_sum(array_column($combinedCampuses, 'noResp')),
            'totalCampuses' => count($combinedCampuses),
            'totalEventsWithLeads' => 0,
        ];
        foreach ($combinedCampuses as $c) {
            $macro['totalEventsWithLeads'] += count($c['events']);
        }
        $safeMacroTot = max(1, $macro['totalLeads']);
        $macro['overallClosingPct'] = round(($macro['totalClosing'] / $safeMacroTot) * 100, 1);
        $macro['overallPositivePct'] = round((($macro['totalClosing'] + $macro['totalMinat']) / $safeMacroTot) * 100, 1);

        // 9. Scope data if limited to single campus
        $visibleCampuses = $combinedCampuses;
        $visibleAgents = $globalAgents;
        $matchedCampus = null;

        if (!$canViewAllCampuses) {
            $userTarget = strtoupper(trim($userCampusFullName));
            foreach ($combinedCampuses as $c) {
                if (strtoupper(trim($c['kampus'])) === $userTarget || stripos($c['kampus'], $userTarget) !== false) {
                    $matchedCampus = $c;
                    break;
                }
            }
            if ($matchedCampus === null && !empty($combinedCampuses)) {
                $matchedCampus = $combinedCampuses[0];
            }
            $visibleCampuses = $matchedCampus ? [$matchedCampus] : [];
            $visibleAgents = array_values(array_filter($globalAgents, function ($ag) use ($matchedCampus) {
                return $matchedCampus && strtoupper(trim($ag['kampus'])) === strtoupper(trim($matchedCampus['kampus']));
            }));
        } elseif ($requestedCampusParam !== '') {
            $targetUpper = strtoupper(trim($requestedCampusParam));
            foreach ($combinedCampuses as $c) {
                if (strtoupper(trim($c['kampus'])) === $targetUpper || (string) $c['no'] === $requestedCampusParam) {
                    $matchedCampus = $c;
                    break;
                }
            }
        }

        return [
            'success' => true,
            'timestamp' => $timestamp,
            'macro' => $macro,
            'campuses' => $visibleCampuses,
            'allCampuses' => $combinedCampuses,
            'agents' => $visibleAgents,
            'recentLogs' => $recentLogs,
            'canViewAllCampuses' => $canViewAllCampuses,
            'matchedCampus' => $matchedCampus,
        ];
    }

    private function timeElapsedString(string $datetime): string
    {
        try {
            $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
            $ago = new \DateTimeImmutable($datetime, new \DateTimeZone('Asia/Jakarta'));
            $diff = $now->diff($ago);

            if ($diff->d > 0) {
                return $diff->d . ' hari lalu';
            }
            if ($diff->h > 0) {
                return $diff->h . ' jam lalu';
            }
            if ($diff->i > 0) {
                return $diff->i . ' mnt lalu';
            }
            return 'baru saja';
        } catch (Throwable $e) {
            return $datetime;
        }
    }

    /**
     * Baseline historical benchmark data of regional campuses
     */
    private function getBaseBenchmarkCampuses(): array
    {
        return [
            [
                'no' => 1,
                'kampus' => 'UBSI KAMPUS YOGYAKARTA',
                'group' => 'UBSI',
                'total' => 2056,
                'assigned' => 2056,
                'unassigned' => 0,
                'fresh' => 513,
                'freshAssigned' => 513,
                'freshUnassigned' => 0,
                'noResp' => 1384,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 139,
                'closing' => 15,
                't2Unlabeled' => 5,
                'anomUnassigned' => 0,
                't1' => 1533,
                't2' => 480,
                't3' => 43,
                'avgScore' => 14.66,
                'fuCount' => 1543,
                'logCount' => 2056,
                'batchCount' => 13,
                'schoolCount' => 324,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-2855', 'name' => 'Klaten Edu Expo & Job Fair 2025', 'category' => 'Edufair', 'date' => '16 Dec 2025', 'location' => 'GRAHA BUNG KARNO KLATEN', 'total' => 1189, 'unassigned' => 0, 'fresh' => 513, 'noResp' => 630, 'minat' => 0, 'ragu' => 0, 'menolak' => 45, 'closing' => 0, 't2Unlabeled' => 1],
                    ['code' => 'EVT-2273', 'name' => 'Edufair SMKN 1 Panjatan', 'category' => 'Edufair', 'date' => '22 Apr 2026', 'location' => 'SMKN 1 Panjatan', 'total' => 150, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 142, 'minat' => 0, 'ragu' => 0, 'menolak' => 8, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2435', 'name' => 'Presentasi SMAN 1 Imogiri', 'category' => 'Akademik', 'date' => '10 Oct 2025', 'location' => 'Auditorium Utama', 'total' => 124, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 108, 'minat' => 0, 'ragu' => 0, 'menolak' => 16, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2283', 'name' => 'Edufair SMAN 1 Jetis', 'category' => 'Edufair', 'date' => '10 Feb 2026', 'location' => 'SMAN 1 Jetis', 'total' => 85, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 78, 'minat' => 0, 'ragu' => 0, 'menolak' => 6, 'closing' => 0, 't2Unlabeled' => 1],
                    ['code' => 'EVT-2274', 'name' => 'Edufair SMKN 1 Sewon', 'category' => 'Edufair', 'date' => '22 Apr 2026', 'location' => 'SMKN 1 Sewon', 'total' => 82, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 61, 'minat' => 0, 'ragu' => 0, 'menolak' => 18, 'closing' => 1, 't2Unlabeled' => 2],
                    ['code' => 'EVT-2430', 'name' => 'Edufair SMKN 1 Nanggulan', 'category' => 'Edufair', 'date' => '21 May 2026', 'location' => 'SMKN 1 Nanggulan', 'total' => 74, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 72, 'minat' => 0, 'ragu' => 0, 'menolak' => 2, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2428', 'name' => 'DATA APLIKASI PMB 2026-2027', 'category' => 'Aplikasi PMB', 'date' => '01 May 2026', 'location' => 'APLIKASI PMB', 'total' => 73, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 54, 'minat' => 0, 'ragu' => 0, 'menolak' => 6, 'closing' => 13, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2275', 'name' => 'Edufair SMKN 1 Pengasih', 'category' => 'Edufair', 'date' => '23 Apr 2026', 'location' => 'SMKN 1 Pengasih', 'total' => 67, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 61, 'minat' => 0, 'ragu' => 0, 'menolak' => 5, 'closing' => 1, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2276', 'name' => 'Edufair SMKN 4 Yogyakarta', 'category' => 'Edufair', 'date' => '29 Apr 2026', 'location' => 'SMKN 4 Yogyakarta', 'total' => 67, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 56, 'minat' => 0, 'ragu' => 0, 'menolak' => 11, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2282', 'name' => 'Edufair MAN 1 Kulonprogo', 'category' => 'Edufair', 'date' => '10 Feb 2026', 'location' => 'MAN 1 Kulonprogo', 'total' => 63, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 51, 'minat' => 0, 'ragu' => 0, 'menolak' => 12, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Admin UBSI Yogyakarta', 'total' => 73, 'fresh' => 0, 'noResp' => 54, 'minat' => 0, 'ragu' => 0, 'menolak' => 6, 'closing' => 13, 't2Unlabeled' => 0],
                    ['name' => 'luksti nandiya sari', 'total' => 92, 'fresh' => 23, 'noResp' => 64, 'minat' => 0, 'ragu' => 0, 'menolak' => 5, 'closing' => 0, 't2Unlabeled' => 0],
                    ['name' => 'adellima putri', 'total' => 69, 'fresh' => 21, 'noResp' => 45, 'minat' => 0, 'ragu' => 0, 'menolak' => 3, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'topSchools' => [
                    ['name' => 'SMKN 1 Sewon', 'total' => 82, 'closing' => 1, 'warm' => 2, 'fresh' => 0, 'noResp' => 61],
                    ['name' => 'SMKN 1 Pengasih', 'total' => 67, 'closing' => 1, 'warm' => 0, 'fresh' => 0, 'noResp' => 61],
                    ['name' => 'SMKN 1 Panjatan', 'total' => 150, 'closing' => 0, 'warm' => 0, 'fresh' => 0, 'noResp' => 142],
                ],
                'reasons' => [
                    ['reason' => 'Sudah daftar kampus lain', 'count' => 11],
                    ['reason' => 'Nomor tidak terdaftar di WhatsApp', 'count' => 9],
                    ['reason' => 'Memilih kerja', 'count' => 4],
                ],
            ],
            [
                'no' => 2,
                'kampus' => 'UBSI KAMPUS JATIWARINGIN',
                'group' => 'UBSI',
                'total' => 1203,
                'assigned' => 1203,
                'unassigned' => 0,
                'fresh' => 0,
                'freshAssigned' => 0,
                'freshUnassigned' => 0,
                'noResp' => 1156,
                'minat' => 0,
                'ragu' => 12,
                'menolak' => 33,
                'closing' => 2,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 1201,
                't2' => 0,
                't3' => 2,
                'avgScore' => 20.07,
                'fuCount' => 1203,
                'logCount' => 902,
                'batchCount' => 8,
                'schoolCount' => 6,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-2350', 'name' => 'Edufair SMAN 7', 'category' => 'Edufair', 'date' => '22 Jan 2026', 'location' => 'Lapangan Sekolah', 'total' => 306, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 293, 'minat' => 0, 'ragu' => 0, 'menolak' => 11, 'closing' => 2, 't2Unlabeled' => 0],
                    ['code' => 'EVT-1313', 'name' => 'Edu fair SMAN 16 Bekasi', 'category' => 'Edufair', 'date' => '16 Dec 2025', 'location' => 'SMAN 16 Bekasi', 'total' => 289, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 268, 'minat' => 0, 'ragu' => 5, 'menolak' => 16, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-977', 'name' => 'Edu Fair SMA Hutama Bekasi', 'category' => 'Edufair', 'date' => '14 Nov 2025', 'location' => 'Lapangan Sekolah', 'total' => 222, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 221, 'minat' => 0, 'ragu' => 0, 'menolak' => 1, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-1308', 'name' => 'Edu Fair SMA Tulus Bhakti', 'category' => 'Edufair', 'date' => '18 Nov 2025', 'location' => 'SMA Tulus Bhakti', 'total' => 216, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 207, 'minat' => 0, 'ragu' => 6, 'menolak' => 3, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Nani Agustina', 'total' => 483, 'fresh' => 0, 'noResp' => 459, 'minat' => 0, 'ragu' => 8, 'menolak' => 16, 'closing' => 0, 't2Unlabeled' => 0],
                    ['name' => 'Arwircha genvil', 'total' => 464, 'fresh' => 0, 'noResp' => 448, 'minat' => 0, 'ragu' => 2, 'menolak' => 13, 'closing' => 1, 't2Unlabeled' => 0],
                ],
                'topSchools' => [
                    ['name' => 'SMAN 7 BEKASI', 'total' => 306, 'closing' => 2, 'warm' => 0, 'fresh' => 0, 'noResp' => 293],
                    ['name' => 'SMAN 16 BEKASI', 'total' => 289, 'closing' => 0, 'warm' => 5, 'fresh' => 0, 'noResp' => 268],
                ],
                'reasons' => [
                    ['reason' => 'Sudah dapat kampus lain', 'count' => 8],
                ],
            ],
            [
                'no' => 3,
                'kampus' => 'UBSI KAMPUS TANGERANG',
                'group' => 'UBSI',
                'total' => 1081,
                'assigned' => 79,
                'unassigned' => 1002,
                'fresh' => 1067,
                'freshAssigned' => 65,
                'freshUnassigned' => 1002,
                'noResp' => 7,
                'minat' => 1,
                'ragu' => 3,
                'menolak' => 3,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 1081,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.35,
                'fuCount' => 15,
                'logCount' => 0,
                'batchCount' => 5,
                'schoolCount' => 5,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-976', 'name' => 'Edufair SMAN 4 Kabupaten Tangerang', 'category' => 'Edufair', 'date' => '27 Nov 2025', 'location' => 'Halaman Sekolah', 'total' => 296, 'unassigned' => 296, 'fresh' => 296, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-975', 'name' => 'Edufair SMAN 4 Kota Tangerang', 'category' => 'Edufair', 'date' => '20 Nov 2025', 'location' => 'Kelas XII dan Halaman Sekolah', 'total' => 287, 'unassigned' => 287, 'fresh' => 287, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-974', 'name' => 'Edufair MAN 2 Kota Tangerang', 'category' => 'Edufair', 'date' => '19 Nov 2025', 'location' => 'Kelas XII dan Halaman Sekolah', 'total' => 245, 'unassigned' => 245, 'fresh' => 245, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Kampus Tangerang', 'total' => 79, 'fresh' => 65, 'noResp' => 7, 'minat' => 1, 'ragu' => 3, 'menolak' => 3, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'topSchools' => [
                    ['name' => 'SMAN 4 Kabupaten Tangerang', 'total' => 296, 'closing' => 0, 'warm' => 0, 'fresh' => 296, 'noResp' => 0],
                ],
                'reasons' => [],
            ],
            [
                'no' => 4,
                'kampus' => 'UBSI KAMPUS FATMAWATI',
                'group' => 'UBSI',
                'total' => 593,
                'assigned' => 37,
                'unassigned' => 556,
                'fresh' => 510,
                'freshAssigned' => 0,
                'freshUnassigned' => 510,
                'noResp' => 38,
                'minat' => 27,
                'ragu' => 6,
                'menolak' => 3,
                'closing' => 9,
                't2Unlabeled' => 0,
                'anomUnassigned' => 46,
                't1' => 581,
                't2' => 1,
                't3' => 11,
                'avgScore' => 5.89,
                'fuCount' => 83,
                'logCount' => 0,
                'batchCount' => 4,
                'schoolCount' => 0,
                'missingSchool' => 593,
                'events' => [
                    ['code' => 'EVT-3433', 'name' => 'Edu Fair SMAN 97 Jakarta', 'category' => 'Edufair', 'date' => '23 Oct 2026', 'location' => 'Halaman Sekolah', 'total' => 290, 'unassigned' => 290, 'fresh' => 290, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-962', 'name' => 'Edufair SMKN 41 Jakarta', 'category' => 'Edufair', 'date' => '13 May 2026', 'location' => 'Halaman Sekolah', 'total' => 245, 'unassigned' => 244, 'fresh' => 207, 'noResp' => 18, 'minat' => 12, 'ragu' => 3, 'menolak' => 2, 'closing' => 3, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'BSI Fatmawati', 'total' => 37, 'fresh' => 0, 'noResp' => 20, 'minat' => 9, 'ragu' => 1, 'menolak' => 1, 'closing' => 6, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 5,
                'kampus' => 'UBSI KAMPUS TASIKMALAYA',
                'group' => 'UBSI',
                'total' => 562,
                'assigned' => 78,
                'unassigned' => 484,
                'fresh' => 484,
                'freshAssigned' => 0,
                'freshUnassigned' => 484,
                'noResp' => 20,
                'minat' => 10,
                'ragu' => 20,
                'menolak' => 5,
                'closing' => 23,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 538,
                't2' => 0,
                't3' => 24,
                'avgScore' => 7.78,
                'fuCount' => 78,
                'logCount' => 9,
                'batchCount' => 2,
                'schoolCount' => 34,
                'missingSchool' => 7,
                'events' => [
                    ['code' => 'EVT-952', 'name' => 'Job Edu Fair & Pelepasan SMKN 2 Ciamis', 'category' => 'Edufair', 'date' => '19 May 2026', 'location' => 'Auditorium', 'total' => 484, 'unassigned' => 484, 'fresh' => 484, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                    ['code' => 'EVT-2148', 'name' => 'Incer Fest 2026 UBSI Tasikmalaya', 'category' => 'Non Akademik', 'date' => '02 Mar 2026', 'location' => 'Aula UBSI', 'total' => 78, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 20, 'minat' => 10, 'ragu' => 20, 'menolak' => 5, 'closing' => 23, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Sri Mulyani', 'total' => 45, 'fresh' => 0, 'noResp' => 19, 'minat' => 1, 'ragu' => 6, 'menolak' => 5, 'closing' => 14, 't2Unlabeled' => 0],
                ],
                'topSchools' => [
                    ['name' => 'Tasikmalaya', 'total' => 23, 'closing' => 3, 'warm' => 6, 'fresh' => 0, 'noResp' => 12],
                ],
                'reasons' => [],
            ],
            [
                'no' => 6,
                'kampus' => 'UBSI KAMPUS CIKARANG',
                'group' => 'UBSI',
                'total' => 377,
                'assigned' => 377,
                'unassigned' => 0,
                'fresh' => 3,
                'freshAssigned' => 3,
                'freshUnassigned' => 0,
                'noResp' => 95,
                'minat' => 5,
                'ragu' => 3,
                'menolak' => 12,
                'closing' => 259,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 116,
                't2' => 2,
                't3' => 259,
                'avgScore' => 75.07,
                'fuCount' => 374,
                'logCount' => 124,
                'batchCount' => 4,
                'schoolCount' => 130,
                'missingSchool' => 4,
                'events' => [
                    ['code' => 'EVT-2440', 'name' => 'APLIKASI PMB CIKARANG', 'category' => 'Aplikasi PMB', 'date' => '12 Jun 2026', 'location' => 'UBSI CIKARANG', 'total' => 377, 'unassigned' => 0, 'fresh' => 3, 'noResp' => 95, 'minat' => 5, 'ragu' => 3, 'menolak' => 12, 'closing' => 259, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Indah Novitasari', 'total' => 166, 'fresh' => 0, 'noResp' => 21, 'minat' => 0, 'ragu' => 1, 'menolak' => 5, 'closing' => 139, 't2Unlabeled' => 0],
                    ['name' => 'Salsabila Qibtia', 'total' => 140, 'fresh' => 1, 'noResp' => 18, 'minat' => 3, 'ragu' => 0, 'menolak' => 2, 'closing' => 116, 't2Unlabeled' => 0],
                ],
                'topSchools' => [
                    ['name' => 'SMKN 1 CIKARANG SELATAN', 'total' => 37, 'closing' => 37, 'warm' => 0, 'fresh' => 0, 'noResp' => 0],
                    ['name' => 'SMKN 1 CIBARUSAH', 'total' => 17, 'closing' => 17, 'warm' => 0, 'fresh' => 0, 'noResp' => 0],
                ],
                'reasons' => [],
            ],
            [
                'no' => 7,
                'kampus' => 'UBSI KAMPUS CIKAMPEK',
                'group' => 'UBSI',
                'total' => 270,
                'assigned' => 270,
                'unassigned' => 0,
                'fresh' => 0,
                'freshAssigned' => 0,
                'freshUnassigned' => 0,
                'noResp' => 28,
                'minat' => 87,
                'ragu' => 0,
                'menolak' => 1,
                'closing' => 154,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 85,
                't2' => 31,
                't3' => 154,
                'avgScore' => 76.85,
                'fuCount' => 270,
                'logCount' => 270,
                'batchCount' => 16,
                'schoolCount' => 64,
                'missingSchool' => 116,
                'events' => [
                    ['code' => 'EVT-931', 'name' => 'Database Aplikasi PMB UBSI Cikampek', 'category' => 'Aplikasi PMB', 'date' => '15 Oct 2025', 'location' => 'UBSI Cikampek', 'total' => 270, 'unassigned' => 0, 'fresh' => 0, 'noResp' => 28, 'minat' => 87, 'ragu' => 0, 'menolak' => 1, 'closing' => 154, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Ironia Lestari', 'total' => 81, 'fresh' => 0, 'noResp' => 14, 'minat' => 20, 'ragu' => 0, 'menolak' => 0, 'closing' => 47, 't2Unlabeled' => 0],
                    ['name' => 'Ani Nuraini', 'total' => 73, 'fresh' => 0, 'noResp' => 9, 'minat' => 17, 'ragu' => 0, 'menolak' => 0, 'closing' => 47, 't2Unlabeled' => 0],
                ],
                'topSchools' => [
                    ['name' => 'SMKN 1 JATISARI', 'total' => 22, 'closing' => 22, 'warm' => 0, 'fresh' => 0, 'noResp' => 0],
                ],
                'reasons' => [
                    ['reason' => 'Memilih kampus lain', 'count' => 1],
                ],
            ],
            [
                'no' => 8,
                'kampus' => 'UBSI KAMPUS CIBITUNG',
                'group' => 'UBSI',
                'total' => 470,
                'assigned' => 22,
                'unassigned' => 448,
                'fresh' => 447,
                'freshAssigned' => 0,
                'freshUnassigned' => 447,
                'noResp' => 0,
                'minat' => 9,
                'ragu' => 3,
                'menolak' => 1,
                'closing' => 10,
                't2Unlabeled' => 0,
                'anomUnassigned' => 1,
                't1' => 447,
                't2' => 12,
                't3' => 11,
                'avgScore' => 3.54,
                'fuCount' => 23,
                'logCount' => 7,
                'batchCount' => 8,
                'schoolCount' => 4,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-959', 'name' => 'Support Event SMKN 15 Kota Bekasi', 'category' => 'Edufair', 'date' => '11 May 2026', 'location' => 'Bekasi', 'total' => 308, 'unassigned' => 308, 'fresh' => 308, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Dian Ardiansyah', 'total' => 22, 'fresh' => 0, 'noResp' => 0, 'minat' => 9, 'ragu' => 3, 'menolak' => 1, 'closing' => 9, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 9,
                'kampus' => 'UNM KAMPUS RAWAMANGUN',
                'group' => 'UNM',
                'total' => 470,
                'assigned' => 470,
                'unassigned' => 0,
                'fresh' => 416,
                'freshAssigned' => 416,
                'freshUnassigned' => 0,
                'noResp' => 33,
                'minat' => 10,
                'ragu' => 3,
                'menolak' => 6,
                'closing' => 2,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 460,
                't2' => 3,
                't3' => 7,
                'avgScore' => 3.41,
                'fuCount' => 54,
                'logCount' => 102,
                'batchCount' => 4,
                'schoolCount' => 54,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-2383', 'name' => 'Edufair SMK N 40 jakarta', 'category' => 'Edufair', 'date' => '07 May 2026', 'location' => 'Jakarta', 'total' => 341, 'unassigned' => 0, 'fresh' => 319, 'noResp' => 16, 'minat' => 0, 'ragu' => 1, 'menolak' => 5, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Galuh Tiasari Estiningtyas', 'total' => 171, 'fresh' => 148, 'noResp' => 15, 'minat' => 6, 'ragu' => 0, 'menolak' => 1, 'closing' => 1, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 10,
                'kampus' => 'UNM KAMPUS MARGONDA',
                'group' => 'UNM',
                'total' => 309,
                'assigned' => 0,
                'unassigned' => 309,
                'fresh' => 309,
                'freshAssigned' => 0,
                'freshUnassigned' => 309,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 309,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 1,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-3193', 'name' => 'Edu Fair SMAN 1 Depok', 'category' => 'Edufair', 'date' => '15 Sep 2026', 'location' => 'SMAN 1 Depok', 'total' => 309, 'unassigned' => 309, 'fresh' => 309, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 11,
                'kampus' => 'UBSI KAMPUS TEGAL',
                'group' => 'UBSI',
                'total' => 204,
                'assigned' => 0,
                'unassigned' => 204,
                'fresh' => 204,
                'freshAssigned' => 0,
                'freshUnassigned' => 204,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 204,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 1,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-2232', 'name' => 'Tegal Eduucation Fair 2026', 'category' => 'Edufair', 'date' => '01 May 2026', 'location' => 'Kota Tegal', 'total' => 204, 'unassigned' => 204, 'fresh' => 204, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 12,
                'kampus' => 'UBSI KAMPUS BOGOR',
                'group' => 'UBSI',
                'total' => 188,
                'assigned' => 0,
                'unassigned' => 188,
                'fresh' => 188,
                'freshAssigned' => 0,
                'freshUnassigned' => 188,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 188,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 2,
                'schoolCount' => 3,
                'missingSchool' => 127,
                'events' => [
                    ['code' => 'EVT-3258', 'name' => 'Edufair SMAN 4 Kota Bogor', 'category' => 'Edufair', 'date' => '19 Oct 2026', 'location' => 'Bogor', 'total' => 127, 'unassigned' => 127, 'fresh' => 127, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 13,
                'kampus' => 'UBSI KAMPUS KALIABANG',
                'group' => 'UBSI',
                'total' => 154,
                'assigned' => 154,
                'unassigned' => 0,
                'fresh' => 126,
                'freshAssigned' => 126,
                'freshUnassigned' => 0,
                'noResp' => 13,
                'minat' => 5,
                'ragu' => 2,
                'menolak' => 1,
                'closing' => 7,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 139,
                't2' => 8,
                't3' => 7,
                'avgScore' => 8.77,
                'fuCount' => 28,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 1,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-978', 'name' => 'Data CRM Gel 3', 'category' => 'Aplikasi PMB', 'date' => '04 Jun 2026', 'location' => 'Auditorium', 'total' => 154, 'unassigned' => 0, 'fresh' => 126, 'noResp' => 13, 'minat' => 5, 'ragu' => 2, 'menolak' => 1, 'closing' => 7, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'nasiela Azira', 'total' => 152, 'fresh' => 126, 'noResp' => 12, 'minat' => 5, 'ragu' => 2, 'menolak' => 1, 'closing' => 6, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 14,
                'kampus' => 'UBSI KAMPUS BEKASI CUT MUTIA',
                'group' => 'UBSI',
                'total' => 140,
                'assigned' => 0,
                'unassigned' => 140,
                'fresh' => 140,
                'freshAssigned' => 0,
                'freshUnassigned' => 140,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 140,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 0,
                'missingSchool' => 140,
                'events' => [
                    ['code' => 'EVT-3231', 'name' => 'Edufair SMA Ananda Bekasi', 'category' => 'Edufair', 'date' => '25 Sep 2026', 'location' => 'SMK Ananda Bekasi', 'total' => 140, 'unassigned' => 140, 'fresh' => 140, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 15,
                'kampus' => 'UBSI KAMPUS SLIPI',
                'group' => 'UBSI',
                'total' => 140,
                'assigned' => 0,
                'unassigned' => 140,
                'fresh' => 140,
                'freshAssigned' => 0,
                'freshUnassigned' => 140,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 140,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 1,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-2915', 'name' => 'Open Booth CFD Jakarta Barat', 'category' => 'Open Booth (Mall/CFD/Public Area)', 'date' => '09 Aug 2026', 'location' => 'CFD Barat', 'total' => 140, 'unassigned' => 140, 'fresh' => 140, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 16,
                'kampus' => 'UBSI KAMPUS CENGKARENG',
                'group' => 'UBSI',
                'total' => 131,
                'assigned' => 6,
                'unassigned' => 125,
                'fresh' => 124,
                'freshAssigned' => 0,
                'freshUnassigned' => 124,
                'noResp' => 1,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 6,
                't2Unlabeled' => 0,
                'anomUnassigned' => 1,
                't1' => 124,
                't2' => 1,
                't3' => 6,
                'avgScore' => 4.73,
                'fuCount' => 7,
                'logCount' => 7,
                'batchCount' => 1,
                'schoolCount' => 0,
                'missingSchool' => 131,
                'events' => [
                    ['code' => 'EVT-2555', 'name' => 'FU Camaba Belum Bayar DU', 'category' => 'Aplikasi PMB', 'date' => '29 Jun 2026', 'location' => 'Administrasi', 'total' => 131, 'unassigned' => 125, 'fresh' => 124, 'noResp' => 1, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 6, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Yunita', 'total' => 6, 'fresh' => 0, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 6, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 17,
                'kampus' => 'UBSI KAMPUS SOLO',
                'group' => 'UBSI',
                'total' => 110,
                'assigned' => 35,
                'unassigned' => 75,
                'fresh' => 74,
                'freshAssigned' => 0,
                'freshUnassigned' => 74,
                'noResp' => 0,
                'minat' => 3,
                'ragu' => 4,
                'menolak' => 2,
                'closing' => 27,
                't2Unlabeled' => 0,
                'anomUnassigned' => 1,
                't1' => 82,
                't2' => 1,
                't3' => 27,
                'avgScore' => 28.14,
                'fuCount' => 36,
                'logCount' => 13,
                'batchCount' => 2,
                'schoolCount' => 32,
                'missingSchool' => 47,
                'events' => [
                    ['code' => 'EVT-1683', 'name' => 'Live Tiktok PMB Solo', 'category' => 'Non Akademik', 'date' => '28 Aug 2025', 'location' => 'UBSI Solo', 'total' => 110, 'unassigned' => 75, 'fresh' => 74, 'noResp' => 0, 'minat' => 3, 'ragu' => 4, 'menolak' => 2, 'closing' => 27, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'ahmad fauzi', 'total' => 35, 'fresh' => 0, 'noResp' => 0, 'minat' => 3, 'ragu' => 4, 'menolak' => 2, 'closing' => 26, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 18,
                'kampus' => 'UBSI KAMPUS KARAWANG',
                'group' => 'UBSI',
                'total' => 87,
                'assigned' => 0,
                'unassigned' => 87,
                'fresh' => 87,
                'freshAssigned' => 0,
                'freshUnassigned' => 87,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 87,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 56,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-2377', 'name' => 'Bincang Kampus Bersama Orang Tua', 'category' => 'BKOT/NKBO/TWOT', 'date' => '23 May 2026', 'location' => 'Aula Karawang', 'total' => 87, 'unassigned' => 87, 'fresh' => 87, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 19,
                'kampus' => 'UBSI KAMPUS SUKABUMI',
                'group' => 'UBSI',
                'total' => 84,
                'assigned' => 84,
                'unassigned' => 0,
                'fresh' => 84,
                'freshAssigned' => 84,
                'freshUnassigned' => 0,
                'noResp' => 0,
                'minat' => 0,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 84,
                't2' => 0,
                't3' => 0,
                'avgScore' => 0.0,
                'fuCount' => 0,
                'logCount' => 0,
                'batchCount' => 3,
                'schoolCount' => 28,
                'missingSchool' => 0,
                'events' => [
                    ['code' => 'EVT-1811', 'name' => 'ASMILA BSI FLASH Sukabumi', 'category' => 'Non Akademik', 'date' => '19 Dec 2025', 'location' => 'Aula Sukabumi', 'total' => 60, 'unassigned' => 0, 'fresh' => 60, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Anisa Julia Utami', 'total' => 66, 'fresh' => 66, 'noResp' => 0, 'minat' => 0, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
            [
                'no' => 20,
                'kampus' => 'UBSI KAMPUS PONTIANAK',
                'group' => 'UBSI',
                'total' => 13,
                'assigned' => 1,
                'unassigned' => 12,
                'fresh' => 12,
                'freshAssigned' => 0,
                'freshUnassigned' => 12,
                'noResp' => 0,
                'minat' => 1,
                'ragu' => 0,
                'menolak' => 0,
                'closing' => 0,
                't2Unlabeled' => 0,
                'anomUnassigned' => 0,
                't1' => 13,
                't2' => 0,
                't3' => 0,
                'avgScore' => 4.23,
                'fuCount' => 1,
                'logCount' => 0,
                'batchCount' => 1,
                'schoolCount' => 0,
                'missingSchool' => 13,
                'events' => [
                    ['code' => 'EVT-950', 'name' => 'Open Booth Kaisar Sport Center', 'category' => 'Open Booth (Mall/CFD/Public Area)', 'date' => '23 May 2026', 'location' => 'Pontianak', 'total' => 13, 'unassigned' => 12, 'fresh' => 12, 'noResp' => 0, 'minat' => 1, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'agents' => [
                    ['name' => 'Eri Bayu Pratama', 'total' => 1, 'fresh' => 0, 'noResp' => 0, 'minat' => 1, 'ragu' => 0, 'menolak' => 0, 'closing' => 0, 't2Unlabeled' => 0],
                ],
                'topSchools' => [],
                'reasons' => [],
            ],
        ];
    }
}
