<?php

namespace Tests\Feature\Simulation;

use App\Models\Staff;
use App\Models\StaffMessage;
use App\Models\StaffRelationship;
use App\Services\Simulation\Clock;
use App\Services\Simulation\ConfrontationService;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class CrewChatTest extends TestCase
{
    use CreatesSimFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubConfig();
    }

    private function caretaker(): Staff
    {
        return $this->makeStaff(['name' => 'Player Caretaker']);
    }

    private function coworker(): Staff
    {
        return $this->makeStaff(['name' => 'NPC Caretaker']);
    }

    public function test_crew_page_seeds_the_staff_room_thread(): void
    {
        $player = $this->caretaker();
        $this->coworker();

        $this->actingAs($player->user)
            ->get(route('caretaker.crew.index'))
            ->assertOk()
            ->assertSee('The Staff Room')
            ->assertSee('Relationship Meter');

        $this->assertGreaterThan(0, StaffMessage::whereDate('date', Clock::date())->count());
    }

    public function test_customer_cannot_open_crew_page(): void
    {
        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer)->get(route('caretaker.crew.index'))->assertForbidden();
    }

    public function test_poll_returns_message_payload(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->getJson(route('caretaker.crew.poll', ['after' => 0]))
            ->assertOk()
            ->assertJsonStructure(['messages' => [['id', 'mine', 'name', 'initials', 'bubble_type', 'kind', 'body']], 'typing']);
    }

    public function test_apologize_nudges_trust_and_writes_a_reply(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $npc = StaffMessage::with('staff')
            ->whereDate('date', Clock::date())
            ->where('staff_id', '!=', $player->id)
            ->whereNotIn('kind', ['reply', 'thought'])
            ->where('bubble_type', 'speech')
            ->first();

        $this->assertNotNull($npc, 'Expected a seeded NPC speech message to apologize to.');

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.reply', $npc), ['action' => 'apologize'])
            ->assertRedirect();

        $rel = StaffRelationship::where(function ($q) use ($player, $npc) {
            $q->where('staff_a_id', $player->id)->where('staff_b_id', $npc->staff_id);
        })->orWhere(function ($q) use ($player, $npc) {
            $q->where('staff_a_id', $npc->staff_id)->where('staff_b_id', $player->id);
        })->first();

        $this->assertNotNull($rel);
        $this->assertSame(8, $rel->score);
        $this->assertDatabaseHas('staff_messages', ['staff_id' => $player->id, 'kind' => 'reply']);
    }

    public function test_snitching_creates_pending_report_for_the_steward(): void
    {
        $player = $this->caretaker();
        $npc = $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $target = StaffMessage::with('staff')
            ->whereDate('date', Clock::date())
            ->where('staff_id', $npc->id)
            ->whereNotIn('kind', ['reply', 'thought'])
            ->first();

        $this->assertNotNull($target, 'Expected a seeded NPC message to snitch on.');

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.reply', $target), ['action' => 'snitch'])
            ->assertRedirect();

        $this->assertDatabaseHas('snitch_reports', [
            'reporter_staff_id' => $player->id,
            'accused_staff_id' => $npc->id,
            'status' => 'pending',
        ]);
    }

    public function test_reply_rejects_unknown_action(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $msg = StaffMessage::whereDate('date', Clock::date())->first();

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.reply', $msg), ['action' => 'yell'])
            ->assertSessionHasErrors('action');
    }

    public function test_responding_to_accusation_records_response(): void
    {
        $player = $this->caretaker();
        $reporter = $this->coworker();

        $confrontation = app(ConfrontationService::class)->create($reporter->id, $player->id, 'negligence', 'Left the oil machine running.', true);

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.respond', $confrontation), ['response' => 'confessed'])
            ->assertRedirect();

        $this->assertSame('confessed', $confrontation->fresh()->staff_response);
    }

    public function test_cannot_respond_to_someone_elses_confrontation(): void
    {
        $player = $this->caretaker();
        $accused = $this->coworker();

        $confrontation = app(ConfrontationService::class)->create($player->id, $accused->id, 'negligence', 'x', true);

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.respond', $confrontation), ['response' => 'confessed'])
            ->assertForbidden();
    }

    public function test_venting_logs_a_vent_message(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->post(route('caretaker.crew.vent'))
            ->assertRedirect();

        $this->assertDatabaseHas('staff_messages', ['staff_id' => $player->id, 'kind' => 'vent']);
    }

    public function test_group_send_posts_a_message_to_the_room(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->postJson(route('caretaker.crew.send'), ['body' => 'Good morning everyone.'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('staff_messages', [
            'staff_id' => $player->id,
            'recipient_staff_id' => null,
            'kind' => 'reply',
            'body' => 'Good morning everyone.',
        ]);
    }

    public function test_blank_group_send_is_rejected(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $before = StaffMessage::count();

        $this->actingAs($player->user)
            ->postJson(route('caretaker.crew.send'), ['body' => ''])
            ->assertUnprocessable();

        $this->actingAs($player->user)
            ->postJson(route('caretaker.crew.send'), ['body' => '   '])
            ->assertUnprocessable();

        $this->assertSame($before, StaffMessage::count());
    }

    public function test_dm_send_writes_a_private_message_and_reply(): void
    {
        $player = $this->caretaker();
        $steward = $this->makeStaff(['name' => 'NPC Steward', 'role' => 'steward']);
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->postJson(route('caretaker.crew.send'), ['body' => 'Got a minute?', 'to' => $steward->id])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('staff_messages', [
            'staff_id' => $player->id,
            'recipient_staff_id' => $steward->id,
            'kind' => 'reply',
            'body' => 'Got a minute?',
        ]);

        $this->actingAs($player->user)
            ->get(route('caretaker.crew.index', ['with' => $steward->id, 'tab' => 'dm']))
            ->assertOk()
            ->assertSee('Direct Messages')
            ->assertSee('Got a minute?');
    }

    public function test_tabs_default_to_group_and_switch_sections(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $html = $this->actingAs($player->user)
            ->get(route('caretaker.crew.index'))
            ->getContent();

        $this->assertStringContainsString('class="sim-tab on" id="tab-crew"', $html);

        $html = $this->actingAs($player->user)
            ->get(route('caretaker.crew.index', ['tab' => 'reported']))
            ->getContent();

        $this->assertStringContainsString('class="sim-tab on" id="tab-reported"', $html);

        $this->actingAs($player->user)
            ->get(route('caretaker.crew.index', ['tab' => 'nonsense']))
            ->assertOk();

        $this->actingAs($player->user)
            ->get(route('caretaker.crew.index', ['tab' => 'ledger']))
            ->assertSee('Snitch Ledger');
    }

    public function test_cannot_dm_self(): void
    {
        $player = $this->caretaker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->postJson(route('caretaker.crew.send'), ['body' => 'hi', 'to' => $player->id])
            ->assertForbidden();
    }

    public function test_dm_endpoint_returns_thread_and_chips(): void
    {
        $player = $this->caretaker();
        $steward = $this->makeStaff(['name' => 'NPC Steward', 'role' => 'steward']);
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->postJson(route('caretaker.crew.send'), ['body' => 'Got a minute?', 'to' => $steward->id])
            ->assertOk();

        $this->actingAs($player->user)
            ->getJson(route('caretaker.crew.dm', ['with' => $steward->id]))
            ->assertOk()
            ->assertJsonStructure([
                'messages' => [['id', 'mine', 'name', 'initials', 'bubble_type', 'kind', 'body']],
                'chips' => [['label', 'action']],
                'other' => ['id', 'name', 'initials'],
            ])
            ->assertJsonPath('other.id', $steward->id);
    }

    public function test_dm_chips_offer_snitch_on_damning_message(): void
    {
        $player = $this->caretaker();
        $steward = $this->makeStaff(['name' => 'NPC Steward', 'role' => 'steward']);
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $damning = StaffMessage::create([
            'staff_id' => $steward->id,
            'recipient_staff_id' => $player->id,
            'bubble_type' => 'speech',
            'kind' => 'chat',
            'body' => 'Do not repeat this, but I saw the steward trimming the overtime log.',
            'date' => Clock::date(),
        ]);

        $this->actingAs($player->user)
            ->getJson(route('caretaker.crew.dm', ['with' => $steward->id]))
            ->assertOk()
            ->assertJsonPath('chips.0.action', 'snitch')
            ->assertJsonPath('chips.0.message_id', $damning->id);
    }

    public function test_poll_returns_vibe_chips(): void
    {
        $player = $this->caretaker();
        $this->coworker();
        $this->actingAs($player->user)->get(route('caretaker.crew.index'))->assertOk();

        $this->actingAs($player->user)
            ->getJson(route('caretaker.crew.poll', ['after' => 0]))
            ->assertOk()
            ->assertJsonStructure(['messages', 'chips' => [['label', 'action']]]);
    }
}
