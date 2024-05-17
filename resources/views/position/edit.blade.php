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
                        <form method="POST" action="{{ route('position.update', $position->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            @include('position._form', ['users' => $users, 'position' => $position])

                            <div class="form-group row mb-0 float-right">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('layouts.update') }}
                                    </button>
                                    <a href="{{Session::get('backUrl') ?? route('position.index') }}" class="btn btn-dark">
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
