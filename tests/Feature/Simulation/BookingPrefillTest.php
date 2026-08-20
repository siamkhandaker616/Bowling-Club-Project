<?php

namespace Tests\Feature\Simulation;

use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class BookingPrefillTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_lane_query_param_preselects_the_lane(): void
    {
        $user = $this->makeUser();
        $this->makeVisitor(['user_id' => $user->id]);
        $lane = $this->makeLane();
        $this->makeLane();

        $this->actingAs($user)
            ->get(route('visitor.bookings.create', ['lane' => $lane->lane_number]))
            ->assertOk()
            ->assertSee('value="' . $lane->id . '"', false)
            ->assertSee('data-v="' . $lane->id . '"', false)
            ->assertSee('br-lane on', false);
    }

    public function test_unknown_lane_query_param_is_ignored(): void
    {
        $user = $this->makeUser();
        $this->makeVisitor(['user_id' => $user->id]);
        $this->makeLane();

        $this->actingAs($user)
            ->get(route('visitor.bookings.create', ['lane' => 999]))
            ->assertOk()
            ->assertSee('name="lane_id"', false);
    }

    public function test_no_lane_query_param_loads_plain_form(): void
    {
        $user = $this->makeUser();
        $this->makeVisitor(['user_id' => $user->id]);
        $lane = $this->makeLane();

        $this->actingAs($user)
            ->get(route('visitor.bookings.create'))
            ->assertOk()
            ->assertSee('name="lane_id"', false)
            ->assertDontSee('br-lane on', false);
    }
}
