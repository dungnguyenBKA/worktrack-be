<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page_title ?? config('app.name', 'Neos Viet Nam') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bs-custom-file-input.min.js') }}" defer></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/custom.js') }}" defer></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" defer></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.vi.min.js') }}" charset="UTF-8"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .bg-graylight {
            background: #D3D5D9;
        }
        .btn-width-default {
            width: 100px;
        }
        .table-striped-reverse > tbody > tr:nth-child(odd)>td {
            background: #FFFFFF;
        }
        .table-striped-reverse > tbody > tr:nth-child(even)>td {
            background: #EEEEEE;
        }
    </style>
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @yield('style')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    @auth
    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Sidebar Container -->
    @include('layouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper bg-graylight">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $page_title ?? ''}}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('layouts.home_page') }}</a></li>
                            <li class="breadcrumb-item active">{{ $page_title ?? ''}}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        @yield('content')
        <!-- /.content -->
    </div>

    <!-- Modal confirm delete -->
    <div class="modal fade show" id="modal-delete" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div>{{ __('messages.delete_confirm') }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('layouts.close') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-delete">{{ __('layouts.delete') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <!-- Modal confirm approve all -->
    <div class="modal fade show" id="modal-approve-or-reject" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="approve-or-reject"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('layouts.close') }}</button>
                    <button type="button" class="btn btn-danger btn-approve-or-reject">{{ __('layouts.label-approve-or-reject') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.content-wrapper -->
    @endauth

    <!-- Guest -->
    @guest
    <div class="content-wrapper m-0">
        <div style="transform: translateY(150px);">
            @yield('content')
        </div>
    </div>
    @endguest

    @auth
    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <strong>Work track version</strong><span>1.0</span>
    </footer>
    @endauth
</div>
<!-- ./wrapper -->
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function () {
      bsCustomFileInput.init();
      $('.select2').select2();
    });

    //date
    $('.date').daterangepicker({
        timePicker: false,
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD'
        },
    });
    $('.date-up').daterangepicker({
        timePicker: false,
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD'
        },
        drops: 'up'
    });
    $('.date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    $('.date-up').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    //date-time
    $('.date-time').daterangepicker({
        timePicker: true,
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        timePicker24Hour: true,
        locale: {
            format: 'YYYY-MM-DD H:mm',
            daysOfWeek: [
            " {{ __('layouts.daysOfWeek_sun') }} ",
            " {{ __('layouts.daysOfWeek_mon') }} ",
            " {{ __('layouts.daysOfWeek_tue') }} ",
            " {{ __('layouts.daysOfWeek_wed') }} ",
            " {{ __('layouts.daysOfWeek_thu') }} ",
            " {{ __('layouts.daysOfWeek_fri') }} ",
            " {{ __('layouts.daysOfWeek_sat') }} "
            ],
            monthNames: [
            " {{ __('layouts.monthName_01') }} ",
            " {{ __('layouts.monthName_02') }} ",
            " {{ __('layouts.monthName_03') }} ",
            " {{ __('layouts.monthName_04') }} ",
            " {{ __('layouts.monthName_05') }} ",
            " {{ __('layouts.monthName_06') }} ",
            " {{ __('layouts.monthName_07') }} ",
            " {{ __('layouts.monthName_08') }} ",
            " {{ __('layouts.monthName_09') }} ",
            " {{ __('layouts.monthName_10') }} ",
            " {{ __('layouts.monthName_11') }} ",
            " {{ __('layouts.monthName_12') }} "
            ],
            applyLabel: " {{ __('layouts.applyLabel') }} ",
            cancelLabel: " {{ __('layouts.cancelLabel') }} "
        }
    });
    $('.date-time').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD H:mm'));
    });

    // $('.date, .date-time').on('cancel.daterangepicker', function(ev, picker) {
    //     $(this).val('');
    // });

    $('.date, .date-time').on('keydown', function (e) {
        e.preventDefault();
    })

    $('.delete').on('click', function (e) {
        var $this = $(this);
        e.preventDefault();
        $('#modal-delete').modal('show');
        $('#btn-delete').on('click', function () {
            $this.parents('form').submit();
            $('#btn-delete').prop('disabled', true);
        })
    })

    $('.date-month').datepicker({
        format: "yyyy-mm",
        startView: "months",
        minViewMode: "months",
        language: "vi"
    })

    $('#search_date').datepicker({
        format: "yyyy/mm",
        startView: "months",
        minViewMode: "months",
        language: "vi"
    })

    $('#overtimes-check').on('click', function(e) {
        if($(this).is(':checked',true)) {
            $(".overtime-check").prop('checked', true);
        } else {
            $(".overtime-check").prop('checked',false);
        }
    });

    $('#absents-check').on('click', function(e) {
        if($(this).is(':checked',true)) {
            $(".request-absent-check").prop('checked', true);
        } else {
            $(".request-absent-check").prop('checked',false);
        }
    });

    $('.approve_overtime_all').on('click', function (e) {
        openModal(e, "{{ __('messages.approve-all')}}", "btn-approve-overtime-all", "overtime-check")
    })

    $('.reject_overtime_all').on('click', function (e) {
        openModal(e, "{{ __('messages.reject-all')}}", "btn-reject-overtime-all", "overtime-check")
    })

    $('.approve_request_all').on('click', function (e) {
        openModal(e, "{{ __('messages.approve-all')}}", "btn-approve-request-all", "request-absent-check")
    })

    $('.reject_request_all').on('click', function (e) {
        openModal(e, "{{ __('messages.reject-all')}}", "btn-reject-request-all", "request-absent-check")
    })

    $("body").delegate('#close-message','click', function () {
        $('#modal-approve-or-reject').modal('hide');
    })

    $("body").delegate('#btn-approve-overtime-all','click', function () {
        callAjax('approve_overtime_all', "{{ route('overtimes.index')}}", "overtime-check");
    })

    $("body").delegate('#btn-reject-overtime-all','click', function () {
        callAjax('reject_overtime_all', "{{ route('overtimes.index')}}", "overtime-check");
    })

    $("body").delegate('#btn-approve-request-all','click', function () {
        callAjax('approve_request_all', "{{ route('request-absent.index')}}", "request-absent-check");
    })

    $("body").delegate('#btn-reject-request-all','click', function () {
        callAjax('reject_request_all', "{{ route('request-absent.index')}}", "request-absent-check");
    })

    function callAjax(className, route, classChecked) {
        let valSelected = getSelected(classChecked);
        $.ajax({
            type: 'POST',
            beforeSend: function (request) {
                return request.setRequestHeader('X-CSRF-Token', $("meta[name='csrf-token']").attr('content'));
            },
            url: $(`.${className}`).data('route'),
            data: {
                'ids': valSelected,
                'type': $(`.${className}`).val()
            },
            success: function (response, textStatus, xhr) {
                $('#modal-approve-or-reject').modal('hide');
                window.location.href = response.url.length > 1 ? `${route}?${response.url[1]}`: route;
            }
        });
    }

    function getSelected(classChecked) {
        let valSelected = [];
        $(`.${classChecked}:checked`).each(function() {
            valSelected.push($(this).attr('data-id'));
        });

        return valSelected;
    }

    function openModal(e, message, id, classChecked) {
        e.preventDefault();
        let valSelected = getSelected(classChecked);
        if(valSelected.length > 0) {
            $('.approve-or-reject').text(message)
            $('.btn-approve-or-reject').attr('id', id)
            $('#modal-approve-or-reject').modal('show');
        } else {
            $('.approve-or-reject').text("{{ __('messages.select-option')}}")
            $('.btn-approve-or-reject').attr('id', 'close-message')
            $('#modal-approve-or-reject').modal('show');
        }
    }

</script>
@yield('script')
</body>
</html>
