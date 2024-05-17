<?php

namespace App\Http\Requests;

class GetTimesheetRequest extends BaseRequest
{
    public function rules()
    {
        $this->errorCode = 'E0005';
        return [
            'from_time' => 'nullable|date',
            'to_time' => 'nullable|date',            
            'user_id' => 'nullable|string',
        ];
    }
}
