<?php

namespace App\Console\Commands;
use App\Models\Offer;
use Illuminate\Console\Command;

class offersdel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:offersdel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {  $offers = Offer::all();   
        $todayDate = date('Y-m-d'); // تاريخ اليوم
        $todayDatetime = strtotime($todayDate);
        foreach ($offers as $of) {
            $periodOfferDate = date('Y-m-d', strtotime($of->period_offer)); // تحويل تنسيق تاريخ period_offer
            if ($periodOfferDate <= $todayDatetime) {
                $of->update([
                    'period_offer'=>$todayDatetime
                ]);
    }
        }
        
    }
}
