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

Route::get('login', 'Jacofda\Core\Http\Controllers\LoginController@login')->name('login');
Route::post('login', 'Jacofda\Core\Http\Controllers\LoginController@loginPost')->name('loginPost');

//newsletter tracking
Route::get('tracker', 'Jacofda\Core\Http\Controllers\ReportController@tracker');
Route::get('unsubscribe', 'Jacofda\Core\Http\Controllers\ReportController@unsubscribe');
Route::get('prodotti', 'Jacofda\Core\Http\Controllers\PagesController@indexProdotti')->middleware(Jacofda\Core\Http\Middleware\SaveTracking::class);
Route::get('prodotti/{id}', 'Jacofda\Core\Http\Controllers\PagesController@showProdotti')->middleware(Jacofda\Core\Http\Middleware\SaveTracking::class);
