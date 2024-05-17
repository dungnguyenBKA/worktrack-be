<?php

namespace App\Console\Commands;

use App\Actions\TimesheetAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;


class WeeklyReportLateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly_report_late:cmd {cron_time} {sub_domain_db}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send late to staff and manager on weekly!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TimesheetAction $action)
    {
        $cronTime = $this->argument('cron_time');
        $subDomainDB = $this->argument('sub_domain_db');
        $config = Config::get('database.connections.mysql');
        $config['database'] = $subDomainDB;
        Config::set('database.connections.mysql', $config);
        app('db')->purge('mysql');

        $startTime = now()->startOfWeek();
        $endTime = now()->endOfWeek();
        $action->sendLateReportMail($startTime, $endTime);
        Log::info("$cronTime: Weekly Report Late Command Run successfully!");

        // Todo: Do we need to reset to default DB connection here after done? 
        // Maybe no because app re-get subdomain DB for every request

    }

}