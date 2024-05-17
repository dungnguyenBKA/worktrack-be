<?php

namespace App\Http\Requests\Cms;

class StoreUserRequest extends CmsBaseRequest
{
    public function rules()
    {
        return [
            'staff_id' => 'required|numeric|max:9999|unique:users',
            'email' => 'required|string|email|unique:users,email,NULL,id,deleted_at,NULL|max:50',
            'password' => ['required', 'min:8', 'regex:/^[A-Za-z\d!"#$%&\'()=~\-|^\\@\[;:\],.\/`{}+*>?_]{8,}$/'],
            'position_id' => 'required|numeric',
            'role' => 'required|numeric',
            'status' => 'required|numeric',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'birth' => 'nullable|date|date_format:Y-m-d|before:today',
            'address' => 'nullable|string',
            'phone_number' => ['nullable', 'regex:/^[+\-\(\)0-9]+$/', 'max:20'],
            'date_start_work' => 'required|date|date_format:Y-m-d',
            'paid_leave_start_date' => 'required|date|date_format:Y-m',
            'face_id' => 'nullable|string',
            'gps' => 'nullable|string',
            'timesheet_machine_id' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'phone_number.regex' => 'The phone number must be a number and only characters ()+- are accepted'
        ];
    }

    public function transferredData()
    {
        $parameters = $this->validated();
        $parameters['password'] = bcrypt($parameters['password']);
        $parameters['created_by'] = auth()->id();

        return $parameters;
    }
}
