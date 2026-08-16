<?php

namespace App\Services\Simulation;

use App\Models\Confrontation;
use App\Models\Staff;
use App\Models\StaffEvent;

class ConfrontationService
{
    public function create(int $reporterStaffId, int $accusedStaffId, string $incidentType, ?string $description, bool $dbVerified = false): Confrontation
    {
        return Confrontation::create([
            'reporter_staff_id' => $reporterStaffId,
            'accused_staff_id' => $accusedStaffId,
            'incident_type' => $incidentType,
            'incident_description' => $description,
            'db_verified' => $dbVerified,
            'date' => Clock::date(),
            'happiness_impacts' => [],
        ]);
    }

    public function autoRespond(Confrontation $confrontation): void
    {
        $accused = $confrontation->accused;

        if ($confrontation->db_verified) {
            $confessChance = 0.3 + ($accused->honesty_score / 100) * 0.5;

            foreach ($accused->personalities->pluck('name')->all() as $name) {
                $confessChance += match ($name) {
                    'honest' => 0.15,
                    'rude' => 0.1,
                    'nerd' => 0.05,
                    'cliquey' => -0.15,
                    'opportunistic' => -0.2,
                    'creepy' => -0.1,
                    default => 0,
                };
            }

            $response = mt_rand(1, 100) / 100 <= min(0.95, max(0.05, $confessChance)) ? 'confessed' : 'bs';
        } else {
            $response = 'innocent';
        }

        $this->respond($confrontation, $response);
    }

    public function respond(Confrontation $confrontation, string $response): void
    {
        $accused = $confrontation->accused;

        $confrontation->staff_response = $response;
        $confrontation->save();

        $impacts = $confrontation->happiness_impacts ?? [];

        switch ($response) {
            case 'confessed':
                $this->apply($accused, -15, 'confession', $impacts);
                $confrontation->investigation_result = 'Accused confessed to the incident during investigation.';
                break;

            case 'innocent':
                $this->apply($accused, -5, 'accused_but_denied', $impacts);
                $confrontation->investigation_result = 'Accused denies involvement. Awaiting manager verdict.';
                break;

            case 'bs':
                $this->apply($accused, -8, 'accused_brushed_off', $impacts);
                $confrontation->investigation_result = 'Accused dismissed the claim and challenged the report.';
                break;
        }

        $confrontation->happiness_impacts = $impacts;
        $confrontation->save();
    }

    public function verdict(Confrontation $confrontation, string $verdict, ?float $penaltyAmount = null): void
    {
        $accused = $confrontation->accused;
        $reporter = $confrontation->reporter;
        $impacts = $confrontation->happiness_impacts ?? [];

        $confrontation->manager_verdict = $verdict;

        switch ($verdict) {
            case 'upheld':
                $this->apply($accused, -10, 'verdict_upheld', $impacts);
                $confrontation->investigation_result = 'Manager upheld the report. Accused disciplined.';
                break;

            case 'dismissed':
                $this->apply($reporter, -8, 'report_dismissed', $impacts);
                $confrontation->investigation_result = 'Manager dismissed the report for lack of evidence.';
                break;

            case 'penalized':
                $this->apply($accused, -12, 'verdict_penalized', $impacts);
                $this->apply($reporter, -4, 'report_partly_credited', $impacts);
                $confrontation->investigation_result = 'Manager issued a formal penalty to the accused.';
                break;
        }

        $confrontation->happiness_impacts = $impacts;
        $confrontation->save();

        if ($verdict === 'penalized' && $penaltyAmount > 0) {
            \App\Models\Penalty::create([
                'staff_id' => $accused->id,
                'type' => 'pay_dock',
                'reason' => 'Confrontation verdict: ' . $confrontation->incident_type,
                'amount_or_hours' => $penaltyAmount,
                'date' => Clock::date(),
                'issued_by' => null,
            ]);

            $accused->current_salary = max(0, $accused->current_salary - $penaltyAmount);
            $accused->warnings_count = $accused->warnings_count + 1;
            $accused->save();
        }
    }

    private function apply(Staff $staff, int $delta, string $eventType, array &$impacts): void
    {
        $before = $staff->happiness;
        $staff->happiness = max(0, min(100, $staff->happiness + $delta));
        $staff->save();

        StaffEvent::create([
            'staff_id' => $staff->id,
            'event_type' => $eventType,
            'severity' => 'moderate',
            'description' => 'Happiness impact from confrontation',
            'date' => Clock::date(),
            'happiness_change' => $delta,
        ]);

        $impacts[] = ['staff_id' => $staff->id, 'before' => $before, 'after' => $staff->happiness, 'delta' => $delta];
    }
}
