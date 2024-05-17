<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class BaseRequest extends FormRequest
{
    protected $errorCode;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     if (!isset($this->errorCode)) {
    //         $this->errorCode = 'E0003';
    //     }
    //     $errors = (new ValidationException($validator))->errors();
    //     throw new HttpResponseException(send_error($this->errorCode, $errors));
    // }
}
