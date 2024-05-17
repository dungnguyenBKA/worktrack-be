<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Gate;

class CmsBaseRequest extends FormRequest
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

//    protected function failedValidation(Validator $validator)
//    {
//        return redirect()->back()->withErrors($validator);
//    }
    
    public function userCan($action, $user = null, $option = null)
    {
        $user = $user ?? auth()->user();
        return Gate::forUser($user)->allows($action, $option);
    }
}
