<?php

namespace App\Console\Commands;

use App\Models\StockReservation;
use App\Services\StockReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireStockReservations extends Command
{
    protected $signature = 'stock:reservations:expire
                            {--limit=100 : Maximum number of reservations to process per run}
                            {--dry-run : Show what would be expired without making changes}';

    protected $description = 'Find and expire active stock reservations that have passed their expiry time';

    public function handle(StockReservationService $reservationService): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $query = StockReservation::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->limit($limit);

        if ($dryRun) {
            $count = $query->count();
            $this->info("Dry run: {$count} reservation(s) would be expired.");
            if ($count > 0) {
                $reservations = $query->get(['id', 'business_id', 'product_id', 'quantity', 'expires_at']);
                $this->table(
                    ['ID', 'Business ID', 'Product ID', 'Quantity', 'Expires At'],
                    $reservations->map(fn ($r) => [
                        $r->id,
                        $r->business_id,
                        $r->product_id,
                        $r->quantity,
                        $r->expires_at->toDateTimeString(),
                    ])->toArray()
                );
            }

            return Command::SUCCESS;
        }

        $expiredCount = 0;
        $failedCount = 0;

        $reservations = $query->get(['id', 'business_id']);

        foreach ($reservations as $reservation) {
            try {
                DB::transaction(function () use ($reservation, $reservationService) {
                    $reservationService->expire($reservation->id, $reservation->business_id);
                });
                $expiredCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("Failed to expire reservation {$reservation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Expired {$expiredCount} reservation(s).");
        if ($failedCount > 0) {
            $this->warn("Failed to expire {$failedCount} reservation(s).");
        }

        return $failedCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
