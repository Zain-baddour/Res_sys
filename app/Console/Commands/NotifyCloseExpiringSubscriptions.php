<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hall;
use App\Models\DeviceToken;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;

class NotifyCloseExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-close-expiring-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications when hall subscription is 2 days away from expiry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $halls = Hall::whereDate('subscription_end_date', $today->copy()->addDays(2))->get();

        foreach ($halls as $hall) {
            $ownerTokens = DeviceToken::where('user_id', $hall->owner_id)->pluck('device_token');

            $staffTokens = DeviceToken::whereIn('user_id', $hall->employee()->pluck('user_id'))->pluck('device_token');

            $allTokens = $ownerTokens->merge($staffTokens);

            foreach ($allTokens as $token) {
                FirebaseNotificationService::sendNotification(
                    $token,
                    "Subscription Expiry Reminder",
                    "Your hall '{$hall->name}' subscription will expire in 2 days."
                );
            }

            $this->info("Notified hall: {$hall->name}");
        }
    }
}
