<?php

namespace App\Console\Commands;

use App\Actions\TimesheetAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MonthlyReportLateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly_report_late:cmd {cron_time} {sub_domain_db}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send late to staff and manager on monthly!';

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

        $startTime = now()->subMonths()->startOfMonth();
        $endTime = now()->subMonths()->endOfMonth();
        $action->sendLateReportMail($startTime, $endTime);
        info("$cronTime: Monthly Report Late Command Run successfully!");
    }
}