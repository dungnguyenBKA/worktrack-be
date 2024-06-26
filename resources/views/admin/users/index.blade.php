@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    <div class="card p-3 rounded-lg">
        <div class="row">
            <div class="col-md-8">
                @roles('admin')
                <a class="btn btn-primary text-nowrap btn-width-default" href="{{ route('users.create') }}">{{ __('layouts.add') }}</a>
                @endroles
            </div>
            <div class="col-md-4">
                <form class="form-inline my-lg-0 justify-content-end" method="GET" action="{{ route('users.index') }}">
                    <input class="form-control mr-sm-2 mt-2" type="search" placeholder="{{ __('layouts.user_search_key') }}" aria-label="Search" name="search" value="{{ $search }}">
                    <button class="btn btn-dark mt-2 btn-width-default" type="submit">{{ __('layouts.search') }}</button>
                </form>
            </div>
        </div>
        @include('layouts.alert')
        <div class="table-responsive">
            <table class="table table-striped my-3 table-borderless table-striped-reverse">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('layouts.staff_id') }}</th>
                        <th>{{ __('layouts.first_name') }}</th>
                        <th>{{ __('layouts.last_name') }}</th>
                        <th>{{ __('layouts.email') }}</th>
                        <th>{{ __('layouts.role') }}</th>
                        <th>{{ __('layouts.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($users) == 0)
                    <tr>
                        <td class="text-danger no-data" colspan="8">{{ __('messages.no_data') }}</td>
                    </tr>
                    @endif
                    @foreach ($users as $user)
                    <tr>
                        <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td>{{ $user->staff_id }}</td>
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->last_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if(config('app.locale') == 'vi' && $user->roles && $user->roles->name_vi)
                                {{ $user->roles->name_vi }}
                            @elseif(config('app.locale') == 'ja' && $user->roles && $user->roles->name_ja)
                                {{ $user->roles->name_ja }}
                            @else
                                {{ $user->roles ? $user->roles->name_en : '' }}
                            @endif
                        </td>
                        <td>{{ __('common.user.status')[$user->status] }}</td>
                        <td>
                            <div class="d-flex justify-content-right">
                                <a class="btn btn-success py-0 px-1" href="{{ route('users.show', $user->id) }}" data-toggle="tooltip" title="{{ __('layouts.show') }}"><i class="fas fa-eye"></i></a>
                                @roles('admin')
                                <a class="btn btn-primary py-0 px-1 mx-2" href="{{ route('users.edit', $user->id) }}" data-toggle="tooltip" title="{{ __('layouts.edit') }}"><i class="fas fa-pen"></i></a>
                                <form class="inline-block" method="POST" action="{{ route('users.destroy', $user->id) }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <button class="btn btn-danger py-0 px-1 delete" data-toggle="tooltip" title="{{ __('layouts.delete') }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endroles
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('layouts.paginate', ['paginate' => $users])
    </div>
</div>
@endsection
