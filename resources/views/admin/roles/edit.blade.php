@extends('layouts.app')

@section('content')
    <div class="container-fluid pb-3">
        @include('layouts.alert')
        <div class="card p-3 mb-3 rounded">
            <form method="POST" action="{{ route('roles.update', $role->id) }}">
                @csrf
                @method('PUT')
                <input type="text" value="{{ $role->id }}" name="id" hidden>
                <div class="form-group row">
                    <label for="inputStaffId" class="col-sm-2 col-form-label">{{ __('layouts.name_en') }}
                        <code>*</code>
                    </label>
                    <div class="col-sm-10 input-group">
                        <input class="form-control @error('name_en') is-invalid @enderror" type="text" name="name_en"
                               value="{{ old('name_en', $role->name_en) }}" id="inputStaffId">
                        @error('name_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputStaffId" class="col-sm-2 col-form-label">{{ __('layouts.name_vi') }}</label>
                    <div class="col-sm-10 input-group">
                        <input class="form-control @error('name_vi') is-invalid @enderror" type="text" name="name_vi"
                               value="{{ old('name_vi', $role->name_vi) }}" id="inputStaffId">
                        @error('name_vi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputStaffId" class="col-sm-2 col-form-label">{{ __('layouts.name_ja') }}</label>
                    <div class="col-sm-10 input-group">
                        <input class="form-control @error('name_ja') is-invalid @enderror" type="text" name="name_ja"
                               value="{{ old('name_ja', $role->name_ja) }}" id="inputStaffId">
                        @error('name_ja')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <input class="btn btn-primary btn-width-default" type="submit" value="{{ __('layouts.save') }}">
                    <a class="btn btn-dark btn-width-default ml-2" href="{{ route('roles.index') }}">{{ __('layouts.close') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
