<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Timesheet;
use Illuminate\Support\Carbon;

class ExistCheckIn implements Rule
{

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function passes($attribute, $value)
    {
        $checkIn = Carbon::parse($value)->format('Y-m-d');
        $timeSheet = Timesheet::query()->where('user_id', '=', $this->userId)
                ->whereDate('check_in', $checkIn)->first();
        if ($timeSheet) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('messages.checkin_date_already_exists');
    }
}