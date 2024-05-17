<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'not-authen', 'namespace' => 'API'], function () {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::post('/face-login', 'AuthController@loginByFaceId');
    Route::post('/reset-access-token', 'AuthController@resetAccessToken');
    Route::post('/sync-time-data', 'TimesheetController@syncTimeData');
    // Route::post('/password-forgot', [AuthController::class, 'passwordForgot']);

    Route::post('/create-env/{subdomain}', 'SetupController@createEnv');
    Route::post('/maintenance', 'SetupController@maintenance')->name('maintenance');
    Route::get('now', 'SetupController@getNow')->name('now');
});

Route::group(['middleware' => 'is-authen', 'namespace' => 'API'], function () {
    Route::post('/logout', 'AuthController@logout');
    Route::post('/face-register', 'AuthController@faceRegister');
    Route::post('/check-in-out', 'TimesheetController@checkInOut')->middleware('white-list-ip');

    // Kien added
    Route::get('/check-in-out', 'TimesheetController@getTimesheet');
    Route::resource('comments', '\App\Http\Controllers\API\CommentController');

    Route::get('/profile', 'UserController@getUserProfile');
    Route::put('/profile', 'UserController@updateUserProfile');
    Route::put('/password-change', 'UserController@passwordChange');
    Route::resource('/request-absents', 'RequestAbsentController');
    Route::resource('/overtimes', 'OvertimeController');
    Route::get('/notifications', 'NotificationController@index');
    Route::get('/notifications/{id}', 'NotificationController@detail');
});
