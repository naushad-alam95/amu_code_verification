<?php

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
Route::get('/endpoints', function () {
    return view('endpoints');
});

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('route:clear');
    $exitCode = Artisan::call('view:clear');
    return 'Clear Your cache'; //Return anything
});

Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');


// Super Admin guard
Route::group(['middleware' => 'App\Http\Middleware\SuperAdminMiddleware'], function()
{
	//dashboard route for super admin
	Route::get('/dashboard', 'HomeController@index')->name('home');
	
});