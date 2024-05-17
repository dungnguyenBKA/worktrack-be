<?php

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Models\ReportConfig;

class OvertimeRequest implements Rule
{
    public $fromTime;
    public $toTime;    

    public function __construct($fromTime, $toTime)
    {
        $this->fromTime = $fromTime;
        $this->toTime = $toTime;
        
    }

    public function passes($attribute, $value)
    {        
        //$this->endNightOTTime = Carbon::parse($value)->format('H:i');
        
        $config = ReportConfig::first();
        

        // If Overnight OT
        if (date("Y-m-d", strtotime($this->toTime)) > date("Y-m-d", strtotime($this->fromTime))) {
            $fromPlus1Day = date('Y-m-d', strtotime('+1 day', strtotime($this->fromTime)));
            $to = Carbon::parse($this->toTime)->format('Y-m-d');
            if ($to > $fromPlus1Day) { // If over than next of next day
                return false;
            } else if (date("H:i", strtotime($this->toTime)) > $config->end_night_OT) { 
                // If Totime > nightOTEndTime
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return __('messages.overtime_must_not_over_end_night_time');
    }
}
