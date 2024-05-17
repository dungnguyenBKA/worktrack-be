@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @include('layouts.alert')
    <div class="p-3 rounded-lg card">
        <div class="form-group row">
            <label for="inputRegister" class="col-sm-2 col-form-label">{{ __('layouts.registration_applicant') }}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="inputRegister" value="{{ get_fullname($overtime) }}" disabled>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputFromTime" class="col-sm-2 col-form-label">{{ __('layouts.from_time') }}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="inputFromTime" value="{{ $overtime->from_time }}" disabled>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputToTime" class="col-sm-2 col-form-label">{{ __('layouts.to_time') }}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="inputToTime" value="{{ $overtime->to_time }}" disabled>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputProject" class="col-sm-2 col-form-label">{{ __('layouts.project') }}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="inputProject" value="{{ $overtime->project }}" disabled>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputReason" class="col-sm-2 col-form-label">{{ __('layouts.reason') }}</label>
            <div class="col-sm-10">
                <textarea class="form-control" id="inputReason" disabled>{{ $overtime->reason }}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputMembers" class="col-sm-2 col-form-label">{{ __('layouts.ot_members') }}</label>
            <div class="col-sm-10">
                <table class="table table-bordered text-center table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="w-25">ID</th>
                            <th>{{ __('layouts.name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($overtime->overtimeUsers) == 0)
                        <tr>
                            <td class="text-danger no-data" colspan="2">{{ __('messages.no_data') }}</td>
                        </tr>
                        @endif
                        @foreach($overtime->overtimeUsers as $u)
                        <tr>
                            <td>{{ $u->staff_id }}</td>
                            <td>{{ get_fullname($u) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <form method="POST" action="{{ route('overtimes.update', $overtime->id) }}">
            @csrf <input name="_method" value="PUT" hidden>
            <div class="form-group row">
                <label for="inputComment" class="col-sm-2 col-form-label">{{ __('layouts.comment') }}</label>
                <div class="col-sm-10">
                    <textarea @roles('user') disabled @endroles class="form-control" id="inputComment" name="reason2">{{ $overtime->reason2 }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                @can('update', $overtime)
                <button class="btn btn-success btn-width-default" name="status" value="{{ config('common.overtime.approve') }}">{{ __('layouts.approve') }}</button>
                <button class="btn btn-danger ml-2 btn-width-default" name="status" value="{{ config('common.overtime.reject') }}">{{ __('layouts.reject') }}</button>
                @endcannot
                <a class="btn btn-dark ml-2 btn-width-default" href="{{ Session::has('backUrl') ? Session::get('backUrl') : route('overtimes.index') }}">{{ __('layouts.close') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
