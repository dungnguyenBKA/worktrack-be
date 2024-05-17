<?php

namespace App\Http\Requests\Cms;

class UpdateUserRequest extends CmsBaseRequest
{
    public function rules()
    {
        $isAdmin = $this->userCan('admin');

        $vaildation = [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'birth' => 'nullable|date|date_format:Y-m-d|before:today',
            'address' => 'nullable|string',
            'phone_number' => ['nullable', 'regex:/^[+\-\(\)0-9]+$/', 'max:20'],
        ];

        if($isAdmin) {
            $vaildation['position_id'] = 'required|numeric';
            $vaildation['role'] = 'required|numeric';
            $vaildation['status'] = 'required|numeric';
            $vaildation['date_start_work'] = 'required|date|date_format:Y-m-d';
            $vaildation['paid_leave_start_date'] = 'required|date|date_format:Y-m';
            $vaildation['timesheet_machine_id'] = 'nullable|string|max:50';
            $vaildation['face_id'] = 'nullable|string';
            $vaildation['gps'] = 'nullable|string';
            $vaildation['staff_id'] = 'required|string|unique:users,staff_id,'. $this->user->id .',id,deleted_at,NULL|max:9999';
            $vaildation['email'] = 'required|string|email|unique:users,email,'. $this->user->id .',id,deleted_at,NULL|max:80';

        }

        return $vaildation;
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

        return $parameters;
    }
}
