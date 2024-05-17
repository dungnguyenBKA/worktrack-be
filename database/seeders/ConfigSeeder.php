<?php

namespace Database\Seeders;

use App\Models\ReportConfig;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $config = (object) [];
        $config->start = "08:30";
        $config->start_morning_late = "08:31";
        $config->end_morning = "12:00";
        $config->start_afternoon = "13:30";
        $config->start_afternoon_late = "13:31";
        $config->end = "18:00";
        $config->offset_time = "18:00";
        $config->work_days = '["1","2","3","4","5","6"]';

        $config->start_normal_OT = "19:00";
        $config->start_night_OT = "22:00";
        $config->end_night_OT = "24:00";

        ReportConfig::create(
            json_decode(json_encode($config), true)
        );
    }
}
