<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\NationalDay;
use Illuminate\Support\Carbon;

class ExistNationalHoliday implements Rule
{

    public function __construct($toDate, $id = false)
    {
        $this->toDate = $toDate;
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        $fromDate = Carbon::parse($value)->format('Y-m-d');
        $toDate = Carbon::parse($this->toDate)->format('Y-m-d');

        $query = NationalDay::query();
        $query->where(function($query) use ($fromDate, $toDate) {
            $query->where('id', '!=', $this->id);
            $query->where(function ($query) use ($fromDate, $toDate) {
                $query->orWhere(function ($query) use ($fromDate) {
                    $query->where('from_date', '<=', $fromDate);
                    $query->where('to_date', '>=', $fromDate);
                });
                $query->orWhere(function ($query) use ($toDate) {
                    $query->where('from_date', '<=', $toDate);
                    $query->where('to_date', '>=', $toDate);
                });
                $query->orWhere(function ($query) use ($fromDate, $toDate) {
                    $query->where('from_date', '>=', $fromDate);
                    $query->where('to_date', '<=', $toDate);
                });
            });
        });
        if($query->first()) {
            return false;
        }
        return true;
    }

    public function message()
    {
        return __('messages.holiday_date_already_exists');
    }
}