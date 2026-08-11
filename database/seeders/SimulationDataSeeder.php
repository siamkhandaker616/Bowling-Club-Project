<?php

namespace Database\Seeders;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffRelationship;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Simulation\Clock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimulationDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSecondSteward();
        $this->seedVisitors();
        $this->seedInventory();
        $this->seedStaffRelationships();
        $this->seedSchedule();
        $this->seedBookings();
    }

    private function seedSecondSteward(): void
    {
        if (User::where('email', 'maya@cloudnine.ai')->exists()) {
            return;
        }

        $user = User::create([
            'name' => 'Maya Reyes',
            'email' => 'maya@cloudnine.ai',
            'password' => Hash::make('password'),
            'role' => 'steward',
            'email_verified_at' => now(),
            'is_npc' => true,
        ]);

        Staff::create([
            'user_id' => $user->id,
            'role' => 'steward',
            'base_salary' => 3500,
            'current_salary' => 3500,
            'happiness' => 78,
            'performance_score' => 70,
            'honesty_score' => 82,
            'hire_date' => now()->subMonths(4),
            'is_active' => true,
        ]);
    }

    private function seedVisitors(): void
    {
        $premiumEmails = ['alice.morgan@cloudnine.ai', 'diana.reeves@cloudnine.ai', 'hannah.lee@cloudnine.ai'];

        foreach (User::where('role', 'customer')->get() as $user) {
            $visitor = Visitor::firstOrNew(['user_id' => $user->id]);
            $visitor->name = $user->name;
            $visitor->email = $user->email;
            $visitor->tier = in_array($user->email, $premiumEmails, true) ? 'premium' : 'regular';
            $visitor->reputation_score = mt_rand(40, 95);
            $visitor->is_banned = false;
            $visitor->save();
        }
    }

    private function seedInventory(): void
    {
        if (Inventory::exists()) {
            return;
        }

        $items = [
            ['name' => 'Bowling Shoes', 'category' => 'footwear', 'quantity' => 42, 'max_quantity' => 50, 'reorder_threshold' => 10, 'cost_per_unit' => 25],
            ['name' => 'Lane Oil', 'category' => 'oil', 'quantity' => 6, 'max_quantity' => 30, 'reorder_threshold' => 8, 'cost_per_unit' => 40],
            ['name' => 'Spare Pins', 'category' => 'pins', 'quantity' => 30, 'max_quantity' => 60, 'reorder_threshold' => 12, 'cost_per_unit' => 15],
            ['name' => 'Ball Polish', 'category' => 'gear', 'quantity' => 14, 'max_quantity' => 24, 'reorder_threshold' => 6, 'cost_per_unit' => 12],
            ['name' => 'Cleaning Wipes', 'category' => 'cleaning', 'quantity' => 20, 'max_quantity' => 40, 'reorder_threshold' => 8, 'cost_per_unit' => 5],
            ['name' => 'Bar Napkins', 'category' => 'bar', 'quantity' => 60, 'max_quantity' => 100, 'reorder_threshold' => 20, 'cost_per_unit' => 2],
            ['name' => 'Bowling Balls', 'category' => 'gear', 'quantity' => 24, 'max_quantity' => 30, 'reorder_threshold' => 4, 'cost_per_unit' => 60],
            ['name' => 'Score Sheets', 'category' => 'paper', 'quantity' => 100, 'max_quantity' => 200, 'reorder_threshold' => 40, 'cost_per_unit' => 1],
        ];

        foreach ($items as $item) {
            Inventory::create(array_merge($item, ['condition' => 'good']));
        }
    }

    private function seedStaffRelationships(): void
    {
        if (StaffRelationship::exists()) {
            return;
        }

        $caretakers = Staff::where('role', 'caretaker')->pluck('id')->all();

        $pairs = [
            ['a' => $caretakers[0], 'b' => $caretakers[1], 'level' => 'friendly', 'score' => 12],
            ['a' => $caretakers[1], 'b' => $caretakers[2], 'level' => 'trusted', 'score' => 22],
            ['a' => $caretakers[0], 'b' => $caretakers[3], 'level' => 'neutral', 'score' => 2],
            ['a' => $caretakers[6], 'b' => $caretakers[7], 'level' => 'friendly', 'score' => 15],
            ['a' => $caretakers[8], 'b' => $caretakers[9], 'level' => 'hostile', 'score' => -18],
            ['a' => $caretakers[12], 'b' => $caretakers[14], 'level' => 'hostile', 'score' => -24],
        ];

        foreach ($pairs as $pair) {
            $a = min($pair['a'], $pair['b']);
            $b = max($pair['a'], $pair['b']);

            StaffRelationship::create([
                'staff_a_id' => $a,
                'staff_b_id' => $b,
                'level' => $pair['level'],
                'score' => $pair['score'],
            ]);
        }
    }

    private function seedSchedule(): void
    {
        $date = Clock::date();

        if (Shift::whereDate('date', $date)->exists()) {
            return;
        }

        $slots = ['morning', 'afternoon', 'evening'];
        $laneIds = Lane::pluck('id')->all();

        foreach (Staff::where('is_active', true)->get() as $member) {
            Shift::create([
                'staff_id' => $member->id,
                'date' => $date,
                'time_slot' => $slots[array_rand($slots)],
                'lane_id' => $laneIds[array_rand($laneIds)],
                'status' => 'scheduled',
            ]);
        }
    }

    private function seedBookings(): void
    {
        $date = Clock::date();

        if (LaneBooking::whereDate('date', $date)->exists()) {
            return;
        }

        $visitors = Visitor::where('is_banned', false)->get();
        $slots = ['morning', 'afternoon', 'evening'];
        $laneIds = Lane::pluck('id')->all();

        foreach ($visitors as $visitor) {
            if (mt_rand(1, 100) > 75) {
                continue;
            }

            $laneId = $laneIds[array_rand($laneIds)];

            LaneBooking::create([
                'visitor_id' => $visitor->id,
                'lane_id' => $laneId,
                'date' => $date,
                'time_slot' => $slots[array_rand($slots)],
                'status' => 'confirmed',
            ]);
        }
    }
}
