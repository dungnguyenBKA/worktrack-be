<?php

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class ValidateNightOvertime implements Rule
{
    public $morningStartTime;
    public $nightOTStartTime;

    public function __construct($morningStartTime, $nightOTStartTime)
    {
        $this->morningStartTime = $morningStartTime;
        $this->nightOTStartTime = $nightOTStartTime;
    }

    public function passes($attribute, $value)
    {
        $endNightOTTime = Carbon::parse($value)->format('H:i');

        if (($endNightOTTime > $this->morningStartTime) && ($endNightOTTime < $this->nightOTStartTime)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('messages.end_night_OT_must_not_between_working_time');
    }
}
