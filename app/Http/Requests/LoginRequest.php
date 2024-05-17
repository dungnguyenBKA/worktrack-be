<?php

namespace App\Http\Requests;

class LoginRequest extends BaseRequest
{
    public function rules()
    {
        $this->errorCode = 'E0004';
        return [
            'email' => 'required|max:255|email',
            'password' => 'required|string|min:6',
            'platform' => 'nullable|integer|in:1,2',
        ];
    }
}
