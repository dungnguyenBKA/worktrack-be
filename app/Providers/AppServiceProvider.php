<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Policies\TimesheetPolicy;
use App\Policies\OvertimePolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Timesheet::class => TimesheetPolicy::class,
        Overtime::class => OvertimePolicy::class
    ];
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        Gate::resource('timesheet', TimesheetPolicy::class);
        Gate::define('timesheet.uploadTimesheet', TimesheetPolicy::class . '@uploadTimesheet');
        Gate::resource('overtimes', OvertimePolicy::class);
        
        $environment = \App::environment();
        if ($environment === 'production' || ($environment === 'local' && env('DEV_LOCAL_HTTPS', false))) {
            $this->app['request']->server->set('HTTPS', 'on');
            \URL::forceScheme('http');
        }

        Blade::if('roles', function ($role) {
            $user = auth()->user();
            return $user && $user->role == config('common.user.role')[$role];
        });
    }
}
