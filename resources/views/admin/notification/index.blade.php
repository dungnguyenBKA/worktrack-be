@extends('layouts.app')

@section('content')
    <div class="container-fluid pb-3">
        <div class="card p-3 rounded-lg">
            <div class="row">
                <div class="col-md-8">
                    <a class="btn btn-primary text-nowrap btn-width-default" href="{{ route('notification.create') }}">{{ __('layouts.add') }}</a>
                </div>
{{--                <div class="col-md-4">--}}
{{--                    <form class="form-inline my-lg-0 justify-content-end" method="GET" action="{{ route('users.index') }}">--}}
{{--                        <input class="form-control mr-sm-2 mt-2" type="search" placeholder="{{ __('layouts.user_search_key') }}" aria-label="Search" name="search" value="{{ $search }}">--}}
{{--                        <button class="btn btn-dark mt-2 btn-width-default" type="submit">{{ __('layouts.search') }}</button>--}}
{{--                    </form>--}}
{{--                </div>--}}
            </div>
            @include('layouts.alert')
            <div class="table-responsive">
                <table class="table table-striped my-3 table-borderless table-striped-reverse">
                    <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('layouts.title') }}</th>
                        <th>{{ __('layouts.send_schedule') }}</th>
                        <th>{{ __('layouts.status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (count($notifications) == 0)
                        <tr>
                            <td class="text-danger no-data" colspan="8">{{ __('messages.no_data') }}</td>
                        </tr>
                    @endif
                    @foreach ($notifications as $notification)
                        <tr>
                            <td>{{ ($notifications->currentPage() - 1) * $notifications->perPage() + $loop->iteration }}</td>
                            <td>{{ $notification->title }}</td>
                            <td>{{ $notification->start_time }}</td>
                            <td>{{ $notification->status ? __('layouts.sent') : __('layouts.wait') }}</td>
                            <td>
                                <div class="d-flex justify-content-right">
                                    @if(!$notification->status)
                                    <a class="btn btn-primary py-0 px-1 mx-2"
                                       href="{{ route('notification.edit', $notification->id) }}"
                                       data-toggle="tooltip" title="{{ __('layouts.edit') }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form class="inline-block" method="POST" action="{{ route('notification.destroy', $notification->id) }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                        <button class="btn btn-danger py-0 px-1 delete" data-toggle="tooltip" title="{{ __('layouts.delete') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('layouts.paginate', ['paginate' => $notifications])
        </div>
    </div>
@endsection
