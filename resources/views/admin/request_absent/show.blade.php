@extends('layouts.app')

@section('content')
    <div class="container-fluid pb-3">
        @include('layouts.alert')
        <div class="p-3 rounded-lg card">
            <div class="form-group row">
                <label for="inputRegister" class="col-sm-2 col-form-label">{{ __('layouts.registration_applicant') }}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputRegister" value="{{ get_fullname($requestAbsent->user) }}" disabled>
                </div>
            </div>
            <div class="form-group row">
                <label for="inputFromTime" class="col-sm-2 col-form-label">{{ __('layouts.from_time') }}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputFromTime" value="{{ $requestAbsent->from_time }}" disabled>
                </div>
            </div>
            <div class="form-group row">
                <label for="inputToTime" class="col-sm-2 col-form-label">{{ __('layouts.to_time') }}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="inputToTime" value="{{ $requestAbsent->to_time }}" disabled>
                </div>
            </div>
            <div class="form-group row">
                <label for="inputReason" class="col-sm-2 col-form-label">{{ __('layouts.reason') }}</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="inputReason" disabled>{{ $requestAbsent->reason }}</textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2"></div>
                <div class="col-sm-10">
                    <input type="checkbox" name="use_leave_hour" id="inputUseLeaveHour" disabled value="1" @if(old('use_leave_hour', $requestAbsent->use_leave_hour)) checked @endif>
                    <label for="inputUseLeaveHour">{{ __('layouts.use_leave_hour') }}</label>
                </div>
            </div>
            <form method="POST" action="{{ route('request-absent.update', $requestAbsent->id) }}">
                @csrf <input name="_method" value="PUT" hidden>
                <div class="form-group row">
                    <label for="inputComment" class="col-sm-2 col-form-label">{{ __('layouts.comment') }}</label>
                    <div class="col-sm-10">
                        <textarea @roles('user') disabled @endroles class="form-control" id="inputComment" name="reason2">{{ $requestAbsent->reason2 }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    @can('approve', $requestAbsent)
                        <button class="btn btn-success btn-width-default" name="status" value="{{ config('common.request_absent.approve') }}">{{ __('layouts.approve') }}</button>
                        <button class="btn btn-danger ml-2 btn-width-default" name="status" value="{{ config('common.request_absent.reject') }}">{{ __('layouts.reject') }}</button>
                    @endcannot
                    <a class="btn btn-dark ml-2 btn-width-default" href="{{ Session::has('backUrl') ? Session::get('backUrl') : route('request-absent.index') }}">{{ __('layouts.close') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
