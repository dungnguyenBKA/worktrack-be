<?php

namespace App\Http\Requests\Cms;

use App\Rules\ExistOvertime;
use App\Rules\ExistArrayUser;
use App\Rules\OvertimeRequest;
use Illuminate\Support\Facades\Session;

class UpdateOvertimeRequest extends CmsBaseRequest
{
    public function rules()
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }
        if (preg_match('/approve/', url()->previous(), $match)){
            return [
                'reason2' => 'nullable|string',
                'status' => 'nullable|numeric',
            ];
        }else{
            $overtime = $this->route('overtime');
            return [
                'from_time' => ['bail', 'required', 'date',
                    new ExistOvertime(\Request::instance()->members, \Request::instance()->to_time, $overtime->id)],
                //'to_time' => 'required|date|after_or_equal:from_time',
                'to_time' => ['required','date', 'after_or_equal:from_time', new OvertimeRequest(\Request::instance()->from_time, \Request::instance()->to_time)],
                'project' => 'required|string',
                'reason' => 'required|string',
                'members' => ['bail', 'required', 'array',
                    new ExistArrayUser(\Request::instance()->members)],
            ];
        }
    }

    public function transferredData()
    {
        return $this->validated();
    }
}
