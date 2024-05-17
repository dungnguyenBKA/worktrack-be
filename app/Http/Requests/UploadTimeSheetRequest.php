<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Filename;

class UploadTimeSheetRequest extends FormRequest
{
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
            'file-timesheet' => ['required', 'file', 'max:10000',
                'mimes:xlsx,xls'],
            'type' => ['required', 'integer', 'in:0,1']
        ];
    }

    public function messages()
    {
        return [
            ];
    }
}
