<?php

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

Route::get('/', function () {
    return redirect('zones');
});

Route::get('google/link', 'App\Http\Controllers\AccountController@startGoogleLink')->middleware('auth');
Route::get('google/oauth_callback', 'App\Http\Controllers\AccountController@finishGoogleLink')->middleware('auth');

Route::get('login', ['as' => 'login', 'uses' => 'App\Http\Controllers\UserController@startLogin']);
Route::get('logout', 'App\Http\Controllers\UserController@startLogout');

Route::get('zones', 'App\Http\Controllers\ZoneController@showZoneList')->middleware('auth');
Route::get('zones/new', 'App\Http\Controllers\ZoneController@showNewZone')->middleware('auth');
Route::post('zones/new', 'App\Http\Controllers\ZoneController@doNewZone')->middleware('auth');
Route::get('zones/delete/{id}', 'App\Http\Controllers\ZoneController@doDeleteZone')->middleware('auth');
Route::get('zones/permissions/list/{id}', 'App\Http\Controllers\ZoneController@showZonePermissions')->middleware('auth');
Route::post('zones/permissions/add/{id}', 'App\Http\Controllers\ZoneController@doAddZonePermission')->middleware('auth');
Route::get('zones/permissions/remove/{id}', 'App\Http\Controllers\ZoneController@doRemoveZonePermission')->middleware('auth');
Route::get('zones/content/list/{id}', 'App\Http\Controllers\ZoneController@showZoneContent')->middleware('auth');
Route::get('zones/content/deleteall/{id}', 'App\Http\Controllers\ZoneController@doDeleteAllZoneContent')->middleware('auth');
Route::get('zones/content/add/pic/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddPicture')->middleware('auth');
Route::post('zones/content/add/pic/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddPicture')->middleware('auth');
Route::get('zones/content/add/video/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddVideo')->middleware('auth');
Route::post('zones/content/add/video/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddVideo')->middleware('auth');
Route::get('zones/content/add/zone/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddZone')->middleware('auth');
Route::post('zones/content/add/zone/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddZone')->middleware('auth');
Route::get('zones/content/add/slides/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddSlides')->middleware('auth');
Route::post('zones/content/add/slides/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddSlides')->middleware('auth');
Route::get('zones/content/add/pdf/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddPDF')->middleware('auth');
Route::post('zones/content/add/pdf/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddPDF')->middleware('auth');
Route::get('zones/content/add/text/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddText')->middleware('auth');
Route::post('zones/content/add/text/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddText')->middleware('auth');
Route::get('zones/content/add/weather/{id}', 'App\Http\Controllers\ZoneAddController@showZoneAddWeather')->middleware('auth');
Route::post('zones/content/add/weather/{id}', 'App\Http\Controllers\ZoneAddController@doZoneAddWeather')->middleware('auth');

Route::get('zones/content/remove/{id}', 'App\Http\Controllers\ZoneController@doDeleteZoneContent')->middleware('auth');
Route::get('zones/content/up/{id}', 'App\Http\Controllers\ZoneController@doMoveUpZoneContent')->middleware('auth');
Route::get('zones/content/toggle/{id}', 'App\Http\Controllers\ZoneController@doToggleZoneContent')->middleware('auth');
Route::get('zones/content/refresh/{id}', 'App\Http\Controllers\ZoneController@doRefreshZoneContent')->middleware('auth');
Route::get('zones/content/down/{id}', 'App\Http\Controllers\ZoneController@doMoveDownZoneContent')->middleware('auth');

Route::get('devices', 'App\Http\Controllers\DeviceController@showDeviceList')->middleware('auth');
Route::get('devices/edit/{id}', 'App\Http\Controllers\DeviceController@showEditDevice')->middleware('auth');
Route::post('devices/edit/{id}', 'App\Http\Controllers\DeviceController@doEditDevice')->middleware('auth');
Route::get('devices/delete/{id}', 'App\Http\Controllers\DeviceController@doDeleteDevice')->middleware('auth');

Route::get('account', 'App\Http\Controllers\AccountController@showAccountSettings')->middleware('auth');

Route::get('job/{id}', 'App\Http\Controllers\JobController@showJobStatus')->middleware('auth');

Route::get('api/device/{name}', 'App\Http\Controllers\DeviceController@getDeviceCSV');

Route::get('error', function() {
	return "error";
});
