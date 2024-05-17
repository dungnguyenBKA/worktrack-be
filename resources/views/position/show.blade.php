@extends('layouts.app')
@section('style')
<link href="{{ asset('css/leaflet.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid pb-3">
    @isset($position)
    @include('layouts.alert')
    <div class="card p-3 mb-5 rounded-lg">
        <table class="table table-striped text-break table-borderless">
            <tr>
                <th>{{ __('ID') }}</th>
                <td>{{ $position->id ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.type') }}</th>
                <td>{{ config('constants.postion_type')[$position->type] ?? "" }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.staff') }}</th>
                <td>{{ $position->user ? $position->user->staff_id . " " . $position->user->getFullName() : ""; }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.work_location') }}</th>
                <td>
                    {{ $position->position ?? '' }}
                    <div id="map" style="height: 400px;"></div>
                </td>
            </tr>
            <tr>
                <th>{{ __('layouts.status') }}</th>
                <td>{{ config('constants.status')[$position->status] ?? "" }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.time_created') }}</th>
                <td>{{ $position->created_at ?? "" }}</td>
            </tr>
        </table>
        <div class="d-flex justify-content-end">
            <a class="btn btn-primary mr-2 btn-width-default" href="{{ route('position.edit', $position->id) }}">{{ __('layouts.edit') }}</a>
            @roles('admin')
            <form method="POST" action="{{ route('position.destroy', $position->id) }}">
                @csrf <input type="hidden" name="_method" value="DELETE">
                <input type="submit" value="{{ __('layouts.delete') }}" class="btn btn-danger mr-2 btn-width-default delete">
            </form>
            @endroles
            <a class="btn btn-dark btn-width-default" href="{{ route('position.index') }}">{{ __('layouts.close') }}</a>
        </div>
        @endisset
    </div>
</div>
@endsection
@section('script')
    @parent
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <script>
        $(document).ready(function () {
            initMap();
        })
        function initMap() {
            const latitude = parseFloat('{{ $position->latitude }}');
            const longitude = parseFloat('{{ $position->longitude }}');

            var map = L.map('map').setView([latitude, longitude], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            L.marker([latitude, longitude]).addTo(map);
        }
    </script>
@endsection