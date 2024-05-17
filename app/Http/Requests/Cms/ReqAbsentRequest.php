<?php

namespace App\Http\Requests\Cms;

use App\Rules\ExistRequestAbsent;
use Illuminate\Support\Facades\Session;

class ReqAbsentRequest extends CmsBaseRequest
{
    public function rules()
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }
        $requestAbsent = $this->route('request_absent');

        return [
            'from_time' => ['bail', 'required','date',
                new ExistRequestAbsent(\Request::instance()->to_time, auth()->id(),
                    $requestAbsent ?? false)],
            'to_time' => 'required|date|after_or_equal:from_time',
            'reason' => 'required|string',
            'use_leave_hour' => 'nullable'
        ];
    }

    public function transferredData()
    {
        $parameters = $this->validated();

        $parameters['use_leave_hour'] = isset($parameters['use_leave_hour']) ? 1 : 0;
        $parameters['created_by'] = $this->user()->id;

        return $parameters;

}
}
