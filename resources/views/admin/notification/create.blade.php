@extends('layouts.app')

@section('content')
    <div class="container-fluid pb-3">
        @include('layouts.alert')
        <div class="card p-3 mb-3 rounded">
            <form method="POST" action="{{ route('notification.store') }}">
                @csrf
                <div class="form-group row">
                    <label for="inputTitleId" class="col-sm-2 col-form-label">{{ __('layouts.title') }}
                        <code>*</code>
                    </label>
                    <div class="col-sm-10 input-group">
                        <input class="form-control @error('title') is-invalid @enderror" type="text" name="title"
                               value="{{ old('title') }}" id="inputTitleId">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="inputContentId" class="col-sm-2 col-form-label">{{ __('layouts.content') }}
                        <code>*</code>
                    </label>
                    <div class="col-sm-10 input-group">
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror"
                                  id="inputContentId" rows="5">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="inputStartTime" class="col-sm-2 col-form-label">{{ __('layouts.send_schedule') }}
                        <code>*</code></label>
                    <div class="col-sm-10 input-group">
                        <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                        </div>
                        <input type="text" class="form-control date-time @error('start_time') is-invalid @enderror" id="inputStartTime"
                               placeholder="yyyy/mm/dd --:--" name="start_time"
                               value="{{ old('start_time', \Carbon\Carbon::now()->format('Y/m/d H:i')) }}" autocomplete="off">
                        @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <input class="btn btn-primary btn-width-default" type="submit" value="{{ __('layouts.create') }}">
                    <a class="btn btn-dark ml-2 btn-width-default" href="{{ route('notification.index') }}">{{ __('layouts.close') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
