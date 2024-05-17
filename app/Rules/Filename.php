<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Filename implements Rule
{
    protected $regex = '/^[A-Za-z0-9-_.() ]+$/';

    public function __construct()
    {
        
    }

    public function passes($attribute, $value)
    {
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            return false;
        }

        return preg_match($this->regex, $value->getClientOriginalName()) > 0;
    }

    public function message()
    {
        return __('messages.file_name_error');
    }
}