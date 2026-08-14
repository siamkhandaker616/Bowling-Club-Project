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
            ->assertSee('value="' . $lane->id . '" selected', false);
    }

    public function test_unknown_lane_query_param_is_ignored(): void
    {
        $user = $this->makeUser();
        $this->makeVisitor(['user_id' => $user->id]);
        $this->makeLane();

        $this->actingAs($user)
            ->get(route('visitor.bookings.create', ['lane' => 999]))
            ->assertOk()
            ->assertDontSee('value="1" selected', false);
    }

    public function test_no_lane_query_param_loads_plain_form(): void
    {
        $user = $this->makeUser();
        $this->makeVisitor(['user_id' => $user->id]);
        $lane = $this->makeLane();

        $this->actingAs($user)
            ->get(route('visitor.bookings.create'))
            ->assertOk()
            ->assertDontSee('value="' . $lane->id . '" selected', false);
    }
}
