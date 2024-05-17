@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @include('layouts.alert')
    <div class="card p-3 rounded-lg">
        <form class="request_absent_search" method="GET" action="{{ route('request-absent.index') }}">
            <div class="row">
                <label for="name" class="col-form-label ml-3">{{ __('layouts.date') }}</label>
                <div class="col-xl-3 col-sm-6 mb-2">
                    <input type="text" name="date" class="form-control" id="search_date" autocomplete="off"
                            value="{{ isset($date) ? \Carbon\Carbon::make($date)->format('Y/m') : null }}">
                    <input type="text" value="true" name="isSearch" hidden>
                </div>
                @roles('admin')
                    <label for="name" class="col-form-label mb-2 ml-3">{{ __('layouts.staff_register') }}</label>
                    <div class="col-xl-3 form-group mb-2">
                        <select name="user_id" id="user_id" class="form-control select2 select2-hidden-accessible">
                            <option value="" >{{ __('layouts.all_user') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $request->user_id == $user->id ? 'selected="selected"' : '' }}>{{ $user->getFullName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-dark ml-3 mb-2 btn-width-default" type="submit">{{ __('layouts.search') }}</button>
                @endroles
                <button style="margin-right: 0.5rem" id="excel_export_request_absent" type="button" class="btn btn-primary ml-2 mb-2">{{ __('layouts.export_request_absent') }}</button>
            </div>
        </form>
        {{-- </div> --}}
        <div class="row justify-content-end mr-1">
            <div>
                <a class="btn btn-primary text-nowrap btn-width-default" href="{{ route('request-absent.create') }}">{{ __('layouts.add') }}</a>
            </div>
            @if (count($requestAbsents) > 0)
                <div class="content-approve">
                    @roles('admin')
                        <button value="1" data-route="{{ route('request-absent.approve-or-reject') }}" class="btn ml-2 mb-2 btn-success approve_request_all" name="status">{{ __('layouts.approve') }}</button>
                        <button style="margin-left: 0.3rem" value="0" data-route="{{ route('request-absent.approve-or-reject') }}" class="btn mb-2 btn-danger reject_request_all" name="status">{{ __('layouts.reject') }}</button>
                    @endroles
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-striped my-3 table-borderless table-striped-reverse">
                <thead class="thead-dark">
                    <tr>
                        @roles('admin')
                            <th><input type="checkbox" class="check-all" id="absents-check"></th>
                        @endroles
                        <th>#</th>
                        <th style="min-width: 120px">{{ __('layouts.staff_id') }}</th>
                        <th>{{ __('layouts.name') }}</th>
                        <th>{{ __('layouts.from_time') }}</th>
                        <th>{{ __('layouts.to_time') }}</th>
                        <th style="width: 40%">{{ __('layouts.reason') }}</th>
                        <th style="min-width: 100px">{{ __('layouts.use_leave') }}</th>
                        <th style="min-width: 100px" ">{{ __('layouts.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($requestAbsents) == 0)
                    <tr>
                        <td class="text-danger no-data" colspan="7">{{ __('messages.no_data') }}</td>
                    </tr>
                    @endif
                    @foreach ($requestAbsents as $r)
                    <tr>
                        @roles('admin')
                            <td><input type="checkbox" class="request-absent-check" data-id="{{$r->id}}"></td>
                        @endroles
                        <td>{{ ($requestAbsents->currentPage() - 1)*$requestAbsents->perPage() + $loop->iteration }}</td>
                        <td>{{ $r->staff_id }}</td>
                        <td>{{ get_fullname($r) }}</td>
                        <td>{{ get_datetime($r->from_time, 'Y/m/d H:i') }}</td>
                        <td>{{ get_datetime($r->to_time, 'Y/m/d H:i') }}</td>
                        <td>{{ $r->reason }}</td>
                        <td class="text-center">
                            <i class="{{ $r->use_leave_hour ? 'text-success fas fa-check-circle' : 'text-danger fas fa-times-circle' }}" data-toggle="tooltip"
                               title="{{ $r->use_leave_hour ? __('layouts.use_leave') : __('layouts.not_use_leave') }}"></i>
                        </td>
                        <td class="text-center">
                            <i class="{{ config('common.status_map.'.$r->status.'.icon') }}" data-toggle="tooltip"
                               title="{{ config('common.status_map.'.$r->status.'.text') }}"></i>
                        </td>
                        <td>
                            <div class="d-flex justify-content-right">
                                @can('approve', $r)
                                <a class="btn btn-success py-0 px-1 mr-1"
                                   href="{{ route('request-absent.approve', $r->id) }}"
                                   data-toggle="tooltip" title="{{ __('layouts.approve') }}">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                                @endcan
                                @if($r->status == config('common.request_absent.waiting') || $isAdmin)
                                    <a class="btn btn-primary py-0 px-1 mr-1" href="{{ route('request-absent.edit', $r->id) }}"
                                       data-toggle="tooltip" title="{{ __('layouts.edit') }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @endif
                                @can('delete', $r)
                                        <form class="inline-block" method="POST" action="{{ route('request-absent.destroy', $r->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger py-0 px-1 delete" data-toggle="tooltip" title="{{ __('layouts.delete') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('layouts.paginate', ['paginate' => $requestAbsents])
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.vi.min.js') }}" charset="UTF-8"></script>
    <script type="text/javascript">
        $('#excel_export_request_absent').click(function () {
            let userId = $('#user_id').val();
            let date = $('#search_date').val();
            location = `{{ route("request-absent.exportRequestAbsent") }}?user_id=${userId}&date=${date}`;
        })

        $('#search_date').datepicker({
            format: "yyyy/mm",
            startView: "months",
            minViewMode: "months",
            language: "vi"
        })

        $('#search_date').on('changeDate', function() {
            let date = $('#search_date').val();
            location = `{{ route("request-absent.index") }}?date=${date}&isSearch=true`;
        });
    </script>
@endsection