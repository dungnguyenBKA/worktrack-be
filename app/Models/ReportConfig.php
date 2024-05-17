<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ReportConfig extends Model
{
    protected $table = 'report_configs';

    protected $fillable = [
        'period',
        'time_of_day',
        'day_of_week',
        'over_times',
        'selected_ids',
        'position_limit',
        'distance_limit',
        'white_list_ips',
        'start',
        'start_morning_late',
        'end_morning',
        'start_afternoon',
        'start_afternoon_late',
        'end',
        'offset_time',
        'work_days',
        'start_normal_OT',
        'start_night_OT',
        'end_night_OT',
        'maintenance',
    ];

    public function getUsers($isReceiver = true)
    {
        $selectedIDs = json_decode($this->selected_ids) ?? [];

        $query = User::select('id', 'email');
        if ($isReceiver) {
            $query->whereIn('id', $selectedIDs);
        } else {
            $query->whereNotIn('id', $selectedIDs);
        }
        $users = $query->get();
        return $users;
    }

    public static function getDistanceLimit() {
        $config = ReportConfig::query()->first();

        return $config ? $config->distance_limit : 0;
    }

    public static function getWorkHour()
    {
        $config = ReportConfig::first();
        $workHourMorning = (strtotime(Carbon::make($config->end_morning)) - strtotime(Carbon::make($config->start)))/3600;
        $workHourAfternoon = (strtotime(Carbon::make($config->end)) - strtotime(Carbon::make($config->start_afternoon)))/3600;

        return $workHourMorning + $workHourAfternoon;
    }
}
