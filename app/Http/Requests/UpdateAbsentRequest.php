<?php

namespace App\Http\Requests;

use App\Rules\ExistRequestAbsent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class UpdateAbsentRequest extends FormRequest
{
    public function rules()
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }
        $requestAbsent = $this->route('request_absent');
        if (preg_match('/approve/', url()->previous(), $match)){
            return [
                'reason2' => 'nullable|string',
                'status' => 'required|string',
            ];
        }else{
            return [
                'from_time' => ['bail', 'required','date',
                    new ExistRequestAbsent(\Request::instance()->to_time, auth()->id(),
                        $requestAbsent ?? false)],
                'to_time' => 'required|date|after_or_equal:from_time',
                'reason' => 'required|string',
                'use_leave_hour' => 'nullable'
            ];
        }
    }
}
