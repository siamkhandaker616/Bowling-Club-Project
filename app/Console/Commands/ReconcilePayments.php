<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Payments\PaymentSettler;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--stale= : Reconcile payments older than this many hours (default: all processing)}';

    protected $description = 'Re-validate stuck processing payments against the gateway and complete them';

    public function handle(PaymentSettler $settler): int
    {
        $query = Payment::where('status', 'processing')->whereNotNull('session_key');

        $stale = $this->option('stale');

        if ($stale !== null) {
            $query->where('updated_at', '<', now()->subHours((int) $stale));
        }

        $payments = $query->get();

        if ($payments->isEmpty()) {
            $this->info('No processing payments to reconcile.');

            return self::SUCCESS;
        }

        $settled = 0;
        $expired = 0;

        foreach ($payments as $payment) {
            if ($settler->settleIfPossible($payment)) {
                $settled++;
                $this->info("Settled payment {$payment->transaction_id}.");
            } else {
                $payment->refresh();

                if ($payment->status === 'failed') {
                    $expired++;
                    $this->warn("Expired payment {$payment->transaction_id} — gateway never confirmed it.");
                } else {
                    $this->warn("Could not settle payment {$payment->transaction_id}.");
                }
            }
        }

        $this->info("Reconciled {$settled} of {$payments->count()} payment(s) ({$expired} expired).");

        return self::SUCCESS;
    }
}
