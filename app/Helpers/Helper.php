<?php

use Illuminate\Support\Str;

function get_fullname($user = null)
{
    $fullname = '';
    if (!$user) {
        $user = auth()->user();
    }
    if ($user) {
        $fullname = !$user->first_name || !$user->last_name ? $user->email
            : $user->first_name . ' ' . $user->last_name;
    }
    return $fullname;
}

function get_datetime($datetime = '', $format = '')
{
    $datetime = strtotime($datetime);
    if ($datetime){
        if (empty($format)) {
            return str_replace('%%', 'T', date('Y-m-d%%H:i', $datetime));
        }
        return date($format, $datetime);
    }
    return '';
}

function generate_refresh_token()
{
    $refreshTokenLength = rand(200, 250);
    $timeNow = now()->timestamp;
    $refreshToken = Str::random($refreshTokenLength) . '.' . base64_encode($timeNow);
    return $refreshToken;
}

function generate_face_id($identity)
{
    $identity .= now()->timestamp;
    return base64_encode($identity);
}

function send_success($data = null, $message = '')
{
    $statusCode = __('errors.SUCCESS');
    $response['code'] = $statusCode;

    if (!empty($data)) {
        $response['data'] = $data;
    }
    if ($message) {
        $response['message'] = $message;
    }

    return response()->json($response, $statusCode);
}

function send_error($code, $data = null)
{
    if (is_string($data)) {
        logger()->error($data);
    }

    $statusCode = __('errors.' . $code . '.statusCode');
    $response = [
        'code' => __('errors.' . $code . '.code'),
        'message' => __('errors.' . $code . '.message')
    ];

    if (!empty($data)) {
        $response['data'] = $data;
    }

    if (!empty($validations)) {
        $response['validations'] = $validations;
    }

    return response()->json($response, $statusCode);
}

function daysWorkingInMonth($month = false, $year = false) {
    $workdays = 0;
    $type = CAL_GREGORIAN;
    $month = $month ?? date('m'); // Month ID, 1 through to 12.
    $year = $year ?? date('Y'); // Year in 4 digit 2009 format.
    //$day_count = ($month == date('m')) ? date('d')-1 : cal_days_in_month($type, $month, $year);
    $day_count = cal_days_in_month($type, $month, $year);
    $config = \App\Models\ReportConfig::first();

    //loop through all days
    for ($i = 1; $i <= $day_count; $i++) {
        $date = $year.'/'.$month.'/'.$i; //format date
        $dayOfWeek = date('w', strtotime($date)); //get week day

        //if not a weekend add day to array
        if(in_array($dayOfWeek, json_decode($config->work_days))){
            $workdays += 1;
        }
    }

    return $workdays;
}

function hasSubdomain() {
    return false;
    $url = $_SERVER['HTTP_REFERER'] ?? '';
    $parsed = parse_url($url);
    $exploded = explode('.', $parsed["host"]);
    return (count($exploded) > 2);
}
