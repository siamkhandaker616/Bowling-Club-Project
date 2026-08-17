<?php

namespace Tests\Concerns;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Personality;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

trait CreatesSimFixtures
{
    protected function clubConfig(array $overrides = []): ClubConfig
    {
        return ClubConfig::updateOrCreate(['id' => 1], array_merge([
            'bad_day_mode' => false,
            'current_day' => 1,
            'reputation' => 75,
            'total_revenue' => 0,
            'total_expenses' => 0,
        ], $overrides));
    }

    protected function makeUser(array $attrs = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User ' . uniqid(),
            'email' => 'user' . uniqid() . '@test.local',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_npc' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ], $attrs));
    }

    protected function makeStaff(array $staffAttrs = [], array $userAttrs = [], array $personalities = []): Staff
    {
        $role = $staffAttrs['role'] ?? 'caretaker';

        $user = $this->makeUser(array_merge([
            'name' => $staffAttrs['name'] ?? ('Staff ' . uniqid()),
            'role' => $role,
            'is_npc' => true,
        ], $userAttrs));

        $staff = Staff::create(array_merge([
            'user_id' => $user->id,
            'role' => $role,
            'base_salary' => 2500,
            'current_salary' => 2500,
            'happiness' => 70,
            'performance_score' => 50,
            'honesty_score' => 50,
            'hire_date' => Carbon::today(),
            'is_active' => true,
            'warnings_count' => 0,
        ], $staffAttrs));

        if (! empty($personalities)) {
            $staff->personalities()->attach(array_map(
                fn (string $name) => $this->personality($name)->id,
                $personalities
            ));
        }

        return $staff;
    }

    protected function personality(string $name): Personality
    {
        return Personality::firstOrCreate(['name' => $name], ['description' => $name]);
    }

    protected function makeVisitor(array $attrs = []): Visitor
    {
        return Visitor::create(array_merge([
            'name' => 'Visitor ' . uniqid(),
            'email' => 'visitor' . uniqid() . '@test.local',
            'tier' => 'regular',
            'reputation_score' => 50,
            'is_banned' => false,
        ], $attrs));
    }

    protected function makeInventory(array $attrs = []): Inventory
    {
        return Inventory::create(array_merge([
            'name' => 'Item ' . uniqid(),
            'category' => 'supplies',
            'quantity' => 4,
            'max_quantity' => 20,
            'reorder_threshold' => 5,
            'cost_per_unit' => 3,
        ], $attrs));
    }

    protected function makeLane(array $attrs = []): Lane
    {
        return Lane::create(array_merge([
            'lane_number' => (Lane::max('lane_number') ?? 0) + 1,
            'status' => 'open',
            'oil_level' => 80,
        ], $attrs));
    }

    protected function makeBooking(array $attrs = []): LaneBooking
    {
        $defaults = [
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'confirmed',
            'queue_position' => null,
        ];

        if (! array_key_exists('visitor_id', $attrs)) {
            $defaults['visitor_id'] = $this->makeVisitor()->id;
        }

        if (! array_key_exists('lane_id', $attrs)) {
            $defaults['lane_id'] = $this->makeLane()->id;
        }

        return LaneBooking::create(array_merge($defaults, $attrs));
    }

    protected function makeShift(array $attrs = []): Shift
    {
        $defaults = [
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'scheduled',
            'hours' => 4,
        ];

        if (! array_key_exists('staff_id', $attrs)) {
            $defaults['staff_id'] = $this->makeStaff()->id;
        }

        if (! array_key_exists('lane_id', $attrs)) {
            $defaults['lane_id'] = $this->makeLane()->id;
        }

        return Shift::create(array_merge($defaults, $attrs));
    }

    protected function simLog(array $overrides = []): array
    {
        return array_merge([
            'date' => Carbon::today()->toDateString(),
            'date_label' => Carbon::today()->format('D, M j Y'),
            'bookings_created' => 0,
            'bookings_served' => 0,
            'queues_promoted' => 0,
            'revenue' => 0,
            'expenses' => 0,
            'accidents' => collect(),
            'low_stock' => collect(),
            'happiness_changes' => collect(),
            'complaints_auto' => 0,
            'reputation_delta' => 0,
            'quits' => 0,
            'relationship_changes' => collect(),
            'trash_talk' => [],
            'snitches' => [],
            'snitch_bonuses' => 0,
            'turnaways' => 0,
            'refunds' => 0,
            'matches' => collect(),
            'match_revenue' => 0,
            'league_penalties' => 0,
        ], $overrides);
    }
}
