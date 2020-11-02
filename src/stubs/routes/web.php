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

use Jacofda\Core\Http\Middleware\SaveTracking;
use Jacofda\Core\Http\Controllers\{LoginController, PagesController, ReportController};

Route::get('login', [LoginController::class, 'login'])->name('login');
Route::post('login', [LoginController::class, 'loginPost'])->name('loginPost');

//newsletter tracking
Route::get('tracker', [ReportController::class, 'tracker']);
Route::get('unsubscribe', [ReportController::class, 'unsubscribe']);
//Route::get('prodotti', 'Jacofda\Core\Http\Controllers\PagesController@indexProdotti')->middleware(SaveTracking::class);
//Route::get('prodotti/{id}', 'Jacofda\Core\Http\Controllers\PagesController@showProdotti')->middleware(SaveTracking::class);
