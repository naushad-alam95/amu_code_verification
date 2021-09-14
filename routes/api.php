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

/**************** Start Auth Routes *****************/
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'API\v1\AuthController@login');
    
    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        
        Route::get('user', 'API\v1\UserController@getData');
        Route::get('basicInfo', 'API\v1\UserController@getBasicInfo');
        Route::get('contact', 'API\v1\UserController@getContact');
        Route::get('studyMaterial', 'API\v1\UserController@getStudyMaterial');
        Route::resource('qualification', 'API\v1\UserQualificationController');
        Route::resource('userContact', 'API\v1\UserContactController');

    });

    /* Forget Password*/
    Route::post('checkValidEmpId', 'API\v1\UserController@checkValidEmpId');
    
});
/****************  End Auth Routes  *****************/


Route::fallback(function () {
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@amu.com'
    ], 404);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*****************Common Routes*****************/
Route::get('/users', 'API\v1\HomeController@userSearch');
Route::get('/departments', 'API\v1\HomeController@departmentSearch');
Route::get('/header-menu', 'API\v1\HomeController@headerMenu');
Route::get('/footer-menu', 'API\v1\HomeController@footerMenu');
Route::get('/left-side-menu', 'API\v1\HomeController@leftSideMenu');
Route::get('/home-slider', 'API\v1\HomeController@homeSlider');
Route::get('/home-widget', 'API\v1\HomeController@widget');
Route::get('/main-widget', 'API\v1\HomeController@mainWidget');
Route::get('/staff-list', 'API\v1\UserController@index');
Route::get('/about-us/{slug?}', 'API\v1\CMSController@index');
Route::get('/annual-report', 'API\v1\AnnualReportController@index');
Route::get('/university-gazette', 'API\v1\UniversityGazetteController@index');
Route::get('/rti', 'API\v1\RTIController@index');
Route::get('/flipbook', 'API\v1\FlipBookController@index');
Route::get('/detailFlipBook', 'API\v1\FlipBookController@detailFlipBook');
Route::get('/menu', 'API\v1\HomeController@menu');
Route::get('/getHomeUpdated', 'API\v1\HomeController@getHomeUpdated');