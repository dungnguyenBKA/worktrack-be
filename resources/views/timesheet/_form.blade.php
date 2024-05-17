<div class="form-group row">
    <label for="name" class="col-sm-2 col-form-label">{{ __('layouts.staff') }} <code>*</code></label>
    <div class="col-sm-10">
        <select name="user_id" class="form-control select2 select2-hidden-accessible @if($errors->has('user_id')) is-invalid @endif">
            <option value="" >--</option>
            @foreach($users as $user)
                @php
                    $userSelect = old('user_id', isset($timesheet) && $user->id == $timesheet->user_id ? $timesheet->user_id : false);
                @endphp
                <option value="{{ $user->id }}" @if ($userSelect == $user->id) selected @endif>{{ $user->getFullName() }}</option>
            @endforeach
        </select>
        @if($errors->has('user_id'))
            <span class="error invalid-feedback">{{ $errors->first('user_id') }}</span>
        @endif
    </div>
</div>
<div class="form-group row">
    <label for="name" class="col-sm-2 col-form-labelt">{{ __('layouts.check_in') }} <code>*</code></label>
    <div class="input-group col-sm-10">
        <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="far fa-calendar-alt"></i>
            </span>
        </div>
        <input type="text" class="form-control date-time float-right @if($errors->has('check_in')) is-invalid @endif"
               placeholder="yyyy/mm/dd --:--" name="check_in" id="check_in" autocomplete="off"
               value="{{ get_datetime(old('check_in', isset($timesheet) ? $timesheet->check_in : date('Y/m/d '.$config->start)), 'Y-m-d H:i') }}">
        @if($errors->has('check_in'))
            <span class="error invalid-feedback">{{ $errors->first('check_in') }}</span>
        @endif
    </div>
</div>
<div class="form-group row">
    <label for="name" class="col-sm-2 col-form-label">{{ __('layouts.check_out') }}</label>
    <div class="input-group col-sm-10">
        <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="far fa-calendar-alt"></i>
            </span>
        </div>
        <input type="text" class="form-control date-time float-right @if($errors->has('check_out')) is-invalid @endif"
               placeholder="yyyy/mm/dd --:--" name="check_out" id="check_out" autocomplete="off"
               value="{{ get_datetime(old('check_out', isset($timesheet) ? $timesheet->check_out : date('Y/m/d '.$config->end)), 'Y-m-d H:i') }}">
        @if($errors->has('check_out'))
            <span class="error invalid-feedback">{{ $errors->first('check_out') }}</span>
        @endif
    </div>
</div>
