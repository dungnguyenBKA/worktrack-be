<?php

namespace App\Http\Requests;

class RegisterRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'staff_id' => 'required|string',
            'email' => 'required|string|email|unique:users|max:80',
            'password' => ['required', 'min:8', 'regex:/^[A-Za-z\d!"#$%&\'()=~\-|^\\@\[;:\],.\/`{}+*>?_]{8,}$/'],
            'position_id' => 'required|numeric',
            'role' => 'required|numeric',
            'status' => 'required|numeric',
            'created_by' => 'required|numeric',
            'timesheet_machine_id' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'birth' => 'nullable|date',
            'address' => 'nullable|string',
            'date_start_work' => 'nullable|date',
            'face_id' => 'nullable|string',
            'gps' => 'nullable|string',
        ];
    }

    public function transferredData()
    {
        $parameters = $this->validated();
        $parameters['password'] = bcrypt($parameters['password']);

        return $parameters;
    }
}
