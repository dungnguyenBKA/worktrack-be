@extends('layouts.app')

@php
$month = $obj->month;
$staffs = $obj->staffs;
$startTime = \Carbon\Carbon::parse($obj->startTime)->format('Y/m/d');
$endTime = \Carbon\Carbon::parse($obj->endTime)->format('Y/m/d');
$listMonth = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ __('layouts.list_of_check_in_out_late_in_month') }} {{ $listMonth[$month-1] }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <h1 style="text-align: center;">{{ __('layouts.report_checkin_out_late') }}</h1>
    <div style="padding-bottom: 50px; width: 50%; margin: auto;">
        <h3>{{ __('layouts.from') }} {{$startTime}} {{ __('layouts.to') }} {{$endTime}}</h3>
        @if ( count($staffs) == 0 )
        <h2>{{ __('layouts.congratulation') }}</h2>
        <h3>{{ __('layouts.no_member_late') }}</h3>
        @else
        <h3>{{ __('layouts.check_in_out_late_list') }}</h3>
        <table style="border: 1px solid; margin: auto; width: 100%;">
            <tr>
                <th style="border: 1px solid;">{{ __('layouts.index') }}</th>
                <th style="border: 1px solid;">{{ __('layouts.full_name') }}</th>
                <th style="border: 1px solid;">{{ __('layouts.times') }}</th>
            </tr>
            @foreach ($staffs as $staff)
            <tr>
                <td style="border: 1px solid; text-align: center;">{{ $loop->iteration }}</td>
                <td style="padding: 0 10px 0 10px; border: 1px solid;">{{ $staff->first_name }} {{ $staff->last_name }}</td>
                <td style="border: 1px solid; text-align: center;">{{ $staff->late_times }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    <div style="padding-bottom: 50px; width: 50%; margin: auto;">
        <h3>{{ __('layouts.detail') }}</h3>
        @foreach ($staffs as $staff)
        <h4>{{ $loop->iteration }}. {{$staff->first_name}} {{$staff->last_name}}</h4>
        <table style="border: 1px solid; margin: auto; width: 100%; text-align: center;">
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
        @endforeach
        @endif
    </div>
    <footer style="text-align: center; padding: 20px;">
        <a href="https://neoscorp.vn/vi" target="__blank">
            <img src="https://neoscorp.vn/users/vi/img/logo.png" alt="logo">
        </a>
    </footer>

</body>

</html>
