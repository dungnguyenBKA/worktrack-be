<div class="row">
    <label for="type" class="col-sm-2 col-form-labelt">{{ __('layouts.type') }}<code>*</code></label>

    <div class="input-group col-sm-10 radio">
        <!-- radio -->
        <div class="form-group clearfix">
            <div class="form-check-inline">
                <input class="form-check-input" type="radio" id="type_active" value="1"
                       checked name="type">
                <label class="form-check-label" for="type_active">
                    {{ __('layouts.office') }}
                </label>
            </div>
            <div class="form-check-inline">
                <input class="form-check-input" type="radio" value="2"
                    @if(old('type') == 2 || (isset($position) && $position->type == 2)) checked @endif id="type_inactive" name="type">
                <label class="form-check-label" for="type_inactive">
                    {{ __('layouts.remote') }}
                </label>
            </div>
        </div>
    </div>
</div>
@php
    $staffSelect = old('type') == 2 || (isset($position) && $position->type == 2) ? '' : "hidden";
@endphp
<div class="form-group row" id="select-staff" {{ $staffSelect }}>
    <label for="name" class="col-sm-2 col-form-label">{{ __('layouts.staff') }}<code>*</code></label>
    <div class="col-sm-10">
        <select name="user_id" class="form-control select2 select2-hidden-accessible @if($errors->has('user_id')) is-invalid @endif">
            <option value="" >--</option>
            @foreach($users as $user)
                @php
                    $userSelect = old('user_id', isset($position) && $user->id == $position->user_id ? $position->user_id : false);
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
    <label for="name" class="col-sm-2 col-form-labelt">{{ __('layouts.work_location') }}<code>*</code></label>
    <div class="input-group col-sm-10">
        <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="fas fa-map-marker-alt"></i>
            </span>
        </div>

        <input
            class="form-control float-right"
               placeholder="Lat"
               name="latitude"
               id="address-latitude"
               value="{{ old('latitude', isset($position) ? $position->latitude : 0) }}"  />

        <input
            class="form-control float-right"
               placeholder="Long"
               name="longitude"
               id="address-longitude"
               value="{{ old('longitude', isset($position) ? $position->longitude : 0) }}" />
    </div>
</div>

<div class="form-group row">
    <label for="status" class="col-md-2 col-form-labelt">{{ __('layouts.status') }}<code>*</code></label>

    <div class="input-group col-sm-10 radio">
        <!-- radio -->
        <div class="form-group clearfix">
            <div class="form-check-inline">
                <input class="form-check-input" type="radio" id="status_active" value="1"
                       checked name="status">
                <label class="form-check-label" for="status_active">
                    {{ __('layouts.active_status') }}
                </label>
            </div>
            <div class="form-check-inline">
                <input class="form-check-input" type="radio" value="2"
                       @if(old('status') == 2 || (isset($position) && $position->status == 2)) checked @endif id="status_inactive" name="status">
                <label class="form-check-label" for="status_inactive">
                    {{ __('layouts.inactive_status') }}
                </label>
            </div>
        </div>
    </div>
</div>
