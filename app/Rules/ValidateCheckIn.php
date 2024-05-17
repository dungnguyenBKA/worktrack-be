<?php

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class ValidateCheckIn implements Rule
{

    public function __construct($checkIn)
    {
        $this->checkIn = $checkIn;
    }

    public function passes($attribute, $value)
    {
        $checkOut = Carbon::parse($value)->format('Y-m-d');
        $checkIn = Carbon::parse($this->checkIn)->format('Y-m-d');
        if ($checkOut != $checkIn) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('messages.checkout_must_equal_checkin_date');
    }
}
