<?php

namespace App\Console;

use App\Models\Permit;
use App\Models\PermitDate;
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

            $permit = Permit::where('permit_status', 0)->get();
            foreach ($permit as $row) {
                if ($row->permit_endclock != null) {
                    if (date('Y-m-d', strtotime($row->permit_endclock)) < date('Y-m-d')) {
                        $update = Permit::find($row->id_permit_application);
                        $update->permit_status = 3;
                        $update->save();
                    }
                } else {
                    $permit_date = PermitDate::where('id_permit_application', $row->id_permit_application)->orderBy('id_permit_date', 'desc')->first();
                    if ($permit_date) {
                        if (date('Y-m-d', strtotime($permit_date->permit_date)) < date('Y-m-d')) {
                            $update = Permit::find($permit_date->id_permit_application);
                            $update->permit_status = 3;
                            $update->save();
                        }
                    }
                }
            }
        })->daily();
    }
}
