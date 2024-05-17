@extends('layouts.app')

@section('content')

<div class="container-fluid pb-3">
    @include('layouts.alert')
    <div class="card p-3 rounded-lg">
        <form class="overtime_search" method="GET" action="{{ route('overtimes.index') }}">
            <div class="row">
                <label for="name" class="col-form-label ml-3">{{ __('layouts.date') }}</label>
                <div class="col-xl-3 col-sm-6 mb-2">
                    <input type="text" name="date" class="form-control" id="search_date_overtime" autocomplete="off"
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
                <button style="margin-right: 0.5rem" id="excel_export_overtime" type="button" class="btn btn-primary ml-2 mb-2">{{ __('layouts.export_overtime') }}</button>
            </div>
        </form>
        <div class="row justify-content-end mr-1">
            <div>
                <a class="btn btn-primary text-nowrap btn-width-default" href="{{ route('overtimes.create') }}">{{ __('layouts.add') }}</a>
            </div>
            @if (count($overtimes) > 0)
                <div class="content-approve">
                    @roles('admin')
                        <button value="1" data-route="{{ route('overtime.approve-or-reject') }}" class="mb-2 ml-2 btn btn-success approve_overtime_all" name="status">{{ __('layouts.approve') }}</button>
                        <button style="margin-left: 0.3rem" value="0" data-route="{{ route('overtime.approve-or-reject') }}" class="mb-2 btn btn-danger reject_overtime_all" name="status">{{ __('layouts.reject') }}</button>
                    @endroles
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-striped my-3 table-borderless table-striped-reverse">
                <thead class="thead-dark">
                    <tr>
                        @roles('admin')
                            <th><input type="checkbox" class="check-all" id="overtimes-check"></th>
                        @endroles
                        <th>#</th>
                        <th>{{ __('layouts.registration_applicant') }}</th>
                        <th>{{ __('layouts.ot_members') }}</th>
                        <th>{{ __('layouts.project') }}</th>
                        <th>{{ __('layouts.from_time') }}</th>
                        <th>{{ __('layouts.to_time') }}</th>
                        <th>{{ __('layouts.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($overtimes) == 0)
                    <tr>
                        <td class="text-danger no-data" colspan="7">{{ __('messages.no_data') }}</td>
                    </tr>
                    @endif
                    @foreach ($overtimes as $o)
                    <tr>
                        @roles('admin')
                            <td><input type="checkbox" class="overtime-check" data-id="{{$o->id}}"></td>
                        @endroles
                        <td>{{ ($overtimes->currentPage() - 1) * $overtimes->perPage() + $loop->iteration }}</td>
                        <td>{{ $o->staff_id }} {{ get_fullname($o) }}</td>
                        <td class="text-left">
                            <ul>
                                @foreach ($o->overtimeUsers as $u)
                                <li class="text-nowrap">{{ $u->staff_id }} {{ get_fullname($u) }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $o->project }}</td>
                        <td>{{ $o->from_time }}</td>
                        <td>{{ $o->to_time }}</td>
                                                
                        <td>
                            <i class="{{ config('common.status_map.'.$o->status.'.icon') }}" data-toggle="tooltip"
                                title="{{ config('common.status_map.'.$o->status.'.text') }}"></i>
                        </td>
                        
                        <td>
                            <div class="d-flex justify-content-right">
                                @can('approve', $o)
                                    <a class="btn btn-success py-0 px-1 mr-1" href="{{ route('overtimes.approve', $o->id) }}" data-toggle="tooltip" title="{{ __('layouts.approve') }}"><i class="fas fa-check-circle"></i></a>
                                @endcan
                                @can('update', $o)
                                    @if($o->status == config('common.overtime.waiting'))
                                        <a class="btn btn-primary py-0 px-1 mr-1" href="{{ route('overtimes.edit', $o->id) }}" data-toggle="tooltip" title="{{ __('layouts.edit') }}"><i class="fas fa-pen"></i></a>
                                    @endif    
                                @endcan
                                @can('delete', $o)
                                    <form class="inline-block" method="POST" action="{{ route('overtimes.destroy', $o->id) }}">
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
        @include('layouts.paginate', ['paginate' => $overtimes])
    </div>
</div>
@endsection
@section('script')
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.vi.min.js') }}" charset="UTF-8"></script>
    <script type="text/javascript">
        $('#excel_export_overtime').click(function () {
            let userId = $('#user_id').val();
            let date = $('#search_date_overtime').val();
            location = `{{ route("overtime.exportOvertime") }}?user_id=${userId}&date=${date}`;
        })

        $('#search_date_overtime').datepicker({
            format: "yyyy/mm",
            startView: "months",
            minViewMode: "months",
            language: "vi"
        })

        $('#search_date_overtime').on('changeDate', function() {
            let date = $('#search_date_overtime').val();
            location = `{{ route("overtimes.index") }}?date=${date}&isSearch=true`;
        });
    </script>
@endsection
