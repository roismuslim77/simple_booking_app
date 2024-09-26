<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1/'], function () {
    Route::get('/room', 'RoomController@getListRoom');
    Route::post('/room',  'RoomController@createRoom');
    Route::put('/room/{id}',  'RoomController@updateRoom');
    Route::delete('/room/{id}',  'RoomController@deleteRoom');

    Route::get('/rateplan', 'RatePlanController@getListRatePlan');
    Route::post('/rateplan',  'RatePlanController@createRatePlan');
    Route::put('/rateplan/{id}',  'RatePlanController@updateRatePlan');
    Route::delete('/rateplan/{id}',  'RatePlanController@deleteRatePlan');

    Route::get('/calendar', 'CalendarController@getListCalendar');
    Route::post('/calendar',  'CalendarController@createCalendar');
    Route::put('/calendar/{id}',  'CalendarController@updateCalendar');
    Route::delete('/calendar/{id}',  'CalendarController@deleteCalendar');

    Route::post('/booking/request',  'BookingController@requestBooking');
    Route::patch('/booking/cancel/{id}',  'BookingController@cancelBooking');

    Route::get('/booking', 'BookingController@getListBooking');
    Route::post('/booking',  'BookingController@createBooking');
    Route::put('/booking/{id}',  'BookingController@updateBooking');
    Route::delete('/booking/{id}',  'BookingController@deleteBooking');


    Route::get('/statistic/revenue', 'StatisticController@getRevenueCount');
});
