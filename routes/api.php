<?php

use Illuminate\Http\Request;

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Endpoints
Route::post('login', 'Auth\LoginController@login');
Route::post('register', 'Auth\RegisterController@register');

// Endpoints protected by authentication (bearer {api_token})
Route::group(['middleware' => 'auth:api'], function(){

    // Meeting Endpoints
    Route::get('meetings', 'MeetingController@index');
    Route::post('meetings','MeetingController@store');
    Route::get('meetings/{meeting_id}', "MeetingController@show");
    Route::delete('meetings/{meeting_id}', "MeetingController@delete");
    Route::patch('meetings/{meeting_id}', "MeetingController@update");

    // Room Endpoints
    Route::get('rooms', 'RoomController@index');
    Route::get('rooms/{room_id}', 'RoomController@show');
    Route::get('rooms/{room_id}/meetings','RoomController@getMeetings');

    // Invitation Endpoints
});
