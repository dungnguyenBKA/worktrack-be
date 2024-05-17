<?php

namespace App\Http\Requests;

class CheckInOutRequest extends BaseRequest
{
    public function rules()
    {
        $this->errorCode = 'E0005';
        return [
            'face_id' => 'required|string',
            'gps' => 'nullable|string',
        ];
    }
}
