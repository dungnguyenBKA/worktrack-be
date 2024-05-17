<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\OvertimeUser;
use App\Models\Overtime;
use Illuminate\Support\Carbon;

class ExistOvertime implements Rule
{

    public function __construct($members, $toTime, $id = false)
    {
        $this->members = $members ?? [];
        $this->toTime = $toTime;
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        
        $fromTime = Carbon::parse($value)->format('Y-m-d H:i:s');
        $toTime = Carbon::parse($this->toTime)->format('Y-m-d H:i:s');

        $query = OvertimeUser::query()->with('overtime');
        $query->whereIn('overtime_user.user_id', $this->members);
        $query->whereHas('overtime', function($query) use ($fromTime, $toTime) {
            $query->where('overtime.status', '!=', Overtime::STATUS_REJECT);
            $query->where('overtime.id', '!=', $this->id);
            $query->where(function ($query) use ($fromTime, $toTime) {
                $query->orWhere(function ($query) use ($fromTime) {
                    $query->where('overtime.from_time', '<=', $fromTime);
                    $query->where('overtime.to_time', '>=', $fromTime);
                });
                $query->orWhere(function ($query) use ($toTime) {
                    $query->where('overtime.from_time', '<=', $toTime);
                    $query->where('overtime.to_time', '>=', $toTime);
                });
                $query->orWhere(function ($query) use ($fromTime, $toTime) {
                    $query->where('overtime.from_time', '>=', $fromTime);
                    $query->where('overtime.to_time', '<=', $toTime);
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
        return __('messages.overtime_date_already_exists');
    }
}