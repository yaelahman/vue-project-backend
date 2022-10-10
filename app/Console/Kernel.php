<?php

namespace App\Console;

use App\Models\Permit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {

            $permit = Permit::whereDate('created_at', '<', date('Y-m-d'))->get();
            foreach ($permit as $row) {
                $update = Permit::find($row->id_permit_application);
                $update->permit_status = 3;
                $update->save();
            }
        })->daily();
    }
}
