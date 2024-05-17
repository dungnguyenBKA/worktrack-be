<?php

use PhpOffice\PhpSpreadsheet\Chart\Layout;

$menuItems = [
    __('layouts.dashboard') => [
        'url' => '/dashboard',
        'icon' => 'fa-tachometer-alt',
    ],
    __('layouts.users_management') => [
        'admin',
        'url' => '/users',
        'icon' => 'fa-users',
    ],
    __('layouts.timesheet') => [
        'url' => '/timesheet',
        'icon' => 'fa-th',
    ],
    __('layouts.absence_request') => [
        'url' => '/request-absent',
        'icon' => 'fa-tape',
    ],
    __('layouts.overtime_request') => [
        'url' => '/overtimes',
        'icon' => 'fa-user-clock',
    ],
    __('layouts.config') => [
        'admin',
        'url' => '/config',
        'icon' => 'fa-cog',
    ],
    __('layouts.national_holiday') => [
        'admin',
        'url' => '/national-day',
        'icon' => 'fa-th',
    ],
    __('layouts.work_location') => [
        'admin',
        'url' => '/position',
        'icon' => 'fa-map-marker-alt',
    ],
    __('layouts.notification') => [
        'admin',
        'url' => '/notification',
        'icon' => 'fa-envelope-open-text',
    ],
    __('layouts.role') => [
        'admin',
        'url' => '/roles',
        'icon' => 'fa-envelope-open-text',
    ],
    __('layouts.work_title') => [
        'admin',
        'url' => '/work-titles',
        'icon' => 'fa-envelope-open-text',
    ],
]

?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        <img src="{{ asset('images/admin-logo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Work Track</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                @foreach ( $menuItems as $name => $item )
                @roles('user') @if (in_array('admin', $item)) @continue @endif @endroles
                <li class="nav-item">
                    <a href="{{ $item['url'] }}" class="nav-link">
                        <i class="nav-icon fas {{ $item['icon'] }}"></i>
                        <p>
                            {{ $name }}
                        </p>
                    </a>
                </li>
                @endforeach
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
