<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Offer;
use Carbon\Carbon;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->call(function () {
            // تحديث الطلبات التي تجاوزت ساعة في الحالة "معلقة"
            $offers = Offer::all();   
        $todayDate = Carbon::today(); // تاريخ اليوم
       // $todayDatetime = strtotime($todayDate);
        foreach ($offers as $of) {
            $periodOfferDate = Carbon::parse($of->period_offer); // تحويل تنسيق تاريخ period_offer
            if ($periodOfferDate <= $todayDate) {
                $of->update([
                    'period_offer'=>$todayDate
                ]);
    }
        }

        })->hourly();
    
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
