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
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::group([
    'prefix' => '/v1'//version change
], function () {
    Route::post('register', 'App\Http\Controllers\ApiController@register');
    Route::post('purchase', 'App\Http\Controllers\ApiController@purchase');
    Route::post('checkSubscription', 'App\Http\Controllers\ApiController@checkSubscription');
    Route::post('iosMockApi', 'App\Http\Controllers\ApiController@iosMockApi');
    Route::post('googleMockApi', 'App\Http\Controllers\ApiController@googleMockApi');






    //Route::post('register', 'AuthController@register');

});
