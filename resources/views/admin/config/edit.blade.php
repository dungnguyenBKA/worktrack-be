@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
        <form method="POST" action="{{ route('config.update', $config->id) }}">
            @csrf <input name="_method" value="PUT" hidden>
            <div class="card p-3 rounded-lg">
                <table class="table table-borderless">
                    <tbody>
                    <tr>
                        <th  class="w-25">{{ __('layouts.position_limit') }}</th>
                        <td>
                            <input class="form-control @error('position_limit') is-invalid @enderror" type="number" name="position_limit" min="1" max="100" value="{{ $config->position_limit }}">
                            @error('position_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('layouts.distance_limit') }}</th>
                        <td>
                            <input class="form-control @error('distance_limit') is-invalid @enderror" type="number" name="distance_limit" min="0" value="{{ $config->distance_limit }}">
                            @error('distance_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="card p-3 rounded-lg">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th  class="w-25">{{ __('layouts.work_days') }}</th>
                            <td>
                                <div class="form-group row">
                                    @foreach($dayOfWeek as $key => $value)
                                    <div class="col-md-3">
                                        <input type="checkbox" name="work_days[]" id="workDay{{$key}}" value="{{ $key }}"
                                        @if(in_array($key, old('work_days', json_decode($config->work_days)))) checked @endif>
                                        <label for="workDay{{$key}}">{{ $value }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th  class="w-25">{{ __('layouts.start_morning') }}</th>
                            <td>
                                <input class="form-control @error('start') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="start" value="{{ $config->start ? \Carbon\Carbon::make($config->start)->format('H:i') : '' }}">
                                @error('start')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('layouts.end_morning') }}</th>
                            <td>
                                <input class="form-control @error('end_morning') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="end_morning" value="{{ $config->end_morning ? \Carbon\Carbon::make($config->end_morning)->format('H:i') : '' }}">
                                @error('end_morning')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th  class="w-25">{{ __('layouts.start_afternoon') }}</th>
                            <td>
                                <input class="form-control @error('start_afternoon') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="start_afternoon" value="{{ $config->start_afternoon ? \Carbon\Carbon::make($config->start_afternoon)->format('H:i') : '' }}">
                                @error('start_afternoon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('layouts.end_afternoon') }}</th>
                            <td>
                                <input class="form-control @error('end') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="end" value="{{ $config->end ? \Carbon\Carbon::make($config->end)->format('H:i') : '' }}">
                                @error('end')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th data-toggle="tooltip" title="{{ __('layouts.offset_time_tooltip') }}">{{ __('layouts.offset_time') }} &#9432;</th>
                            <td>
                                <input class="form-control @error('offset_time') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="offset_time" value="{{ $config->offset_time ? \Carbon\Carbon::make($config->offset_time)->format('H:i') : '' }}">
                                @error('offset_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('layouts.start_normal_OT') }}</th>
                            <td>
                                <input class="form-control @error('start_normal_OT') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="start_normal_OT" value="{{ $config->start_normal_OT ? \Carbon\Carbon::make($config->start_normal_OT)->format('H:i') : '' }}">
                                @error('start_normal_OT')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('layouts.start_night_OT') }}</th>
                            <td>
                                <input class="form-control @error('start_night_OT') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="start_night_OT" value="{{ $config->start_night_OT ? \Carbon\Carbon::make($config->start_night_OT)->format('H:i') : '' }}">
                                @error('start_night_OT')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('layouts.end_night_OT') }}</th>
                            <td>
                                <input class="form-control @error('end_night_OT') is-invalid @enderror" type="text" placeholder="12:00"
                                       name="end_night_OT" value="{{ $config->end_night_OT ? \Carbon\Carbon::make($config->end_night_OT)->format('H:i') : '' }}">
                                @error('end_night_OT')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                <button class="btn btn-dark btn-width-default">{{ __('layouts.save') }}</button>
            </div>
        </form>
</div>
@endsection


@section('script')
<script>
    var selectedUsers = JSON.parse('@json($selectedUsers)')
    var optionUsers = JSON.parse('@json($optionUsers)')

    function onPeriodChange() {
        var item = $("select.period").find(":selected")
        var isHidden = item.val() === "{{ config('common.report_config.period.day') }}"
        $("select.day-of-week").attr("hidden", isHidden)
    }

    onPeriodChange()

    $("select.period").change(onPeriodChange)

    $(".selected-btn").each((index, element) => {
        var item = $(element)
        item.click(() => {
            var id = $(element).children("input").val()
            var email = item.text()
            var html = `<option value="${id}">${email}</option>`;
            $("select.option-users").append(html)
            item.remove()
        })
    })

    $("select.option-users").change(() => {
        // option list
        var item = $("select.option-users").find(":selected")
        var id = item.val()
        var selectedId = "selected_" + item.val()
        var email = item.text()

        var html = `<a id="${selectedId}" class="selected-btn btn btn-outline-dark mr-1">${email}
            <input hidden value="${id}" name="selected_ids[]"></a>`

        $("div.list").append(html)
        item.remove()

        // selected list
        $("#" + selectedId).click(() => {
            var html = `<option value="${id}">${email}</option>`;
            $("select.option-users").append(html)
            $("#" + selectedId).remove()
        })
    })

    $("#input-email").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("option").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
</script>
@endsection
