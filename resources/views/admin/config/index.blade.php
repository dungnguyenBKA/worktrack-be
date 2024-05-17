@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @include('layouts.alert')
    <div class="card p-3 rounded-lg">
        <div class="row col-sm-12">
            <h4 class="block-title">{{ __('layouts.work_location') }}</h4>
        </div>
        <table class="table table-striped table-borderless">
            <tr>
                <th class="w-25">{{ __('layouts.position_limit') }}</th>
                <td>{{ $config->position_limit }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.distance_limit') }}</th>
                <td>{{ $config->distance_limit }}</td>
            </tr>
        </table>
    </div>
    <div class="card p-3 rounded-lg">
        <div class="row col-sm-12">
            <h4 class="block-title">{{ __('layouts.late_report') }}</h4>
        </div>
        <table class="table table-striped table-borderless">
            <tr>
                <th>{{ __('layouts.period') }}</th>
                <td>
                    @isset($config->period)
                    {{ __('common.period')[$config->period] }}
                    @endisset
                </td>
            </tr>
            <tr>
                <th>{{ __('layouts.time_of_day') }}</th>
                <td>{{ $config->time_of_day }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.weekly') }}</th>
                <td>
                    @isset($config->day_of_week)
                    {{ __('common.week')[$config->day_of_week] }}
                    @endisset
                </td>
            </tr>
            <tr>
                <th>{{ __('layouts.over_times') }}</th>
                <td>{{ $config->over_times }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.monthly_report') }}</th>
                <td>{{ __('messages.monthly_report') }}</td>
            </tr>
            <tr>
                <th class="w-25">{{ __('layouts.receiver_mails') }}</th>
                <td style="line-height: 45px;">
                    @foreach ($config->getUsers() as $u)
                    <a class="btn btn-outline-dark">{{ $u->email }}</a>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
    <div class="card p-3 rounded-lg">
        <div class="row col-sm-12">
            <h4 class="block-title">{{ __('layouts.network_allows_access') }}</h4>
        </div>
        <table class="table table-striped table-borderless">
            <tr>
                <th class="w-25">{{ __('layouts.white_list_ips') }}</th>
                <td>
                    @php
                        $white_list_ips = str_replace(',', '<br>', $config->white_list_ips);
                    @endphp
                    {!! $white_list_ips !!}
                </td>
            </tr>
        </table>
    </div>
    <div class="card p-3 rounded-lg">
        <div class="row col-sm-12">
            <h4 class="block-title">{{ __('layouts.work_hour') }}</h4>
        </div>
        <table class="table table-striped table-borderless">
            <tr>
                <th class="w-25">{{ __('layouts.work_days') }}</th>
                <td>
                    @foreach($dayOfWeek as $key => $value)
                        @if(in_array($key, json_decode($config->work_days)))
                            <p>{{ $value }}</p>
                        @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <th class="w-25">{{ __('layouts.start_morning') }}</th>
                <td>{{ $config->start ? \Carbon\Carbon::make($config->start)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.end_morning') }}</th>
                <td>{{ $config->end_morning ? \Carbon\Carbon::make($config->end_morning)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.start_afternoon') }}</th>
                <td>{{ $config->start_afternoon ? \Carbon\Carbon::make($config->start_afternoon)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.end_afternoon') }}</th>
                <td>{{ $config->end ? \Carbon\Carbon::make($config->end)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th data-toggle="tooltip" title="{{ __('layouts.offset_time_tooltip') }}">{{ __('layouts.offset_time') }} &#9432;</th>                
                <td>{{ $config->offset_time ? \Carbon\Carbon::make($config->offset_time)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.start_normal_OT') }}</th>
                <td>{{ $config->start_normal_OT ? \Carbon\Carbon::make($config->start_normal_OT)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.start_night_OT') }}</th>
                <td>{{ $config->start_night_OT ? \Carbon\Carbon::make($config->start_night_OT)->format('H:i') : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.end_night_OT') }}</th>
                <td>{{ $config->end_night_OT ? \Carbon\Carbon::make($config->end_night_OT)->format('H:i') : '' }}</td>
            </tr>            
        </table>
        <div class="d-flex justify-content-end">
            <a class="btn btn-dark btn-width-default" href="{{ route('config.edit', $config->id) }}">{{ __('layouts.edit') }}</a>
        </div>
    </div>
</div>
@endsection
