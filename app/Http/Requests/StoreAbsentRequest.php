<?php

namespace App\Http\Requests;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use App\Rules\ExistRequestAbsent;


class StoreAbsentRequest extends BaseRequest
{
    public function rules()
    {
        $requestAbsent = $this->route('request_absent');

        return [
            'from_time' => ['bail', 'required','date',
                new ExistRequestAbsent(\Request::instance()->to_time, \Request::instance()->user_id
                //new ExistRequestAbsent(\Request::instance()->to_time, auth()->id()
                , $requestAbsent ?? false)],
                //)],
            'to_time' => 'required|date|after_or_equal:from_time',
            'reason' => 'required|string',
            'use_leave_hour' => 'nullable'
        ];
    }

    public function transferredData()
    {
        $parameters = $this->validated();
        $parameters['use_leave_hour'] = isset($parameters['use_leave_hour']) ? 1 : 0;
        // $parameters['created_by'] = $this->user()->id;

        return $parameters;
    }

    protected function failedValidation(Validator $validator)
    {
        if (!isset($this->errorCode)) {
            $this->errorCode = 'G00050006';
        }
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(send_error($this->errorCode, $errors));
    }
}
