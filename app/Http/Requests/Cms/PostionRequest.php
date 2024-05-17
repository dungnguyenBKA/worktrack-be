<?php

namespace App\Http\Requests\Cms;

use App\Rules\PositionLimit;
use Illuminate\Support\Facades\Session;

class PostionRequest extends CmsBaseRequest
{
    public function rules()
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }
        $position = $this->route('position');

        $validate = [
            'user_id' => ['nullable', 'integer'],
            'position' => ['required', 'string', 'max:255'],
            'status' => ['required', 'integer', 'in:1,2'],
            'type' => ['required', 'integer', 'in:1,2'],
            'latitude' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/']

        ];

        if(\Request::instance()->type != 1) {
            $validate['user_id'] = ['required', 'integer', 'exists:users,id'];
            $validate['position'] = ['required', 'string', 'max:255', !$position ? new PositionLimit(\Request::instance()->user_id) : ""];
        }

        if($position && \Request::instance()->status != $position->status && \Request::instance()->status == 1) {
            $validate['position'] = ['required', 'string', 'max:255', new PositionLimit(\Request::instance()->user_id)];
        }

        return $validate;
    }
}
