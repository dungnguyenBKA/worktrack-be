<?php

namespace App\Http\Requests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use App\Rules\ExistOvertime;
use App\Rules\ExistArrayUser;
use App\Rules\OvertimeRequest;

class StoreOvertimeRequest extends BaseRequest
{
    public function rules()
    {
        return [
            //'from_time' => 'required|date',
            'from_time' => ['bail', 'required', 'date',
                new ExistOvertime(\Request::instance()->members, \Request::instance()->to_time)],
            //'to_time' => 'required|date',
            //'to_time' => 'required|date|after_or_equal:from_time',
            'to_time' => ['required','date', 'after_or_equal:from_time', new OvertimeRequest(\Request::instance()->from_time, \Request::instance()->to_time)],
            'project' => 'required|string',
            'reason' => 'required|string',
            //'members' => 'nullable|array',
            'members' => ['bail', 'required', 'array', 
                new ExistArrayUser(\Request::instance()->members)],
        ];
    }

    public function transferredData()
    {
        $parameters = $this->validated();

        // $parameters['created_by'] = $this->user()->id;

        return $parameters;
    }
    protected function failedValidation(Validator $validator)
    {
        if (!isset($this->errorCode)) {
            $this->errorCode = 'G00050007';
        }
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(send_error($this->errorCode, $errors));
    }
}
