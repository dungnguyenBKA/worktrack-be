@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @isset($user)
    @include('layouts.alert')
    <div class="card p-3 mb-3 rounded-lg">
        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf<input type="hidden" name="_method" value="PUT">
            <div class="form-group row">
                <label for="inputStaffId" class="col-sm-2 col-form-label">{{ __('layouts.staff_id') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('staff_id') is-invalid @enderror" type="text" name="staff_id"
                           value="{{ old('staff_id', $user->staff_id) }}" @roles('user') disabled @endroles id="inputStaffId">
                    @error('staff_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label for="inputFirstName" class="col-sm-2 col-form-label">{{ __('layouts.first_name') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('first_name') is-invalid @enderror" type="text" name="first_name"
                           value="{{ old('first_name', $user->first_name) }}" id="inputFirstName">
                    @error('first_name')
                    <div class="invalid-feedback">{{ $message }} <code>*</code></div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label for="inputLastName" class="col-sm-2 col-form-label">{{ __('layouts.last_name') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('last_name') is-invalid @enderror" type="text" name="last_name"
                           value="{{ old('last_name', $user->last_name) }}" id="inputLastName">
                    @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail" class="col-sm-2 col-form-label">{{ __('layouts.email') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('email') is-invalid @enderror" type="email" name="email"
                           value="{{ old('email', $user->email) }}" @roles('user') disabled @endroles id="inputEmail">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @roles('admin')
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">{{ __('layouts.role') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <div class="@error('role') is-invalid @enderror">
                        @foreach ($roles as $value)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="role{{ $value->name_en }}" name="role" class="custom-control-input" value="{{ $value->id }}"
                                       @if (old('role', $user->role) == $value->id) checked @endif @roles('user') disabled @endroles>
                                <label class="custom-control-label" for="role{{ $value->name_en }}">
                                    @if(config('app.locale') == 'vi' && !empty($value->name_vi))
                                        {{ $value->name_vi }}
                                    @elseif(config('app.locale') == 'ja' && !empty($value->name_ja))
                                        {{ $value->name_ja }}
                                    @else
                                        {{ $value->name_en }}
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">{{ __('layouts.status') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <div class="@error('status') is-invalid @enderror">
                        @foreach (__('common.user.status') as $key => $value)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status{{ $value }}" name="status" class="custom-control-input" value="{{ $key }}"
                                       @if (old('status', $user->status) == $key) checked @endif @roles('user') disabled @endroles>
                                <label class="custom-control-label" for="status{{ $value }}">{{ $value }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">{{ __('layouts.start_work_from') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input class="form-control date-up @error('date_start_work') is-invalid @enderror" type="text" name="date_start_work"
                           placeholder="yyyy-mm-dd" value="{{ old('date_start_work', $user->date_start_work) }}" autocomplete="off"
                           @roles('user') disabled @endroles>
                    @error('date_start_work')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">{{ __('layouts.paid_leave_start_date') }}
                    <code>*</code>
                </label>
                <div class="col-sm-10 input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input class="form-control date-month @error('paid_leave_start_date') is-invalid @enderror" type="text" name="paid_leave_start_date"
                           placeholder="yyyy-mm" autocomplete="off"
                           value="{{ old('paid_leave_start_date', $user->paid_leave_start_date ? \Illuminate\Support\Carbon::make($user->paid_leave_start_date)->format('Y-m') : '') }}">
                    @error('paid_leave_start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @endroles
            <div class="form-group row">
                <label for="inputBirth" class="col-sm-2 col-form-label">{{ __('layouts.birth') }}</label>
                <div class="col-sm-10 input-group">
                    <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                    </div>
                    <input class="form-control date @error('birth') is-invalid @enderror" type="text" name="birth"
                           placeholder="yyyy-mm-dd" value="{{ old('birth', $user->birth) }}" autocomplete="off" id="inputBirth">
                    @error('birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label for="inputAddress" class="col-sm-2 col-form-label">{{ __('layouts.address') }}</label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('address') is-invalid @enderror" type="text" name="address"
                           value="{{ old('address', $user->address) }}" id="inputAddress">
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label for="inputPhoneNumber" class="col-sm-2 col-form-label">{{ __('layouts.phone_number') }}</label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('phone_number') is-invalid @enderror" type="text" name="phone_number"
                           value="{{ old('phone_number', $user->phone_number) }}" id="inputPhoneNumber">
                    @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @roles('admin')
            <div class="form-group row">
                <label for="inputFaceID" class="col-sm-2 col-form-label">{{ __('layouts.face_id') }}</label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('face_id') is-invalid @enderror" type="text" name="face_id"
                           value="{{ old('face_id', $user->face_id) }}" id="inputFaceID" @roles('user') disabled @endroles>
                    @error('face_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label for="inputTimeSheetMachineID" class="col-sm-2 col-form-label">{{ __('layouts.timesheet_machine_id') }}</label>
                <div class="col-sm-10 input-group">
                    <input class="form-control @error('timesheet_machine_id') is-invalid @enderror" type="text"
                           name="timesheet_machine_id" value="{{ old('timesheet_machine_id', $user->timesheet_machine_id) }}"
                           id="inputTimeSheetMachineID" @roles('user') disabled @endroles>
                    @error('timesheet_machine_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">{{ __('layouts.work_title') }}</label>
                <div class="col-sm-10 input-group">
                    <select class="form-control" name="position_id">
                        @foreach ($workTitles as $value)
                            <option value="{{ $value->id }}" @if (old('position_id', $user->position_id) == $value->id) selected @endif>
                                @if(config('app.locale') == 'vi' && !empty($value->name_vi))
                                    {{ $value->name_vi }}
                                @elseif(config('app.locale') == 'ja' && !empty($value->name_ja))
                                    {{ $value->name_ja }}
                                @else
                                    {{ $value->name_en }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endroles
            <div class="d-flex justify-content-end mb-2">
                <input class="btn btn-primary btn-width-default" type="submit" value="{{ __('layouts.save') }}">
                <a class="btn btn-dark ml-2 btn-width-default" href="{{ route($redirect) }}">{{ __('layouts.close') }}</a>
            </div>
        </form>
        @endisset
    </div>
</div>
@endsection
