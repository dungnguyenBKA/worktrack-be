@extends('layouts.app')

@section('content')
<div class="container-fluid pb-3">
    @isset($user)
    @include('layouts.alert')
    <div class="card p-3 mb-5 rounded-lg">
        <table class="table table-striped text-break table-borderless">
            <tr>
                <th>{{ __('layouts.staff_id') }}</th>
                <td>{{ $user->staff_id ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.first_name') }}</th>
                <td>{{ $user->first_name ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.last_name') }}</th>
                <td>{{ $user->last_name ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.email') }}</th>
                <td>{{ $user->email ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.birth') }}</th>
                <td>{{ $user->birth ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.address') }}</th>
                <td>{{ $user->address ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.phone_number') }}</th>
                <td>{{ $user->phone_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.role') }}</th>
                <td>
                    @if(config('app.locale') == 'vi' && $user->roles && $user->roles->name_vi)
                        {{ $user->roles->name_vi }}
                    @elseif(config('app.locale') == 'ja' && $user->roles && $user->roles->name_ja)
                        {{ $user->roles->name_ja }}
                    @else
                        {{ $user->roles ? $user->roles->name_en : '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ __('layouts.status') }}</th>
                <td>{{ __('common.user.status')[$user->status] ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.face_id') }}</th>
                <td>{{ $user->face_id ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('layouts.work_title') }}</th>
                <td>
                    @if(config('app.locale') == 'vi' && $user->workTitle && $user->workTitle->name_vi)
                        {{ $user->workTitle->name_vi }}
                    @elseif(config('app.locale') == 'ja' && $user->workTitle && $user->workTitle->name_ja)
                        {{ $user->workTitle->name_ja }}
                    @else
                        {{ $user->workTitle ? $user->workTitle->name_en : '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ __('layouts.start_work_from') }}</th>
                <td>{{ $user->date_start_work ?? '-' }}</td>
            </tr>
        </table>
        <div class="d-flex justify-content-end">
            <a class="btn btn-primary mr-2 btn-width-default" href="{{ route('users.edit', $user->id) }}">{{ __('layouts.edit') }}</a>
            @roles('admin')
            <form method="POST" action="{{ route('users.destroy', $user->id) }}">
                @csrf <input type="hidden" name="_method" value="DELETE">
                <input type="submit" value="{{ __('layouts.delete') }}" class="btn btn-danger mr-2 btn-width-default delete">
            </form>
            @endroles
            <a class="btn btn-dark btn-width-default" href="{{ route($redirect) }}">{{ __('layouts.close') }}</a>
        </div>
        @endisset
    </div>
</div>
@endsection
