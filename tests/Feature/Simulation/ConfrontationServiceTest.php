<?php

namespace Tests\Feature\Simulation;

use App\Models\Penalty;
use App\Models\StaffEvent;
use App\Services\Simulation\ConfrontationService;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class ConfrontationServiceTest extends TestCase
{
    use CreatesSimFixtures;

    private function makeConfrontation(int $reporterId, int $accusedId, bool $verified = false)
    {
        return app(ConfrontationService::class)->create(
            $reporterId,
            $accusedId,
            'other',
            'Reported incident for testing',
            $verified
        );
    }

    public function test_unverified_confrontation_auto_responds_innocent(): void
    {
        $this->clubConfig();
        $accused = $this->makeStaff(['happiness' => 70]);
        $reporter = $this->makeStaff();

        $confrontation = $this->makeConfrontation($reporter->id, $accused->id, false);
        app(ConfrontationService::class)->autoRespond($confrontation);

        $this->assertSame('innocent', $confrontation->fresh()->staff_response);
        $this->assertSame(65, $accused->fresh()->happiness);
        $this->assertNotNull($confrontation->fresh()->investigation_result);
        $this->assertDatabaseHas('staff_events', ['staff_id' => $accused->id, 'event_type' => 'accused_but_denied']);
    }

    public function test_verified_confrontation_auto_responds_outside_pending(): void
    {
        $this->clubConfig();
        $accused = $this->makeStaff(['happiness' => 70, 'honesty_score' => 100], [], ['honest']);
        $reporter = $this->makeStaff();

        $confrontation = $this->makeConfrontation($reporter->id, $accused->id, true);
        app(ConfrontationService::class)->autoRespond($confrontation);

        $response = $confrontation->fresh()->staff_response;
        $this->assertContains($response, ['confessed', 'bs']);
        $this->assertSame($response === 'confessed' ? 55 : 62, $accused->fresh()->happiness);
    }

    public function test_confessed_response_applies_happiness_hit(): void
    {
        $this->clubConfig();
        $accused = $this->makeStaff(['happiness' => 70]);
        $reporter = $this->makeStaff();

        $confrontation = $this->makeConfrontation($reporter->id, $accused->id);
        app(ConfrontationService::class)->respond($confrontation, 'confessed');

        $this->assertSame('confessed', $confrontation->fresh()->staff_response);
        $this->assertSame(55, $accused->fresh()->happiness);
        $this->assertCount(1, $confrontation->fresh()->happiness_impacts);
        $this->assertDatabaseHas('staff_events', ['staff_id' => $accused->id, 'event_type' => 'confession']);
    }

    public function test_penalized_verdict_creates_a_penalty(): void
    {
        $this->clubConfig();
        $accused = $this->makeStaff(['happiness' => 70]);
        $reporter = $this->makeStaff(['happiness' => 70]);

        $confrontation = $this->makeConfrontation($reporter->id, $accused->id);
        app(ConfrontationService::class)->respond($confrontation, 'confessed');
        app(ConfrontationService::class)->verdict($confrontation, 'penalized', 200);

        $this->assertSame('penalized', $confrontation->fresh()->manager_verdict);
        $this->assertSame(43, $accused->fresh()->happiness);
        $this->assertSame(66, $reporter->fresh()->happiness);
        $this->assertDatabaseHas('penalties', [
            'staff_id' => $accused->id,
            'type' => 'pay_dock',
            'amount_or_hours' => 200,
        ]);
    }
}
