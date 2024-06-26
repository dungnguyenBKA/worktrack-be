@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    <div class="card p-3 rounded-lg">
        <div class="row">
            <div class="col-md-4">
                @roles('admin')
                <a class="btn btn-primary text-nowrap" href="{{ route('position.create') }}">{{ __('layouts.register_work_location') }}</a>
                @endroles
            </div>

        </div>
        @include('layouts.alert')
        <div class="table-responsive">
            <table class="table table-striped my-3 table-borderless table-striped-reverse">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th scope="col">{{ __('layouts.type') }}</th>
                        <th scope="col">{{ __('layouts.staff') }}</th>
                        <th scope="col">{{ __('layouts.work_location') }}</th>
                        <th scope="col" width="120">{{ __('layouts.status') }}</th>
                        <th scope="col" width="120"></th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($positions) == 0)
                    <tr>
                        <td class="text-danger no-data" colspan="5">{{ __('messages.no_data') }}</td>
                    </tr>
                    @endif
                    @foreach ($positions as $position)
                    <tr>
                        <td>{{ ($positions->currentPage() - 1) * $positions->perPage() + $loop->iteration }}</td>
                        <td>{{ config('constants.postion_type')[$position->type] ?? "" }}</td>
                        <td>{{ $position->user ? $position->user->staff_id . " " . $position->user->getFullName() : ""; }}</td>
                        <td>{{ $position->position }}</td>
                        <td>{{ $status[$position->status] ?? "" }}</td>
                        <td>
                            <div class="d-flex justify-content-right">
                                <a class="btn btn-success py-0 px-1" href="{{ route('position.show', $position->id) }}" data-toggle="tooltip" title="{{ __('layouts.show') }}"><i class="fas fa-eye"></i></a>
                                @roles('admin')
                                <a class="btn btn-primary py-0 px-1 mx-2" href="{{ route('position.edit', $position->id) }}" data-toggle="tooltip" title="{{ __('layouts.edit') }}"><i class="fas fa-pen"></i></a>
                                @endroles
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($positions->lastPage() > 1)
        <div class="d-flex justify-content-end mb-2">
            @if ($positions->currentPage() > 1)
            <a class="btn btn-outline-info" href="{{ $positions->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
            @endif
            @if($positions->currentPage() > 1)
                <a class="btn btn-outline-info mx-1 font-weight-bold" href="{{ $positions->previousPageUrl() }}">{{ $positions->currentPage() - 1 }}</a>
            @endif
            <a class="btn btn-info mx-1 font-weight-bold">{{ $positions->currentPage() }}</a>
            @if($positions->currentPage() < $positions->lastPage())
                <a class="btn btn-outline-info mx-1 font-weight-bold" href="{{ $positions->nextPageUrl() }}">{{ $positions->currentPage() + 1 }}</a>
            @endif
            @if ($positions->currentPage() < $positions->lastPage())
                <a class="btn btn-outline-info" href="{{ $positions->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                @endif
        </div>
        @endif
    </div>
</div>
@endsection
