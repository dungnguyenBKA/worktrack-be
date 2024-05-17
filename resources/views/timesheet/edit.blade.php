@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ __($message)}}
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('timesheet.update', $timesheet->id) }}">
                            @csrf
                            @method('PUT')

                            @include('timesheet._form', ['users' => $users, 'timesheet' => $timesheet])
                            
                            <div class="form-group row mb-0 float-right">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('layouts.update') }}
                                    </button>
                                    <a href="{{ Session::get('backUrl') }}" class="btn btn-dark">
                                        {{ __('layouts.close') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
