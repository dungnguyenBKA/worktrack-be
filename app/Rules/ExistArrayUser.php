<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class ExistArrayUser implements Rule
{

    public function __construct($userIds)
    {
        $this->userIds = $userIds ?? [];
    }

    public function passes($attribute, $value)
    {
        $exist = true;
        if( ! is_array($this->userIds) ) {
            $exist = false;
        }
        
        foreach ($this->userIds as $userId) {
            if( ! User::find($userId) ) {
                $exist = false;
            }
        }

        return $exist;
    }

    public function message()
    {
        return __('messages.member_not_exists');
    }
}