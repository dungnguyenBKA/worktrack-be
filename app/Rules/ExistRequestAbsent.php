<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\RequestAbsent;
use Illuminate\Support\Carbon;

class ExistRequestAbsent implements Rule
{

    public function __construct($toDate, $userId, $requestAbsent = false)
    {
        $this->toDate = $toDate;
        $this->requestAbsent = $requestAbsent;
        $this->userId = $userId;
    }

    public function passes($attribute, $value)
    {
        $fromDate = Carbon::parse($value)->format('Y-m-d H:i:s');
        $toDate = Carbon::parse($this->toDate)->format('Y-m-d H:i:s');

        $query = RequestAbsent::query();
        if($this->requestAbsent) {
            $query->where('id', '!=', $this->requestAbsent->id);
            $query->where('user_id', '=', $this->requestAbsent->user_id);
        } else {
            $query->where('user_id', '=', $this->userId);
        }
        $query->where(function ($query) use ($fromDate, $toDate) {
            $query->orWhere(function ($query) use ($fromDate) {
                $query->where('from_time', '<=', $fromDate);
                $query->where('to_time', '>', $fromDate);
            });
            $query->orWhere(function ($query) use ($toDate) {
                $query->where('from_time', '<', $toDate);
                $query->where('to_time', '>=', $toDate);
            });
            $query->orWhere(function ($query) use ($fromDate, $toDate) {
                $query->where('from_time', '>=', $fromDate);
                $query->where('to_time', '<=', $toDate);
            });
        });
        $query->where('status', '<>', config('common.request_absent.reject'));
        if($query->first()) {
            return false;
        }
        return true;
    }

    public function message()
    {
        return __('messages.absense_date_already_exists');
    }
}
