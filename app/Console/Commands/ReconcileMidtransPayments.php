<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ReconcileMidtransPayments extends Command
{
    protected $signature = 'payments:reconcile-midtrans
                            {--limit=100 : Maximum number of payments to process per run}
                            {--dry-run : Show what would be reconciled without making changes}';

    protected $description = 'Reconcile pending Midtrans payments by pulling their transaction status';

    public function handle(PaymentService $paymentService): int
    {
        $minutes = (int) config('midtrans.reconcile_after_minutes', 2);
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $query = Payment::where('provider', 'midtrans')
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->limit($limit);

        if ($dryRun) {
            $count = $query->count();
            $this->info("Dry run: {$count} pending Midtrans payment(s) would be reconciled.");

            if ($count > 0) {
                $payments = $query->get(['id', 'business_id', 'sale_id', 'amount', 'created_at']);
                $this->table(
                    ['ID', 'Business ID', 'Sale ID', 'Amount', 'Created At'],
                    $payments->map(fn ($payment) => [
                        $payment->id,
                        $payment->business_id,
                        $payment->sale_id,
                        $payment->amount,
                        $payment->created_at?->toDateTimeString(),
                    ])->toArray()
                );
            }

            return Command::SUCCESS;
        }

        $reconciledCount = 0;
        $failedCount = 0;

        foreach ($query->get() as $payment) {
            try {
                $paymentService->reconcileWithMidtrans($payment);
                $reconciledCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("Failed to reconcile payment {$payment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Reconciled {$reconciledCount} payment(s).");

        if ($failedCount > 0) {
            $this->warn("Failed to reconcile {$failedCount} payment(s).");
        }

        return $failedCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
