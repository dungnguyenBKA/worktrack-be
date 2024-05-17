@extends('layouts.app')

@section('style')
    <link href="{{ asset("css/dataTables.bootstrap4.min.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/daterangepicker.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid pb-3">
        <div class="card p-3 rounded-lg">
            <div class="row">
                <div class="col-md-8">
                    <a class="btn btn-primary text-nowrap btn-width-default" href="{{ route('national-day.create') }}">{{ __('layouts.add') }}</a>
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
                        <th>{{ __('layouts.name') }}</th>
                        <th>{{ __('layouts.start_date') }}</th>
                        <th>{{ __('layouts.end_date') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (count($nationalDays) == 0)
                        <tr>
                            <td class="text-danger no-data" colspan="8">{{ __('messages.no_data') }}</td>
                        </tr>
                    @endif
                    @foreach ($nationalDays as $nationalDay)
                        <tr>
                            <td>{{ ($nationalDays->currentPage() - 1) * $nationalDays->perPage() + $loop->iteration }}</td>
                            <td>{{ $nationalDay->name }}</td>
                            <td>{{ $nationalDay->from_date }}</td>
                            <td>{{ $nationalDay->to_date }}</td>
                            <td>
                                <div class="d-flex justify-content-right">
{{--                                    <a class="btn btn-success py-0 px-1" href="{{ route('national-day.show', $nationalDay->id) }}" data-toggle="tooltip" title="{{ __('layouts.show') }}"><i class="fas fa-eye"></i></a>--}}
                                    @roles('admin')
                                    <a class="btn btn-primary py-0 px-1 mx-2" href="{{ route('national-day.edit', $nationalDay->id) }}" data-toggle="tooltip" title="{{ __('layouts.edit') }}"><i class="fas fa-pen"></i></a>
                                    <form class="inline-block" method="POST" action="{{ route('national-day.destroy', $nationalDay->id) }}">
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
            @include('layouts.paginate', ['paginate' => $nationalDays])
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset("js/jquery.dataTables.min.js") }}" defer ></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.js') }}"></script>
@endsection
