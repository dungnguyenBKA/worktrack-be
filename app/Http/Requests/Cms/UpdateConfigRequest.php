<?php

namespace App\Http\Requests\Cms;

use Carbon\Carbon;
use App\Rules\ValidateNightOvertime;

class UpdateConfigRequest extends CmsBaseRequest
{
    public function rules()
    {
        return [
            'period' => 'nullable|numeric',
            'time_of_day' => 'nullable|string',
            'day_of_week' => 'nullable|numeric',
            'over_times' => 'nullable|numeric',
            'selected_ids' => 'nullable|array',
            'position_limit' => 'nullable|numeric',
            'distance_limit' => 'nullable|numeric|min:0',
            'white_list_ips' => ['nullable', 'string', 'regex:/((?(?!^)(,|, ))(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5])).(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5])).(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5])).(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5])))+$/'],
            'start' => 'required|date_format:H:i',
            'end_morning' => 'required|date_format:H:i|after:start',
            'start_afternoon' => 'required|date_format:H:i|after:end_morning',
            'end' => 'required|date_format:H:i|after:start_afternoon',
            'offset_time' => 'nullable|date_format:H:i|after_or_equal:end',
            'work_days' => 'nullable|array',
            'work_days.*' => 'integer',
            'start_normal_OT' => 'required|date_format:H:i|after_or_equal:offset_time',
            'start_night_OT' => 'required|date_format:H:i|after_or_equal:start_normal_OT',
            //'end_night_OT' => 'required|date_format:H:i|after_or_equal:start_night_OT', // Note TODO: should be (start_night_OT <= end_night_OT <= 24:00) OR (0:00 <= end_night_OT <= start)
            'end_night_OT' => ['required' ,'date_format:H:i', 
            new ValidateNightOvertime(\Request::instance()->start, \Request::instance()->start_night_OT)],
        ];
    }

    public function transferredData()
    {
        $parameters = $this->validated();

        if ($parameters['period'] == config('common.report_config.period.day')) {
            $parameters['day_of_week'] = null;
        }

        if (!array_key_exists('selected_ids', $parameters)) {
            $parameters['selected_ids'] = [];
        }

        if(isset($parameters['white_list_ips'])) {
            $parameters['white_list_ips'] = preg_replace('/\s+/', '', $parameters['white_list_ips']);
        }

        $parameters['start_morning_late'] = $parameters['start'] ?
            Carbon::make($parameters['start'])->addMinute(1)->format('H:i') : null;
        $parameters['start_afternoon_late'] = $parameters['start_afternoon'] ?
            Carbon::make($parameters['start_afternoon'])->addMinute(1)->format('H:i') : null;
        $parameters['offset_time'] = $parameters['offset_time'] ?? $parameters['end'];

        $parameters['start_normal_OT'] = $parameters['start_normal_OT'] ?? $parameters['offset_time'];
        $parameters['start_night_OT'] = $parameters['start_night_OT'] ?? $parameters['start_normal_OT'];
        $parameters['end_night_OT'] = $parameters['end_night_OT'] ?? $parameters['start_night_OT'];

        return $parameters;
    }
}
