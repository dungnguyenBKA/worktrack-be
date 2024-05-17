@extends('layouts.app')

@section('style')
    <link href="{{ asset("css/dataTables.bootstrap4.min.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/leaflet.css') }}" rel="stylesheet">
    <style type="text/css">
        #map {
            width: 500px;
            height: 500px;
        }

        .popover {
            max-width: none;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ __($message)}}
            </div>
        @endif
        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ __($message)}}
            </div>
        @endif
        <div class="row ">
            <div class="col-md-12">
                <div class="card rounded-lg">
                    <div class="card-body">
                        <form id="frm_search" action="{{ route('timesheet.index') }}" method="GET">
                            <div class="row">
                                <label for="name" class="col-form-label ml-4">{{ __('layouts.date') }}</label>
                                <div class="col-xl-3">
                                    <input type="text" name="date" class="form-control" id="search_date" autocomplete="off"
                                           value="{{ \Carbon\Carbon::make($date)->format('Y/m') }}">
                                </div>
                                @if($isAdmin)
                                    <label for="name" class="col-form-label ml-4">{{ __('layouts.staff') }}</label>
                                    <div class="col-xl-3 form-group">
                                        <select name="user_id" id="user_id" class="form-control select2 select2-hidden-accessible">
                                            <option value="" >{{ __('layouts.all_user') }}</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request()->query('user_id') && $userSelected->id == $user->id ? 'selected' : '' }}>{{ $user->getFullName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-xl-4 form-group">
                                    <button class="btn btn-dark" type="submit">{{ __('layouts.search') }}</button>
                                    <button id="excel_export" type="button" class="btn btn-primary">{{ __('layouts.export_timesheet') }}</button>
                                    @if($isAdmin)
                                        <button id="update_data_month" type="button" class="btn btn-primary">{{ __('layouts.calc_annual_leave') }}</button>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <form id="frm_update_data_month" action="{{ route('timesheet.updateMonthAll')}}" method="POST" style="display: inline">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="date-search" class="form-control" id="date-search"
                                   value="{{ request()->has('date') ? \Carbon\Carbon::make(request()->get('date').'/01')->format('Y/m/d')
                                                : \Carbon\Carbon::make('first day of this month')->format('Y/m/d')}}">
                        </form>
                        <div class="info-box bg-dark">
                            <div class="col-xl-8 timesheet-paid-leave">
                                <form action="{{ route("timesheet.updatePaidLeave", $paidLeave ? $paidLeave->id : 0) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="w-30">{{ __('layouts.annual_leave_hours_left_last_month') }}</td>
                                            <td class="w-10">{{ $paidLeave ? $paidLeave->day_left : "" }}</td>
                                            <td class="w-30">{{ __('layouts.leave_hours_this_month') }}</td>
                                            <td class="w-10">{{ $paidLeave ? $paidLeave->day_use_in_month + $paidLeave->leave_hour_in_work_hour - $timeSheetArr['total']['timeInFuture'] : 0 }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('layouts.annual_leave_hours_this_month') }}</td>
                                            <td>{{ $paidLeave ? $paidLeave->day_add_in_month : '' }}</td>
                                            <td>{{ __('layouts.unpaid_leave_hours_of_month') }}</td>
                                            <td>
                                                {{ $paidLeave ? $paidLeave->salaryDeductionDay($timeSheetArr['total']['timeInFuture']) : "" }}
                                            </td>
                                        </tr>
                                        <tr>
                                            @if(!$isAdmin)
                                                <!-- <td>{{ __('layouts.annual_leave_adjustments') }}</td> -->
                                                <td>{{ $paidLeave ? $paidLeave->day_edit : "" }}</td>
                                                <!-- <td>{{ __('layouts.comment') }}</td> -->
                                                <td>{{ $paidLeave ? $paidLeave->comment : "" }}</td>
                                            @else
                                                <td>{{ __('layouts.annual_leave_adjustments') }}</td>
                                                <td>
                                                    @if($paidLeave)
                                                        <input name="day_edit" id="paid-leave-day_edit" type="text"
                                                               style="width: 80px" value="{{ old('day_edit', $paidLeave ? $paidLeave->day_edit : "") }}"
                                                               class="form-control form-control-sm @error('day_edit') is-invalid @enderror transparent"/>
                                                        @error('day_edit')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    @endif
                                                </td>
                                                <td>{{ __('layouts.comment') }}</td>
                                                <td>
                                                    @if($paidLeave)
                                                        <input name="comment" id="paid-leave-comment" type="text"
                                                               value="{{ old('comment', $paidLeave ? $paidLeave->comment : "") }}"
                                                               class="form-control form-control-sm @error('comment') is-invalid @enderror transparent"/>
                                                        @error('comment')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($paidLeave)
                                                        <button type="submit" class="btn btn-primary btn-sm">{{ __('layouts.update') }}</button>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <td>{{ __('layouts.annual_leave_hours_left') }}</td>
                                            <td>
                                                {{ $paidLeave ? $paidLeave->leaveDaysLeft($timeSheetArr['total']['timeInFuture']) : "" }}
                                            </td>
                                            <td>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if($isAdmin)
            <div class="row">
                <div class="col-md-12">
                    <div class="card rounded-lg">
                        <div class="card-header card-title">{{ __('layouts.upload_timesheet') }}</div>
                        <div class="card-body">
                            <form id="upload-time-sheet" action="{{ route("timesheet.uploadTimesheet") }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-xl-4">
                                        <div class="custom-file @error('file-timesheet') is-invalid @enderror" style="margin-top: 0.3rem">
                                            <input type="file" class="file-timesheet" name="file-timesheet" id="file-timesheet">
                                            <label class="custom-file-label" for="file-timesheet">{{ __('layouts.choose_file') }}</label>
                                        </div>
                                        @error('file-timesheet')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-2">
                                        <div class="form-check @error('type') is-invalid @enderror">
                                            <input class="form-check-input" id="type-0" type="radio" name="type" checked="" value="0">
                                            <label class="form-check-label" for="type-0">{{ __('layouts.do_not_overwrite') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" id="type-1" type="radio" name="type" value="1">
                                            <label class="form-check-label" for="type-1">{{ __('layouts.overwrite_select_best_time') }}</label>
                                        </div>
                                        @error('type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-3"><button type="submit" id="btn-upload-time-sheet" class="btn btn-primary" style="margin-top: 0.3rem">{{ __('layouts.upload') }}</button></div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <a href="{{ asset('/files/Template_Import_Timesheet.xlsx') }}" download>{{ __('layouts.download_template_import') }}</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="card rounded-lg">
                    <div class="card-header card-title">
                    {{ __('layouts.working_time') }}
                        @if($isAdmin)
                        <a href="{{ route("timesheet.create") }}" class="btn btn-primary float-right">{{ __('layouts.add_working_time') }}</a>
                        @endif
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <table class="table table-striped table-borderless">
                            <thead class="thead-dark">
                            <tr>
                                <th>{{ __('layouts.staff_id') }}</th>
                                <th style="min-width: 140px">{{ __('layouts.first_name') }}</th>
                                <th style="min-width: 120px">{{ __('layouts.last_name') }}</th>
                                <th style="min-width: 100px">{{ __('layouts.date') }}</th>
                                <th style="min-width: 90px">{{ __('layouts.check_in') }}</th>
                                <th style="min-width: 90px">{{ __('layouts.check_out') }}</th>
                                <th style="width: 70px" data-toggle="tooltip" title="{{ __('layouts.WH_Tooltip') }}">WH &#9432;</th>
                                <th style="width: 70px" data-toggle="tooltip" title="{{ __('layouts.MH_Tooltip') }}">MH &#9432;</th>
                                <th style="width: 70px" data-toggle="tooltip" title="{{ __('layouts.OT1_Tooltip') }}">OT1 &#9432;</th>
                                <th style="width: 70px" data-toggle="tooltip" title="{{ __('layouts.OT2_Tooltip') }}">OT2 &#9432;</th>
                                <th style="width: 70px" data-toggle="tooltip" title="{{ __('layouts.OT3_Tooltip') }}">OT3 &#9432;</th>
                                <th style="width: 70px" data-toggle="tooltip" title="{{ __('layouts.OT4_Tooltip') }}">OT4 &#9432;</th>
                                <th class="text-center">{{ __('layouts.comment') }}</th>
                                <th class="text-center" style="min-width: 100px">{{ __('layouts.location') }}</th>
                                <th class="text-center" style="min-width: 120px"></th>
                            </tr>
                            </thead>
                            <tbody>
                                @if (isset($timeSheetArr['timesheets']))
                                    @foreach ($timeSheetArr['timesheets'] as $key => $timesheet)
                                        <tr class="@if($timesheet['national_day']) bg-warning @elseif($timesheet['day_of_week']) bg-secondary-dark @endif">
                                            <td>{{ $timesheet['staff_id'] ?? "" }}</td>
                                            <td>{{ $timesheet['first_name'] ?? "" }}</td>
                                            <td>{{ $timesheet['last_name'] ?? "" }}</td>
                                            <td>{{ $timesheet['date'] ?? "" }}</td>
                                            <td style = "{{ $timesheet['is_late'] ? 'color:#d0211c; font-weight:bold' : '' }}">{{ $timesheet['checkin'] ?? "" }}</td>
                                            <td>{{ $timesheet['checkout'] ?? "" }}</td>
                                            <td>{{ $timesheet['wh'] ?? 0 }}</td>
                                            <td>{{ $timesheet['mh'] ?? 0 }}</td>
                                            <td>{{ $timesheet['ot1'] ?? 0 }}</td>
                                            <td>{{ $timesheet['ot2'] ?? 0 }}</td>
                                            <td>{{ $timesheet['ot3'] ?? 0 }}</td>
                                            <td>{{ $timesheet['ot4'] ?? 0 }}</td>
                                            <td>{{ $timesheet['comment'] ?? "" }}</td>
                                            <td class="text-center">
                                                @if(isset($timesheet['location']) && !empty($timesheet['location']) )
                                                    @php $timesheet['location'] = array_values($timesheet['location']) @endphp
                                                    <button class="show-map btn {{ $timesheet['location'][0]['checkLocation'] ? 'bg-info' : 'bg-danger' }} py-0 px-1"
                                                            data-toggle="popover" data-html="true" title="Current Position"
                                                            data-content="<div id='map'></div>"
                                                            data-check-location="{{ $timesheet['location'][0]['checkLocation'] }}"
                                                            data-lat="{{ $timesheet['location'][0]['lat'] ?? "" }}"
                                                            data-long="{{ $timesheet['location'][0]['long'] ?? "" }}">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </button>
                                                    @if(count($timesheet['location']) >= 2)
                                                        <button class="show-map btn {{ $timesheet['location'][count($timesheet['location']) - 1]['checkLocation'] ? 'bg-info' : 'bg-danger' }} py-0 px-1" data-toggle="popover" data-html="true" title="Current Position"
                                                                data-content="<div id='map'></div>"
                                                                data-check-location="{{ $timesheet['location'][count($timesheet['location']) - 1]['checkLocation'] }}"
                                                                data-lat="{{ $timesheet['location'][count($timesheet['location']) - 1]['lat'] ?? "" }}"
                                                                data-long="{{ $timesheet['location'][count($timesheet['location']) - 1]['long'] ?? "" }}">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-outline-info py-0 px-1 location" data-toggle="modal"
                                                            data-date="{{ $timesheet['date'] ?? "" }}"
                                                            data-location="{{ json_encode($timesheet['location']) }}">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="d-flex justify-content-right">
                                                <button class="btn btn-success py-0 px-1 comment" data-toggle="modal"
                                                        data-date="{{ $timesheet['date'] ?? "" }}"
                                                        data-check-in="{{ $timesheet['checkin'] ?? "" }}"
                                                        data-check-out="{{ $timesheet['checkout'] ?? "" }}"
                                                        data-comment="{{ $timesheet['comment'] ?? "" }}">
                                                    <i class="fas fa-comment"></i>
                                                </button>
                                                @if(isset($timesheet['id']) && $timesheet['id'] != "" && $isAdmin)
                                                    <a href="{{ route('timesheet.edit', $timesheet['id']) }}" class="btn btn-primary py-0 px-1 mx-2"><i class="fas fa-pen"></i></a>
                                                    <form action="{{ route('timesheet.destroy', $timesheet['id']) }}" method="POST" style="display: inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger py-0 px-1 delete"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @elseif ($isAdmin)
                                                    <a href="{{ route('timesheet.create', ['user_id' => $timesheet['user_id'], 'date' => $timesheet['date']]) }}"
                                                       class="btn btn-primary py-0 px-1 mx-2"><i class="fas fa-plus"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                            <tr class="bg-primary">
                                <td colspan="6" class="text-bold">{{ __('layouts.total_hours_worked') }}</td>
                                <td>{{ $timeSheetArr['total']['wh'] ?? 0 }}</td>
                                <td>{{ $timeSheetArr['total']['mh'] ?? 0 }}</td>
                                <td>{{ $timeSheetArr['total']['ot1'] ?? 0 }}</td>
                                <td>{{ $timeSheetArr['total']['ot2'] ?? 0 }}</td>
                                <td>{{ $timeSheetArr['total']['ot3'] ?? 0 }}</td>
                                <td>{{ $timeSheetArr['total']['ot4'] ?? 0 }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                    @if ($isAdmin && $userPaginate && $userPaginate->lastPage() > 1)
                        @include('layouts.paginate', ['paginate' => $userPaginate])
                    @endif
                    </div>
                </div>
            </div>
        </div>
            <div class="modal fade show" id="modal-comment" aria-modal="true" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ __('layouts.comment') }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3"><strong>{{ __('layouts.date') }}</strong></div>
                                <div class="col-md-9 date"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-3"><strong>{{ __('layouts.check_in') }}</strong></div>
                                <div class="col-md-9 check-in"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-3"><strong>{{ __('layouts.check_out') }}</strong></div>
                                <div class="col-md-9 check-out"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-3"><strong>{{ __('layouts.comment') }}</strong></div>
                                <div class="col-md-9">
                                    <textarea rows="3" cols="30" class="input-comment"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('layouts.close') }}</button>
                            <button type="button" class="btn btn-primary" id="save-comment">{{ __('layouts.send') }}</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>

            <div class="modal fade show" id="modal-location" aria-modal="true" role="dialog">
                <div style="max-width: 850px" class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ __('layouts.timesheet_date') }}<span id="timesheetDate"></span></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive" style="max-height: 500px">
                                <table class="table table-striped table-borderless">
                                    <thead class="thead-dark" style="position: sticky; top: 0">
                                    <tr>
                                        <th class="text-center">{{ __('layouts.time') }}</th>
                                        <th class="text-center">{{ __('layouts.location') }}</th>
                                        <th class="text-center">{{ __('layouts.map') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="body-location">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-right">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('layouts.close') }}</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>

            <div class="modal fade show" id="modal-export" aria-modal="true" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ __('layouts.label_export_option') }}<span id="timesheetDate"></span></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="">
                                <div class="form-check">
                                    <input class="form-check-input" id="option-export-0" type="radio" name="option-export" checked="" value="0">
                                    <label class="form-check-label" for="option-export-0">{{ __('layouts.timesheet_export_option_0') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" id="option-export-1" type="radio" name="option-export" value="1">
                                    <label class="form-check-label" for="option-export-1">{{ __('layouts.timesheet_export_option_1') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-right">
                            <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('layouts.close') }}</button>
                            <button type="button" class="btn btn-danger" id="btn-export-timesheet">{{ __('layouts.label_agree_export') }}</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset("js/jquery.dataTables.min.js") }}" defer ></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.vi.min.js') }}" charset="UTF-8"></script>
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <script>
        const distanceLimit = '{{ $distanceLimit }}';
        var comment_element = '';
        var comment_text = '';
        $(document).ready(function () {
            $('[data-toggle="popover"]').popover({
                trigger: 'focus'
              });

            $('[data-toggle="popover"]').on("shown.bs.popover", (e) => {
                showMap($(e.target));
            });
        })

        $('#search_date').datepicker({
            format: "yyyy/mm",
            startView: "months",
            minViewMode: "months",
            language: "vi"
        })

        $('#search_date').datepicker()
            .on('hide', function (e) {
                $('#date-search').val($(this).val()+'/01');
        });

        $('.comment').click(function () {
            comment_element = $(this);
            $('#modal-comment').find('.date').text($(this).attr('data-date'));
            $('#modal-comment').find('.check-in').text($(this).attr('data-check-in'));
            $('#modal-comment').find('.check-out').text($(this).attr('data-check-out'));
            if ($(this).attr('data-comment') != '') {
                $('#modal-comment').find('.input-comment').val($(this).attr('data-comment'));
            }else{
                $('#modal-comment').find('.input-comment').val(comment_text);
            }
            $('#modal-comment').modal('show')
        })

        $('.location').click(function () {
            let data = JSON.parse($(this).attr('data-location'));
            let html = '';
            data.forEach(function (item) {
                let classLocation = 'bg-danger';
                if(item.checkLocation){
                    classLocation = 'bg-info';
                }

                html += '<tr>' +
                    '<td>'+ item.date_time +'</td>' +
                    '<td>'+ item.location_name +'</td>' +
                    '<td class="text-center">' +
                    '<button class="show-map btn '+ classLocation +' py-0 px-1" ' +
                    'data-toggle="popover" data-html="true" title="" data-content="<div id=\'map\'></div>" ' +
                    'data-check-location="'+ item.checkLocation +'" data-lat="'+ item.lat +'" data-long="'+ item.long +'" ' +
                    'data-original-title="Current Position">' +
                    '<i class="fas fa-map-marker-alt"></i>' +
                    '</button>' +
                    '</td>'+
                    '</tr>'
            })
            $('.body-location').html(html);
            $('#timesheetDate').text($(this).attr('data-date'))
            $('#modal-location').modal('show');
            $('[data-toggle="popover"]').popover({
                trigger: 'focus'
            });
        })

        var clickHandler = ("ontouchstart" in window ? "touchend" : "click")
        $(document).on(clickHandler, '.show-map', function () {
            showMap($(this));
        })

        $('#save-comment').click(function () {
            let date = $('#modal-comment').find('.date').text();
            let comment = $('#modal-comment').find('.input-comment').val();
            let user_id = '{{ $userSelected->id ?? "" }}';
            $.ajax({
                type: 'POST',
                url: '{{ route('comments.store') }}',
                data: {user_id:user_id, date: date, comment: comment, _token: '{{ csrf_token() }}'},
                success: function (res) {
                    if(res.code == 200){
                        comment_element.parents('tr').find('td:nth-child(13)').text(comment);
                        comment_element.parents('tr').find('.comment').attr('data-comment', comment);
                        comment_text = comment;
                        $('#modal-comment').modal('hide');
                    }else{
                        alert(res.message);
                    }
                }
            })
        })

        $('#excel_export').click(function () {
            $('#modal-export').modal('show');
        })

        $('#btn-export-timesheet').click(function() {
            $('#modal-export').modal('hide');
            let optionSelected = $('input[name="option-export"]:checked').val();
            var userId = $('#user_id').val();
            var date = $('#search_date').val();
            location = `{{ route("timesheet.exportTimesheet") }}?user_id=${userId}&date=${date}&dataInDay=${optionSelected}`;
        })

        function addMarker(currentCoord, allowPositions, checkLocation, mapId = "map", zoomLevel = 16) {
            var mapObj = null;
            var mapConfig = {
                attributionControl: false,
                center: currentCoord,
                zoom: zoomLevel,
            };

            var container = L.DomUtil.get('map');
            if(container != null){
                container._leaflet_id = null;
            }

            mapObj = L.map(mapId, mapConfig);

            var iconMarkRed = new  L.Icon({
                iconUrl: 'images/marker-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var iconMarkGreen = new  L.Icon({
                iconUrl: 'images/marker-green.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var iconMarkBlue = new  L.Icon({
                iconUrl: 'images/marker-icon.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var configIcon = {icon: iconMarkBlue};
            if(checkLocation != 1) {
                configIcon = {icon: iconMarkRed};
            }

            // Add tile
            L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mapObj);

            var circleConfig = {
                color: '#38c172',
                fillColor: '#f03',
                fillOpacity: 0.1,
                radius: distanceLimit * 1
            }

            allowPositions.forEach(e => {
                var marker = L.marker(e.coord, {icon: iconMarkGreen}).addTo(mapObj);
                var circle = L.circle(e.coord, circleConfig).addTo(mapObj);
                circle.bindTooltip(e.address)
            })


            var marker = L.marker(currentCoord, configIcon).addTo(mapObj);
            // Get address
            //$.get(`https://nominatim.openstreetmap.org/reverse.php?lat=${currentCoord.lat}&lon=${currentCoord.lon}&zoom=18&format=jsonv2`).then(data => {
                    // Add marker

                   // marker.bindPopup(data?.display_name)
               // })
        }

        function showMap($this) {
            var lat = $this.data("lat");
            var long = $this.data("long");
            var checkLocation = $this.data("check-location");
            //  add marker
            var myPosition = { lat: lat, lon: long };
            var allowPositions = jQuery.parseJSON('<?= htmlspecialchars($allowPositions, ENT_NOQUOTES) ?>');
            setTimeout(function() {
                addMarker(myPosition, allowPositions, checkLocation);
            }, 100);
        }
        $('#update_data_month').click(function () {
            $('#frm_update_data_month').submit();
        })

    </script>
@endsection
