<?php

namespace App\Http\Requests;

class FaceChecktimeRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'timesheets' => 'required|array',
        ];
    }
}