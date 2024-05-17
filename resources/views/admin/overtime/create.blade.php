@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @include('layouts.alert')
    <form class="card p-3 rounded-lg" method="POST" action="{{ route('overtimes.store') }}">
        @csrf
        <div class="form-group row">
            <label for="inputFromTime" class="col-sm-2 col-form-label">{{ __('layouts.from_time') }} <code>*</code></label>
            <div class="col-sm-10 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                </div>
                <input type="text" class="form-control date-time @error('from_time') is-invalid @enderror" id="inputFromTime"
                       placeholder="yyyy/mm/dd --:--" name="from_time" value="{{ old('from_time') }}" autocomplete="off">
                @error('from_time')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="inputToTime" class="col-sm-2 col-form-label">{{ __('layouts.to_time') }} <code>*</code></label>
            <div class="col-sm-10 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                </div>
                <input type="text" class="form-control date-time @error('to_time') is-invalid @enderror" id="inputToTime"
                       placeholder="yyyy/mm/dd --:--" name="to_time" value="{{ old('to_time') }}" autocomplete="off">
                @error('to_time')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="inputProject" class="col-sm-2 col-form-label">{{ __('layouts.project') }} <code>*</code></label>
            <div class="col-sm-10">
                <input type="text" class="form-control @error('project') is-invalid @enderror" id="inputProject" name="project" value="{{ old('project') }}">
                @error('project')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="inputReason" class="col-sm-2 col-form-label">{{ __('layouts.reason') }} <code>*</code></label>
            <div class="col-sm-10">
                <textarea class="form-control @error('reason') is-invalid @enderror" id="inputReason" rows="3" name="reason">{{ old('reason') }}</textarea>
                @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="inputMembers" class="col-sm-2 col-form-label">{{ __('layouts.members') }}<code>*</code></label>
            <div class="col-sm-10">
                <div class="table-responsive border rounded @error('members') is-invalid @enderror">
                    <table class="table table-borderless table-striped m-0 table-striped-reverse">
                        <tbody>
                            @foreach ($members as $m)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $m->staff_id }}</td>
                                <td class="text-left">{{ get_fullname($m) }}</td>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input @error('members') is-invalid @enderror" id="customCheck{{ $m->id }}"
                                               name="members[]" value="{{ $m->id }}" @if(old('members') && in_array($m->id, old('members'))) checked @endif">
                                        <label class="custom-control-label" for="customCheck{{ $m->id }}"></label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('members')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <input class="btn btn-primary btn-width-default" type="submit" value="{{ __('layouts.create') }}">
            <a class="btn btn-dark ml-2 btn-width-default" href="{{ Session::has('backUrl') ? Session::get('backUrl') : route('overtimes.index') }}">{{ __('layouts.close') }}</a>
        </div>
    </form>
</div>
@endsection
