<?php

namespace App\Http\Requests\Cms;

use App\Rules\ExistCheckIn;
use App\Rules\ValidateCheckIn;
use Illuminate\Support\Facades\Session;

class TimeSheetRequest extends CmsBaseRequest
{
    public function rules()
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }
        $timesheet = $this->route('timesheet');
        $requiredUpdate = $timesheet ? 'nullable' : 'required';

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'check_in' => ['bail', 'required', 'date',
                !$timesheet ? new ExistCheckIn(\Request::instance()->user_id) : false],
            'check_out' => ['bail', 'nullable', 'date', 'after_or_equal:check_in',
                new ValidateCheckIn(\Request::instance()->check_in)],
        ];
    }
}
