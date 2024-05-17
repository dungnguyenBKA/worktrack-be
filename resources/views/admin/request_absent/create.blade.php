@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @include('layouts.alert')
    <form class="card p-3 rounded-lg" method="POST" action="{{ route('request-absent.store') }}">
        @csrf
        <div class="form-group row">
            <label for="inputFromTime" class="col-sm-2 col-form-label">{{ __('layouts.from_time') }}
                <code>*</code></label>
            <div class="col-sm-10 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                </div>
                <input type="text" class="form-control date-time @error('from_time') is-invalid @enderror" id="inputFromTime"
                       placeholder="yyyy/mm/dd --:--" name="from_time" value="{{ get_datetime(old('from_time', date('Y-m-d '.$config->start)), 'Y-m-d H:i') }}" autocomplete="off">
                @error('from_time')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="inputToTime" class="col-sm-2 col-form-label">{{ __('layouts.to_time') }}
                <code>*</code></label>
            <div class="col-sm-10 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                </div>
                <input type="text" class="form-control date-time @error('to_time') is-invalid @enderror" id="inputToTime"
                       placeholder="yyyy/mm/dd --:--" name="to_time" value="{{ get_datetime(old('to_time', date('Y-m-d '.$config->end)), 'Y-m-d H:i') }}" autocomplete="off">
                @error('to_time')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="inputReason" class="col-sm-2 col-form-label">{{ __('layouts.reason') }}
                <code>*</code></label>
            <div class="col-sm-10">
                <textarea class="form-control @error('reason') is-invalid @enderror" id="inputReason" rows="3" name="reason">{{ old('reason') }}</textarea>
                @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-2"></div>
            <div class="col-sm-10">
                <input type="checkbox" name="use_leave_hour" id="inputUseLeaveHour" @if(empty(old()) || old('use_leave_hour') == 'on') checked @endif>
                <label for="inputUseLeaveHour">{{ __('layouts.use_leave_hour') }}</label>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <input class="btn btn-primary btn-width-default" type="submit" value="{{ __('layouts.create') }}">
            <a class="btn btn-dark ml-2 btn-width-default" href="{{ Session::has('backUrl') ? Session::get('backUrl') : route('request-absent.index') }}">{{ __('layouts.close') }}</a>
        </div>
    </form>
</div>
@endsection
