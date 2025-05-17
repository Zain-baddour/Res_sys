<?php

namespace App\Console\Commands;
use App\Models\Offer;
use Illuminate\Console\Command;
use Carbon\Carbon;

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
    {   $offers = Offer::all();   
        $todayDate = Carbon::today(); // تاريخ اليوم
       // $todayDatetime = strtotime($todayDate);
        foreach ($offers as $of) {
            $periodOfferDate = Carbon::parse($of->period_offer); // تحويل تنسيق تاريخ period_offer
            if ($periodOfferDate <= $todayDate) {
                $of->delete();
    }
        }

    }
}
