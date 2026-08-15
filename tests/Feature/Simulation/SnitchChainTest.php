<?php

namespace Tests\Feature\Simulation;

use App\Models\SnitchReport;
use App\Models\Staff;
use App\Models\StaffMessage;
use App\Services\Simulation\Clock;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class SnitchChainTest extends TestCase
{
    use CreatesSimFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubConfig();
    }

    private function caretaker(): Staff
    {
        return $this->makeStaff(['name' => 'Caretaker One']);
    }

    private function coworker(): Staff
    {
        return $this->makeStaff(['name' => 'Caretaker Two']);
    }

    private function steward(): Staff
    {
        return $this->makeStaff(['role' => 'steward', 'name' => 'Steward Sadmarre']);
    }

    public function test_full_snitch_chain_reaches_a_manager_verdict(): void
    {
        $player = $this->caretaker();
        $target = $this->coworker();
        $steward = $this->steward();

        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $msg = StaffMessage::with('staff')
            ->whereDate('date', Clock::date())
            ->where('staff_id', $target->id)
            ->whereNotIn('kind', ['reply', 'thought'])
            ->first();

        $this->assertNotNull($msg, 'Expected a seeded NPC message to snitch on.');

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.reply', $msg), ['action' => 'snitch'])
            ->assertRedirect();

        $report = SnitchReport::where('reporter_staff_id', $player->id)->where('status', 'pending')->first();
        $this->assertNotNull($report);

        $this->actingAs($steward->user)
            ->get(route('steward.snitch.index'))
            ->assertOk()
            ->assertSee('Waiting on Your Desk');

        $this->actingAs($steward->user)
            ->post(route('steward.snitch.escalate', $report), ['note' => 'Witnessed it myself.'])
            ->assertRedirect();

        $report->refresh();
        $this->assertSame('escalated', $report->status);
        $this->assertNotNull($report->confrontation_id);
        $this->assertNotNull($report->escalated_at);

        $confrontation = $report->confrontation;
        $this->assertSame($player->id, $confrontation->reporter_staff_id);
        $this->assertSame($target->id, $confrontation->accused_staff_id);
        $this->assertSame('other', $confrontation->incident_type);

        $player->refresh();
        $this->assertGreaterThanOrEqual(75, $player->happiness);
        $this->assertDatabaseHas('bonuses', ['staff_id' => $player->id, 'type' => 'recognition']);

        $this->actingAs($target->user)
            ->get(route('caretaker.crew.index'))
            ->assertOk()
            ->assertSee('You', false)
            ->assertSee('1 OPEN', false);

        $this->actingAs($target->user)
            ->post(route('caretaker.crew.respond', $confrontation), ['response' => 'innocent'])
            ->assertRedirect();

        $this->assertSame('innocent', $confrontation->fresh()->staff_response);

        $admin = $this->makeUser(['role' => 'admin', 'name' => 'Club Manager']);
        $this->actingAs($admin)
            ->post(route('manager.confrontations.verdict', $confrontation), ['verdict' => 'upheld'])
            ->assertRedirect();

        $this->assertSame('upheld', $confrontation->fresh()->manager_verdict);
    }

    public function test_steward_can_dismiss_a_report_without_a_confrontation(): void
    {
        $player = $this->caretaker();
        $target = $this->coworker();
        $steward = $this->steward();

        $report = SnitchReport::create([
            'reporter_staff_id' => $player->id,
            'accused_staff_id' => $target->id,
            'quote' => 'Management does not see the work we put in.',
            'status' => 'pending',
        ]);

        $this->actingAs($steward->user)
            ->post(route('steward.snitch.dismiss', $report))
            ->assertRedirect();

        $this->assertSame('dismissed', $report->fresh()->status);
        $this->assertDatabaseMissing('confrontations', ['reporter_staff_id' => $player->id]);
    }

    public function test_handled_report_cannot_be_escalated_twice(): void
    {
        $player = $this->caretaker();
        $target = $this->coworker();
        $steward = $this->steward();

        $report = SnitchReport::create([
            'reporter_staff_id' => $player->id,
            'accused_staff_id' => $target->id,
            'quote' => 'Something about payroll.',
            'status' => 'dismissed',
        ]);

        $this->actingAs($steward->user)
            ->post(route('steward.snitch.escalate', $report))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($report->fresh()->confrontation_id);
    }

    public function test_customer_cannot_open_steward_snitch_inbox(): void
    {
        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer)->get(route('steward.snitch.index'))->assertForbidden();
    }
}
