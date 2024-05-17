<?php

namespace App\Console;

use App\Console\Commands\MonthlyReportLateCommand;
use App\Console\Commands\WeeklyReportLateCommand;
use App\Models\ReportConfig;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;
use App\Http\Remotes\CrmRemote;


use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        WeeklyReportLateCommand::class,
        MonthlyReportLateCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $crmRemote = new CrmRemote('call api to crm');
        $subDomains = $crmRemote->getAllSubDomain();
        $subDomains = $subDomains['data'] ?? [];
        // Loop all subDomain companies and send report fop each
        foreach ($subDomains as $subDomain) {            
            $subDomainDb = env('DB_PREFIX') . $subDomain;            
            $config = Config::get('database.connections.mysql');
            $config['database'] = $subDomainDb;
            Config::set('database.connections.mysql', $config);
            // reset DB connection
            app('db')->purge('mysql');

            $reportMailConfig = ReportConfig::first();                        
            $reportCronTime = $this->convertCronTime($reportMailConfig);            
            // setup schedule as cron task to send report later (seperately with this session)
            $schedule->command('weekly_report_late:cmd', [$reportCronTime, $subDomainDb])->cron($reportCronTime);            
            $monthlyReportCronTime = $this->convertCronTime();
            $schedule->command('monthly_report_late:cmd', [$monthlyReportCronTime, $subDomainDb])->cron($monthlyReportCronTime);
        }
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

    private function convertCronTime($input = null)
    {
        if (empty($input)) return '0 9 1 * *';
        $parseTime = \Carbon\Carbon::parse($input->time_of_day);
        $minute = $parseTime->minute ?? '*';
        $hour = $parseTime->hour ?? '*';
        $dayOfWeek = !empty($input->day_of_week) ? $input->day_of_week : '*';
        return "$minute $hour * * $dayOfWeek";
    }
}