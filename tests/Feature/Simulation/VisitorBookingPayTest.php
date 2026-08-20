<?php

namespace Tests\Feature\Simulation;

use App\Models\LaneBooking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Payments\PaymentSettler;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class VisitorBookingPayTest extends TestCase
{
    use CreatesSimFixtures;

    /**
     * @return array{0: User, 1: Visitor, 2: LaneBooking}
     */
    private function linkedVisitorBooking(array $bookingAttrs = []): array
    {
        $user = $this->makeUser(['role' => 'customer']);
        $visitor = $this->makeVisitor(['user_id' => $user->id]);

        $booking = $this->makeBooking(array_merge([
            'visitor_id' => $visitor->id,
            'status' => 'pending',
            'queue_position' => null,
            'amount' => 200,
            'date' => Carbon::today(),
        ], $bookingAttrs));

        return [$user, $visitor, $booking];
    }

    public function test_owner_can_settle_a_pending_booking_when_gateway_is_unconfigured(): void
    {
        $this->clubConfig();
        config(['services.sslcommerz.store_id' => '', 'services.sslcommerz.store_password' => '']);

        [$user, , $booking] = $this->linkedVisitorBooking();

        $response = $this->actingAs($user)
            ->postJson(route('visitor.bookings.pay', $booking));

        $response->assertOk()->assertJsonPath('settled', true);

        $this->assertSame('confirmed', $booking->fresh()->status);

        $payment = Payment::where('payable_type', LaneBooking::class)
            ->where('payable_id', $booking->id)
            ->first();

        $this->assertNotNull($payment);
        $this->assertSame('success', $payment->status);
    }

    public function test_configured_gateway_opens_a_session_and_later_settlement_confirms_the_booking(): void
    {
        $this->clubConfig();
        config(['services.sslcommerz.store_id' => 'TESTSTORE', 'services.sslcommerz.store_password' => 'TESTPASS']);

        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'sessionkey' => 'SESS-ABC',
                'GatewayPageURL' => 'https://gateway.test/pay/session-1',
            ]),
        ]);

        [$user, , $booking] = $this->linkedVisitorBooking();

        $response = $this->actingAs($user)
            ->postJson(route('visitor.bookings.pay', $booking));

        $response->assertOk()
            ->assertJsonPath('gateway_url', 'https://gateway.test/pay/session-1');

        $this->assertSame('pending', $booking->fresh()->status);

        $payment = Payment::findOrFail($response->json('payment_id'));
        $this->assertSame('processing', $payment->status);
        $this->assertSame('SESS-ABC', $payment->session_key);

        $this->assertTrue(app(PaymentSettler::class)->complete(
            $payment,
            $payment->transaction_id,
            ['status' => 'VALID']
        ));

        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('success', $payment->fresh()->status);
    }

    public function test_a_stale_processing_session_is_reissued_with_a_fresh_transaction(): void
    {
        $this->clubConfig();
        config(['services.sslcommerz.store_id' => 'TESTSTORE', 'services.sslcommerz.store_password' => 'TESTPASS']);

        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'sessionkey' => 'SESS-RETRY',
                'GatewayPageURL' => 'https://gateway.test/pay/session-2',
            ]),
        ]);

        [$user, , $booking] = $this->linkedVisitorBooking();

        $stale = Payment::create([
            'payable_type' => LaneBooking::class,
            'payable_id' => $booking->id,
            'transaction_id' => 'TFOLD123',
            'session_key' => 'SESS-OLD',
            'amount' => 200,
            'currency' => 'BDT',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('visitor.bookings.pay', $booking));

        $response->assertOk()->assertJsonPath('payment_id', $stale->id);

        $stale->refresh();
        $this->assertSame('SESS-RETRY', $stale->session_key);
        $this->assertNotSame('TFOLD123', $stale->transaction_id);
    }

    public function test_customers_cannot_pay_someone_elses_booking(): void
    {
        $this->clubConfig();
        config(['services.sslcommerz.store_id' => '', 'services.sslcommerz.store_password' => '']);

        [, , $booking] = $this->linkedVisitorBooking();
        $stranger = $this->makeUser(['role' => 'customer']);

        $this->actingAs($stranger)
            ->postJson(route('visitor.bookings.pay', $booking))
            ->assertForbidden();

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_queued_bookings_cannot_be_paid_until_promoted(): void
    {
        $this->clubConfig();
        config(['services.sslcommerz.store_id' => '', 'services.sslcommerz.store_password' => '']);

        [$user, , $booking] = $this->linkedVisitorBooking(['queue_position' => 2]);

        $this->actingAs($user)
            ->postJson(route('visitor.bookings.pay', $booking))
            ->assertStatus(422);

        $this->assertSame('pending', $booking->fresh()->status);
    }
}
