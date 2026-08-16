<?php

namespace Tests\Feature\Simulation;

use App\Models\StaffMessage;
use App\Services\Simulation\ConfrontationService;
use App\Services\Simulation\InterrogationEngine;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class InterrogationTest extends TestCase
{
    use CreatesSimFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubConfig();
    }

    private function manager()
    {
        return $this->makeUser(['role' => 'admin']);
    }

    private function confrontation(int $reporterId, int $accusedId, bool $verified = false)
    {
        return app(ConfrontationService::class)->create(
            $reporterId,
            $accusedId,
            'negligence',
            'Left the oil machine running unattended.',
            $verified
        );
    }

    public function test_interview_seeds_opening_message_and_returns_chips(): void
    {
        $reporter = $this->makeStaff(['name' => 'Reporter NPC']);
        $accused = $this->makeStaff(['name' => 'Accused NPC']);
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);

        $this->actingAs($this->manager())
            ->getJson(route('manager.confrontations.interview', $confrontation))
            ->assertOk()
            ->assertJsonStructure([
                'messages' => [['id', 'name', 'initials', 'bubble_type', 'body']],
                'chips' => [['action', 'key', 'label']],
                'accused' => ['id', 'name', 'initials'],
            ])
            ->assertJsonCount(5, 'chips')
            ->assertJsonPath('accused.name', 'Accused NPC');

        $this->assertDatabaseHas('staff_messages', [
            'confrontation_id' => $confrontation->id,
            'staff_id' => $accused->id,
            'kind' => 'interrogation',
        ]);
    }

    public function test_interrogate_returns_a_reply_and_persists_the_transcript(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);

        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'where'])
            ->assertOk()
            ->assertJsonStructure(['reply' => ['id', 'name', 'initials', 'bubble_type', 'body'], 'chips']);

        $this->assertDatabaseHas('staff_messages', [
            'confrontation_id' => $confrontation->id,
            'staff_id' => $accused->id,
            'kind' => 'interrogation',
        ]);
    }

    public function test_witness_chip_questions_a_coworker(): void
    {
        $reporter = $this->makeStaff(['name' => 'Reporter NPC']);
        $accused = $this->makeStaff(['name' => 'Accused NPC']);
        $coworker = $this->makeStaff(['name' => 'Coworker NPC']);
        $confrontation = $this->confrontation($reporter->id, $accused->id, false);

        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'witness'])
            ->assertOk()
            ->assertJsonPath('reply.name', 'Coworker NPC');

        $this->assertDatabaseHas('staff_messages', [
            'confrontation_id' => $confrontation->id,
            'staff_id' => $coworker->id,
            'kind' => 'interrogation',
        ]);
    }

    public function test_interrogate_rejects_unknown_key(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id);

        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'motive'])
            ->assertUnprocessable();
    }

    public function test_conclude_rolls_the_response_and_writes_a_narrative(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff(['honesty_score' => 90], [], ['honest']);
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);

        $this->actingAs($this->manager())
            ->getJson(route('manager.confrontations.interview', $confrontation))
            ->assertOk();

        $this->actingAs($this->manager())
            ->post(route('manager.confrontations.conclude', $confrontation))
            ->assertRedirect(route('manager.confrontations.index'));

        $fresh = $confrontation->fresh();
        $this->assertNotNull($fresh->staff_response);
        $this->assertStringContainsString('rounds of questioning', $fresh->investigation_result);
    }

    public function test_conclude_on_unverified_confrontation_returns_innocent(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id, false);

        $this->actingAs($this->manager())
            ->post(route('manager.confrontations.conclude', $confrontation))
            ->assertRedirect();

        $this->assertSame('innocent', $confrontation->fresh()->staff_response);
    }

    public function test_interview_after_response_is_conflict(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);
        app(ConfrontationService::class)->respond($confrontation, 'confessed');

        $this->actingAs($this->manager())
            ->getJson(route('manager.confrontations.interview', $confrontation))
            ->assertConflict();

        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'log'])
            ->assertConflict();
    }

    public function test_customer_cannot_interrogate(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id);

        $this->actingAs($this->makeUser(['role' => 'customer']))
            ->getJson(route('manager.confrontations.interview', $confrontation))
            ->assertForbidden();
    }

    public function test_index_page_drops_the_manual_dropdown_and_offers_interrogate(): void
    {
        $reporter = $this->makeStaff(['name' => 'Reporter NPC']);
        $accused = $this->makeStaff(['name' => 'Accused NPC']);
        $pending = $this->confrontation($reporter->id, $accused->id, true);
        $responded = $this->confrontation($reporter->id, $accused->id, true);
        app(ConfrontationService::class)->respond($responded, 'confessed');

        $this->actingAs($this->manager())
            ->get(route('manager.confrontations.index'))
            ->assertOk()
            ->assertSee('Interrogate')
            ->assertSee('Auto-Investigate')
            ->assertDontSee('Record Response')
            ->assertDontSee('Calls BS')
            ->assertSee('Clear accused — penalize the reporter');

        $this->assertSame('confessed', $responded->fresh()->staff_response);
    }

    public function test_auto_investigate_route_still_rolls_the_response(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff(['honesty_score' => 90], [], ['honest']);
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);

        $this->actingAs($this->manager())
            ->post(route('manager.confrontations.respond', $confrontation))
            ->assertRedirect();

        $this->assertContains($confrontation->fresh()->staff_response, ['confessed', 'bs']);
    }

    public function test_engine_initials_builds_from_user_name(): void
    {
        $staff = $this->makeStaff(['name' => 'Rosie Lane']);

        $this->assertSame('RL', app(InterrogationEngine::class)->initials($staff));
    }

    public function test_transcript_count_feeds_the_conclude_narrative(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);

        $this->actingAs($this->manager())
            ->getJson(route('manager.confrontations.interview', $confrontation))
            ->assertOk();

        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'where'])
            ->assertOk();
        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'log'])
            ->assertOk();

        $this->actingAs($this->manager())
            ->post(route('manager.confrontations.conclude', $confrontation))
            ->assertRedirect();

        $this->assertDatabaseHas('staff_messages', [
            'confrontation_id' => $confrontation->id,
            'kind' => 'interrogation',
        ]);
    }

    public function test_transcript_keeps_questions_out_of_the_persisted_thread(): void
    {
        $reporter = $this->makeStaff();
        $accused = $this->makeStaff();
        $confrontation = $this->confrontation($reporter->id, $accused->id, true);

        $this->actingAs($this->manager())
            ->postJson(route('manager.confrontations.interrogate', $confrontation), ['key' => 'where'])
            ->assertOk();

        $messages = StaffMessage::where('confrontation_id', $confrontation->id)->get();
        $this->assertNotEmpty($messages);
        $this->assertTrue($messages->every(fn (StaffMessage $m) => $m->kind === 'interrogation'));
    }
}
