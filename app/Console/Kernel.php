<?php

namespace App\Console;

use App\Helpers\StatsHelper;
use App\Stats;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
            $employeeHoursByMonth = StatsHelper::employeeHoursByMonth();
            $workerHoursByMonth = StatsHelper::workerHoursByMonth();
            $workerTotalNumbers = StatsHelper::workerTotalNumbers();
            Stats::put('employeeHoursByMonth', $employeeHoursByMonth);
            Stats::put('workerHoursByMonth', $workerHoursByMonth);
            Stats::put('workerTotalNumbers', $workerTotalNumbers);
            Stats::put('lastCronJob', new \DateTime());
        })->hourly();

        $schedule->call(function () {
            $files = glob(storage_path() . "/pdfs/*");
            var_dump($files);
            foreach ($files as $file) {
                if (is_file($file))
                    unlink($file);
            }
        })->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected $middlewareGroups = [
        'throttle' => ['throttle:99,1']
    ];
}
