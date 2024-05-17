<?php

namespace App\Http\Requests;

class LoginFaceIdRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'face_id' => 'required|string',
        ];
    }
}
