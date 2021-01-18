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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::post('user/register','UserController@register');

Route::post('user/login', 'UserController@login');

Route::put('loan/{loan_request}/activate', 'LoanController@activateLoan');

Route::middleware('auth:api')->group(function () {
    
    Route::get('loan/{id}', 'LoanController@getLoanWithSchedules');

    Route::get('card/{reference}', 'UserController@addCard');

    Route::post('card', 'UserController@chargeCard');

    Route::post('loan', 'LoanController@createLoan');




    //Route::post('login', 'UserController@login');
    

	
});