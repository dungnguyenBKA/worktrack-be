@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @include('layouts.alert')
            <div class="card">
                <div class="card-header">{{ __('layouts.change_password') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('change-password.update') }}">
                        @csrf
                        <input name="_method" value="PUT" hidden>
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('layouts.current_password') }}</label>

                            <div class="col-md-6">
                                <input id="password-current" type="password" class="form-control @error('password_current') is-invalid @enderror" name="password_current" required>

                                @error('password_current')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('layouts.new_password') }}</label>

                            <div class="col-md-6">
                                <input id="password-new" type="password" class="form-control @error('password_new') is-invalid @enderror" name="password_new" required autocomplete="new-password">

                                @error('password_new')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('layouts.confirm_password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">

                                @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('layouts.change_password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection