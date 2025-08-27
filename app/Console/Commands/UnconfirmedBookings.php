<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class UnconfirmedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:check-unconfirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unconfirmed bookings and notify or delete them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneDayAgo  = Carbon::now()->subDay(1);
        $twoDaysAgo = Carbon::now()->subDays(2);

        // ✅ حالة 1: الحجوزات unconfirmed صار لها يوم
        $oneDayBookings = Booking::where('status', 'unconfirmed')
            ->whereDate('created_at', '=', $oneDayAgo->toDateString())
            ->get();

        foreach ($oneDayBookings as $booking) {
            $deviceTokens = DeviceToken::where('user_id', $booking->user_id)->pluck('device_token');
            foreach ($deviceTokens as $token) {
                FirebaseNotificationService::sendNotification(
                    $token,
                    "Reminder To Confirm Booking",
                    "Your booking at {$booking->event_date} will be deleted in 1 day if you dont confirm it"
                );
            }

            $this->info("Reminder sent for booking #{$booking->id}");
        }

        // ✅ حالة 2: الحجوزات unconfirmed صار لها يومين → حذف + إشعار
        $twoDayBookings = Booking::where('status', 'unconfirmed')
            ->where('created_at', '<=', $twoDaysAgo)
            ->get();

        foreach ($twoDayBookings as $booking) {
            $deviceTokens = DeviceToken::where('user_id', $booking->user_id)->pluck('device_token');
            foreach ($deviceTokens as $token) {
                FirebaseNotificationService::sendNotification(
                    $token,
                    "Booking Deleted due to no Confirmation",
                    "Your booking at {$booking->event_date} was deleted because you did not confirm it"
                );
            }

            $booking->delete();

            $this->warn("Booking #{$booking->id} deleted due to no confirmation.");
        }

        $this->info("Check unconfirmed bookings completed.");
    }
}
