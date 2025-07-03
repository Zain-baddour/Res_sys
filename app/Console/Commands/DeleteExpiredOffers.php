<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteExpiredOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired offers where end_offer < today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $expiredOffers = Offer::whereDate('end_offer', '<', $today)->get();

        $count = $expiredOffers->count();

        foreach ($expiredOffers as $offer) {
            $offer->delete();
        }

        Log::info("✅ Deleted $count expired offers.");
        $this->info("✅ Deleted $count expired offers.");
    }
}
