<?php

namespace App\Http\Requests\Cms;

class ChangePasswordRequest extends CmsBaseRequest
{
    public function rules()
    {
        return [
            'password_current' => 'required|string',
            'password_new' => ['required','string', 'min:8', 'regex:/^[A-Za-z\d!"#$%&\'()=~\-|^\\@\[;:\],.\/`{}+*>?_]{8,}$/'],
            'password_confirmation' => ['required','string', 'min:8', 'regex:/^[A-Za-z\d!"#$%&\'()=~\-|^\\@\[;:\],.\/`{}+*>?_]{8,}$/'],
        ];
    }
}
