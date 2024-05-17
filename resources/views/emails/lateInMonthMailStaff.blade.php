@extends('layouts.app')

@php
$startTime = \Carbon\Carbon::parse($obj->startTime)->format('Y/m/d');
$endTime = \Carbon\Carbon::parse($obj->endTime)->format('Y/m/d');
$month = \Carbon\Carbon::parse($obj->startTime)->month;
$staff = $obj->staff;
$listMonth = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ __('layouts.number_of_late_times') }} {{ $listMonth[$month-1] }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <h1 style="text-align: center;">{{ __('layouts.reminder_checkin_out_late') }} {{ $listMonth[$month-1] }}</h1>
    <div style="width: 50%; margin: auto;">
        <h3>{{ __('layouts.from') }} {{$startTime}} {{ __('layouts.to') }} {{$endTime}}</h3>
        <h3>{{ __('layouts.staff_id') }}: {{$staff->staff_id}}</h3>
        <h3>{{ __('layouts.full_name') }} {{$staff->first_name}} {{$staff->last_name}}</h3>
        @if ( $staff->late_times > 0 )
        <h4>{{ __('layouts.late_check_in_out_times') }} {{ $staff->late_times }} </h4>
        <table style="width: 100%; border: 1px solid; text-align: center;">
            <tr>
                <th style="border: 1px solid;">{{ __('layouts.index') }}</th>
                <th style="border: 1px solid;">{{ __('layouts.date') }}</th>
                <th style="border: 1px solid;">{{ __('layouts.check_in') }}</th>
                <th style="border: 1px solid;">{{ __('layouts.check_out') }}</th>
            </tr>
            @foreach ($staff->timesheets as $t)
            <tr>
                @php
                $index = $loop->iteration;
                $date = $t->check_in ?? $t->check_out ?? null;
                $checkDate = isset($date) ? \Carbon\Carbon::parse($date)->format('Y-m-d') : 'NA';
                $checkIn = isset($t->check_in) ? \Carbon\Carbon::parse($t->check_in)->format('H:i') : 'NA';
                $checkOut = isset($t->check_out) ? \Carbon\Carbon::parse($t->check_out)->format('H:i') : 'NA';
                @endphp
                <td style="border: 1px solid;">{{ $index }}</td>
                <td style="padding: 0 10px 0 10px; border: 1px solid;">{{ $checkDate }}</td>
                <td style="border: 1px solid;">{{ $checkIn }}</td>
                <td style="border: 1px solid;">{{ $checkOut }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        <p>{{ __('layouts.view_detail') }} <span><a href="{{ config('app.url') }}/timesheet/?ac=1&fromdate={{ $startTime }}&todate={{ $endTime }}" target="__blank">{{ __('layouts.click_here') }}</a></span></p>
    </div>
    <footer style="text-align: center; padding: 20px;">
        <a href="https://neoscorp.vn/vi" target="__blank">
            <img src="https://neoscorp.vn/users/vi/img/logo.png" alt="logo">
        </a>
    </footer>
</body>

</html>
