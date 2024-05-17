@php $fullname = get_fullname(); @endphp
<nav class="main-header navbar navbar-expand navbar-white navbar-light d-flex justify-content-between">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <div class="user-panel d-flex">
        <div class="image">
            <img src="{{ Avatar::create($fullname)->toBase64() }}" class="img-circle" alt="User Image">
        </div>
        <div class="info">
            <a href="/profile/me" class="d-block">{{ $fullname }}</a>
        </div>
        <div class="info">
            <a href="/password/change"><i class="fas fa-exchange-alt m-1"></i>{{ __('layouts.change_password') }}</a>
        </div>
        <div class="info">
            <a href="/logout" onclick="return confirm(`{{ __('messages.logout_confirm') }}`)"><i class="fas fa-sign-out-alt m-1"></i>{{ __('layouts.logout') }}</a>
        </div>
    </div>
</nav>