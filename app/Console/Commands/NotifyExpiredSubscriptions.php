<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Models\hall;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-expired-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications when hall subscription is expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $halls = Hall::whereDate('subscription_end_date', $today->copy())->get();

        foreach ($halls as $hall) {
            $ownerTokens = DeviceToken::where('user_id', $hall->owner_id)->pluck('device_token');

            $staffTokens = DeviceToken::whereIn('user_id', $hall->employee()->pluck('user_id'))->pluck('device_token');

            $allTokens = $ownerTokens->merge($staffTokens);

            foreach ($allTokens as $token) {
                FirebaseNotificationService::sendNotification(
                    $token,
                    "Subscription Expired",
                    "Your hall '{$hall->name}' subscription expired please subscribe."
                );
            }

            $this->info("Notified hall: {$hall->name}");
        }
    }
}
