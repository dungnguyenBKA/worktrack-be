<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', function () {
    return redirect('login');
});

Route::group(['middleware' => 'auth', 'namespace' => 'Admin'], function () {
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('/profile/me', 'UserController@profile')->name('users.profile');
    Route::get('/request-absent/{request_absent}/approve', 'RequestAbsentController@approve')->name('request-absent.approve');
    Route::post('/request-absent/approve-or-reject', 'RequestAbsentController@approveOrRejectAll')->name('request-absent.approve-or-reject');
    Route::get('/overtimes/{overtime}/approve', 'OvertimeController@approve')->name('overtimes.approve');
    Route::post('/overtime/approve-or-reject', 'OvertimeController@approveOrRejectAll')->name('overtime.approve-or-reject');
    Route::resource('/users', 'UserController');
    Route::resource('/request-absent', 'RequestAbsentController');
    Route::resource('/overtimes', 'OvertimeController');
    Route::resource('/config', 'ConfigController')->middleware('admin');
    Route::resource('/notification', 'NotificationController')->middleware('admin');
    Route::resource('/roles', 'RoleController')->middleware('admin');
    Route::resource('/work-titles', 'WorkTitleController')->middleware('admin');
    Route::get('/timesheets', 'DashboardController@index');
    Route::get('/logout', 'AuthController@logout');
    Route::get('/password/change', 'AuthController@changePasswordEdit')->name('change-password.edit');
    Route::put('/password/change', 'AuthController@changePasswordUpdate')->name('change-password.update');
    Route::get('timesheet/getData', '\App\Http\Controllers\Admin\TimeSheetController@getData')->name('timesheet.getData');
    Route::resource('national-day', '\App\Http\Controllers\Admin\NationalDayController')->middleware('admin');
    Route::resource('timesheet', '\App\Http\Controllers\Admin\TimeSheetController')->except(['show']);
    Route::resource('comments', '\App\Http\Controllers\Admin\CommentController');
    Route::put('timesheet/update-paid-leave/{id}', '\App\Http\Controllers\Admin\TimeSheetController@updatePaidLeave')
            ->name('timesheet.updatePaidLeave');
    Route::post('timesheet/upload-timesheet', '\App\Http\Controllers\Admin\TimeSheetController@uploadTimesheet')
            ->name('timesheet.uploadTimesheet')
            ->middleware('can:timesheet.uploadTimesheet');
    Route::get('timesheet/export-timesheet', '\App\Http\Controllers\Admin\TimeSheetController@ExportTimesheet')
            ->name('timesheet.exportTimesheet');
    Route::get('timesheet/create', '\App\Http\Controllers\Admin\TimeSheetController@create')
        ->name('timesheet.create')->middleware('can:timesheet.create');
    Route::get('timesheet/edit/{timesheet}', '\App\Http\Controllers\Admin\TimeSheetController@edit')
        ->name('timesheet.edit')->middleware('can:timesheet.update');
    Route::delete('timesheet/destroy/{timesheet}', '\App\Http\Controllers\Admin\TimeSheetController@destroy')
        ->name('timesheet.destroy')->middleware('can:timesheet.delete');
    Route::resource('/position', 'PositionController')->middleware('admin');
    Route::post('timesheet/update-all-month', '\App\Http\Controllers\Admin\TimeSheetController@updateMonthAll')
            ->name('timesheet.updateMonthAll')->middleware('admin');
    Route::post('users/upload-users', '\App\Http\Controllers\Admin\UserController@uploadUsers')
            ->name('users.uploadUsers')
            ->middleware('admin');
    Route::get('request-absents/export', '\App\Http\Controllers\Admin\RequestAbsentController@exportRequestAbsent')
            ->name('request-absent.exportRequestAbsent');
    Route::get('overtime/export', '\App\Http\Controllers\Admin\OvertimeController@exportOvertime')
            ->name('overtime.exportOvertime');
});
