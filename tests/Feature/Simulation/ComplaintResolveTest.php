<?php

namespace Tests\Feature\Simulation;

use App\Models\Accident;
use App\Models\Complaint;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class ComplaintResolveTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_resolving_a_complaint_auto_resolves_linked_accident(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id]);
        $staff = $this->makeStaff();
        $shift = $this->makeShift(['staff_id' => $staff->id]);

        $accident = Accident::create([
            'staff_id' => $staff->id,
            'shift_id' => $shift->id,
            'type' => 'pinsetter_jam',
            'severity' => 'minor',
            'description' => 'Lane jammed mid-game',
            'resolved' => false,
            'affected_booking_id' => $booking->id,
        ]);

        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'service',
            'description' => 'Lane broke down during my game',
            'status' => 'open',
        ]);

        $response = $this->actingAs($admin)->post(route('manager.complaints.resolve', $complaint), [
            'resolution' => 'Refunded a game',
            'compensation_type' => 'refund',
        ]);

        $response->assertRedirect(route('manager.complaints.index'));

        $this->assertSame('resolved', $complaint->fresh()->status);
        $this->assertSame('refund', $complaint->fresh()->compensation_type);
        $this->assertSame($admin->id, $complaint->fresh()->resolved_by);
        $this->assertNotNull($complaint->fresh()->resolved_at);
        $this->assertTrue((bool) $accident->fresh()->resolved);
        $this->assertStringContainsString('Linked to complaint', $accident->fresh()->resolution);
    }

    public function test_dismissing_a_complaint_marks_it_dismissed(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $visitor = $this->makeVisitor();

        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'service',
            'description' => 'Too noisy',
            'status' => 'open',
        ]);

        $this->actingAs($admin)->post(route('manager.complaints.dismiss', $complaint))->assertRedirect();

        $this->assertSame('dismissed', $complaint->fresh()->status);
        $this->assertSame($admin->id, $complaint->fresh()->resolved_by);
    }
}
