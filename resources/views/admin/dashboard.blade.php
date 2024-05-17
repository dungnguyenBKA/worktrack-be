@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-md-12">
            <div class="card rounded-lg">
                <div class="card-header card-title">{{ __('layouts.search') }}</div>
                <div class="card-body">
                    <form action="{{ route('dashboard') }}" method="GET">
                        <div class="row">
                            <label for="search_date" class="col-form-label ml-4">{{ __('layouts.date') }}</label>
                            <div class="col-xl-4">
                                <input type="text" name="date" class="form-control" id="search_date" autocomplete="off"
                                       value="{{ \Carbon\Carbon::parse($date)->format('Y/m') }}">
                            </div>
                            <div class="col-xl-3 form-group">
                                <button class="btn btn-dark" type="submit">{{ __('layouts.search') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row ">
        <div class="col-md-6">
            <div class="card rounded-lg">
                <div class="card-header card-title">{{ __('layouts.late_stats') }}</div>
                <div class="card-body">
                    <table class="table table-striped table-borderless">
                        <thead class="thead-dark">
                            <tr>
                                <th>{{ __('layouts.staff_id') }}</th>
                                <th>{{ __('layouts.name') }}</th>
                                <th>{{ __('layouts.time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forEach( $timesheets as  $timesheet )
                            <tr>
                                <td>{{ $timesheet['staff_id'] }}</td>
                                <td>{{ $timesheet['full_name'] }}</td>
                                <td>{{ $timesheet['times'] }}</td>
                            </tr>
                            @endforEach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card rounded-lg">
                <div class="card-header card-title">{{ __('layouts.request_absent_month') }}</div>
                <div class="card-body">
                    <table class="table table-striped table-borderless">
                        <thead class="thead-dark">
                            <tr>
                                <th>{{ __('layouts.staff_id') }}</th>
                                <th>{{ __('layouts.name') }}</th>
                                <th>{{ __('layouts.from_time') }}</th>
                                <th>{{ __('layouts.to_time') }}</th>
                                <th>{{ __('layouts.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forEach( $requestAbsents as  $requestAbsent )
                            <tr>
                                <td>{{ $requestAbsent->user->staff_id }}</td>
                                <td>{{ $requestAbsent->user->getFullName() }}</td>
                                <td>{{ get_datetime($requestAbsent->from_time, config('constants.format_datetime_show')) }}</td>
                                <td>{{ get_datetime($requestAbsent->to_time, config('constants.format_datetime_show')) }}</td>
                                <td>
                                    <i class="{{ config('common.status_map.'.$requestAbsent->status.'.icon') }}" data-toggle="tooltip"
                                        title="{{ config('common.status_map.'.$requestAbsent->status.'.text') }}"></i>
                                </td>
                            </tr>
                            @endforEach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card rounded-lg">
                <div class="card-header card-title">{{ __('layouts.happy_birthday') }} </div>
                <div class="card-body">
                    <table class="table table-striped table-borderless">
                        <thead class="thead-dark">
                            <tr>
                                <th>{{ __('layouts.staff_id') }}</th>
                                <th>{{ __('layouts.name') }}</th>
                                <th>{{ __('layouts.time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forEach( $usersBirthday as  $user )
                            <tr>
                                <td>{{ $user->staff_id }}</td>
                                <td>{{ $user->getFullName() }}</td>
                                <td>{{ $user->birth }}</td>
                            </tr>
                            @endforEach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($isAdmin)
            <div class="card rounded-lg">
                <div class="card-header card-title">{{ __('layouts.user_create_new_month') }}</div>
                <div class="card-body">
                    <table class="table table-striped table-borderless">
                        <thead class="thead-dark">
                            <tr>
                                <th>{{ __('layouts.staff_id') }}</th>
                                <th>{{ __('layouts.name') }}</th>
                                <th>{{ __('layouts.start_work_from') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forEach( $newUsers as  $user )
                            <tr>
                                <td>{{ $user->staff_id }}</td>
                                <td>{{ $user->getFullName() }}</td>
                                <td>{{ get_datetime($user->date_start_work, config('constants.format_date_show')) }}</td>
                            </tr>
                            @endforEach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
