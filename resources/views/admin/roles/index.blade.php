@extends('layouts.app')

@section('content')
    <div class="container-fluid pb-3">
        <div class="card p-3 rounded-lg">
            <div class="row">
                <div class="col-md-8">
                    <a class="btn btn-primary text-nowrap btn-width-default" href="{{ route('roles.create') }}">{{ __('layouts.add') }}</a>
                </div>
            </div>
            @include('layouts.alert')
            <div class="table-responsive">
                <table class="table table-striped my-3 table-borderless table-striped-reverse">
                    <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('layouts.name_en') }}</th>
                        <th>{{ __('layouts.name_vi') }}</th>
                        <th>{{ __('layouts.name_ja') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (count($roles) == 0)
                        <tr>
                            <td class="text-danger no-data" colspan="8">{{ __('messages.no_data') }}</td>
                        </tr>
                    @endif
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}</td>
                            <td>{{ $role->name_en }}</td>
                            <td>{{ $role->name_vi }}</td>
                            <td>{{ $role->name_ja }}</td>
                            <td>
                                <div class="d-flex justify-content-right">
                                    <a class="btn btn-primary py-0 px-1 mx-2" href="{{ route('roles.edit', $role->id) }}" data-toggle="tooltip" title="{{ __('layouts.edit') }}"><i class="fas fa-pen"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('layouts.paginate', ['paginate' => $roles])
        </div>
    </div>
@endsection
