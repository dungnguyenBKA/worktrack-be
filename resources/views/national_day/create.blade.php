@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/daterangepicker.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid pb-3">
        @include('layouts.alert')
        <div class="card p-3 mb-3 rounded">
            <form action="{{ route('national-day.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label for="inputName" class="col-sm-2 col-form-label">{{ __('layouts.name') }}
                        <code>*</code>
                    </label>
                    <div class="col-sm-10 input-group">
                        <input class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                               value="{{ old('name') }}" id="inputName">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputFromTime" class="col-sm-2 col-form-label">{{ __('layouts.start_date') }}
                        <code>*</code>
                    </label>
                    <div class="col-sm-10 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input class="form-control date @error('from_date') is-invalid @enderror" type="text" id="inputFromTime"
                               placeholder="yyyy/mm/dd" name="from_date" value="{{ old('from_date') }}" autocomplete="off">
                        @error('from_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEndTime" class="col-sm-2 col-form-label">{{ __('layouts.end_date') }}
                        <code>*</code>
                    </label>
                    <div class="col-sm-10 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input class="form-control date @error('to_date') is-invalid @enderror" type="text" id="inputEndTime"
                               placeholder="yyyy/mm/dd" name="to_date" value="{{ old('to_date') }}" autocomplete="off">
                        @error('to_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <input class="btn btn-primary btn-width-default" type="submit" value="{{ __('layouts.create') }}">
                    <a class="btn btn-dark btn-width-default ml-2" href="{{ route('national-day.index') }}">{{ __('layouts.close') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
