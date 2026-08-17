<?php

namespace App\Helpers;

class Label
{
    public static function timeSlot(string $value): string
    {
        return match ($value) {
            'morning' => '10:00',
            'afternoon' => '14:00',
            'evening' => '18:00',
            default => ucfirst($value),
        };
    }

    public static function timeSlotFull(string $value): string
    {
        return match ($value) {
            'morning' => 'Morning (10am – 1pm)',
            'afternoon' => 'Afternoon (1pm – 6pm)',
            'evening' => 'Evening (6pm – 11pm)',
            default => ucfirst($value),
        };
    }

    public static function shiftStatus(string $value): string
    {
        return match ($value) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'On Shift',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($value),
        };
    }

    public static function bookingStatus(string $value): string
    {
        return match ($value) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($value),
        };
    }

    public static function queueStatus(string $value): string
    {
        return match ($value) {
            'waiting' => 'Waiting',
            'notified' => 'Notified',
            'expired' => 'Expired',
            default => ucfirst($value),
        };
    }

    public static function complaintStatus(string $value): string
    {
        return match ($value) {
            'open' => 'Open',
            'investigating' => 'Under Review',
            'resolved' => 'Resolved',
            'dismissed' => 'Dismissed',
            default => ucfirst($value),
        };
    }

    public static function banStatus(string $value): string
    {
        return match ($value) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'denied' => 'Denied',
            default => ucfirst($value),
        };
    }

    public static function touringStatus(string $value): string
    {
        return match ($value) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'declined' => 'Declined',
            default => ucfirst($value),
        };
    }

    public static function confrontationVerdict(string $value): string
    {
        return match ($value) {
            'upheld' => 'Upheld',
            'dismissed' => 'Dismissed',
            'penalized' => 'Penalized',
            'reporter_penalized' => 'Reporter Penalized',
            default => 'Awaiting Verdict',
        };
    }

    public static function compensationType(string $value): string
    {
        return match ($value) {
            'free_game' => 'Free Game',
            'refund' => 'Refund',
            'discount' => 'Discount',
            'apology' => 'Apology',
            'priority_queue' => 'Priority Queue',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }

    public static function incidentType(string $value): string
    {
        return match ($value) {
            'theft' => 'Theft',
            'sabotage' => 'Sabotage',
            'harassment' => 'Harassment',
            'negligence' => 'Negligence',
            'other' => 'Other',
            default => ucfirst($value),
        };
    }

    public static function staffRole(string $value): string
    {
        return match ($value) {
            'club_manager' => 'Club Manager',
            'steward' => 'Steward',
            'caretaker' => 'Caretaker',
            default => ucfirst($value),
        };
    }

    public static function tier(?string $value): string
    {
        return match ($value) {
            'regular' => 'Regular',
            'premium' => 'Premium',
            default => ucfirst((string) $value),
        };
    }

    public static function relationshipLevel(string $value): string
    {
        return match ($value) {
            'hostile' => 'Hostile',
            'neutral' => 'Neutral',
            'friendly' => 'Friendly',
            'trusted' => 'Trusted',
            default => ucfirst($value),
        };
    }

    public static function staffEventType(string $value): string
    {
        return match ($value) {
            'accident' => 'Accident',
            'hired' => 'Hired',
            'fired' => 'Fired',
            'bonus' => 'Bonus',
            'penalty' => 'Penalty',
            'quit' => 'Resigned',
            'worked' => 'Shift Completed',
            'trash_talk' => 'Trash Talk',
            'social' => 'Social',
            'snitch_report' => 'Snitch Report',
            'confrontation_response' => 'Confrontation Response',
            'confession' => 'Confession',
            'accused_but_denied' => 'Denial',
            'accused_brushed_off' => 'Brushed Off',
            'verdict_upheld' => 'Verdict Upheld',
            'report_dismissed' => 'Report Dismissed',
            'verdict_penalized' => 'Verdict Penalized',
            'report_partly_credited' => 'Report Partly Credited',
            'report_deemed_false' => 'Report Deemed False',
            'exonerated' => 'Exonerated',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }

    public static function bonusType(string $value): string
    {
        return match ($value) {
            'cash' => 'Cash Bonus',
            'time_off' => 'Time Off',
            'recognition' => 'Recognition',
            default => ucfirst($value),
        };
    }

    public static function penaltyType(string $value): string
    {
        return match ($value) {
            'pay_dock' => 'Pay Dock',
            'extra_hours' => 'Extra Hours',
            'written_warning' => 'Written Warning',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }

    public static function laneStatus(string $value): string
    {
        return match ($value) {
            'open' => 'Open',
            'occupied' => 'In Play',
            'maintenance' => 'Under Maintenance',
            'reserved' => 'Reserved',
            default => ucfirst($value),
        };
    }

    public static function inventoryCondition(string $value): string
    {
        return match ($value) {
            'good' => 'Good',
            'worn' => 'Worn',
            'broken' => 'Broken',
            default => ucfirst($value),
        };
    }

    public static function billStatus(string $value): string
    {
        return match ($value) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($value),
        };
    }

    public static function paymentStatus(string $value): string
    {
        return match ($value) {
            'pending' => 'Awaiting Payment',
            'processing' => 'Processing',
            'success' => 'Paid in Full',
            'failed' => 'Declined',
            'cancelled' => 'Cancelled',
            default => ucfirst($value),
        };
    }

    public static function complaintType(string $value): string
    {
        return match ($value) {
            'service' => 'Service',
            'cleanliness' => 'Cleanliness',
            'behavior' => 'Behavior',
            'facility' => 'Facility',
            'other' => 'Other',
            default => ucfirst($value),
        };
    }
}
