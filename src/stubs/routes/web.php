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

Route::get('login', '\App\Http\Controllers\LoginController@login')->name('login');
Route::post('login', '\App\Http\Controllers\LoginController@loginPost')->name('loginPost');

//newsletter tracking
Route::get('tracker', 'Core\Controllers\ReportController@tracker');
Route::get('unsubscribe', 'Core\Controllers\ReportController@unsubscribe');
Route::get('prodotti', '\App\Http\Controllers\PagesController@indexProdotti')->middleware(Jacofda\Core\Http\Middleware\SaveTracking::class);
Route::get('prodotti/{id}', '\App\Http\Controllers\PagesController@showProdotti')->middleware(Jacofda\Core\Http\Middleware\SaveTracking::class);
