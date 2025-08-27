<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notifications;
use Carbon\Carbon;

class DeleteOldReadNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:delete-old-read';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications that have been read for more than a week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = Carbon::now()->subWeek(); // قبل أسبوع من الآن

        $oldNotifications = Notifications::where('is_read', true)
            ->where('updated_at', '<=', $date)
            ->get();

        if ($oldNotifications->isEmpty()) {
            $this->info('No old read notifications found.');
            return;
        }

        $count = $oldNotifications->count();
        Notifications::whereIn('id', $oldNotifications->pluck('id'))->delete();

        $this->info("Deleted {$count} old read notifications.");
    }
}
