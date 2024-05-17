<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistNationalHoliday;

class NationalDayRequest extends FormRequest
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
        $nationalHoliday = $this->route('national_day');

        return [
            'name' => 'required',
            'from_date' => ['bail', 'required','date',
                new ExistNationalHoliday(\Request::instance()->to_date,
                    $nationalHoliday ? $nationalHoliday->id : false)],
            'to_date' => 'required|date|after_or_equal:from_date'
        ];
    }

//    public function messages()
//    {
//        return [
//            'required' => config('validate.common.required'),
//            'to_date.after_or_equal' => config('validate.national_day.after_or_equal')
//        ];
//    }
}
