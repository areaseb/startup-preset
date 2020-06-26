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

use App\Classes\{Csv, City, Country, User};
use App\Classes\Contacts\{Contact, Newsletter, NewsletterList, Report, Template};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Mediatypes\MediaEditor;

Route::get('test', function(){

    return 'test';

});


Route::get('login', '\App\Http\Controllers\LoginController@login')->name('login');
Route::post('login', '\App\Http\Controllers\LoginController@loginPost');
Route::get('tracker', 'Contacts\Controllers\ReportController@tracker');
Route::get('unsubscribe', 'Contacts\Controllers\ReportController@unsubscribe');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/', function(){
        return view('welcome');
    });


    Route::group(['prefix' => 'contacts'], function () {
        Route::delete('lists/{list}/contact/{contact}', 'Contacts\Controllers\NewsletterListController@removeContactFromList');
        Route::post('lists/{list}/update', 'Contacts\Controllers\NewsletterListController@updateContacts');
        Route::resource('lists', 'Contacts\Controllers\NewsletterListController');
    });
    Route::resource('contacts', 'Contacts\Controllers\ContactController');


    Route::group(['prefix' => 'templates'], function () {
        Route::get('html/{template}', 'Contacts\Controllers\TemplateController@html');
        Route::post('{template}', 'Contacts\Controllers\TemplateController@update');
    });
    Route::resource('templates', 'Contacts\Controllers\TemplateController')->except(['create', 'edit', 'update']);


    Route::group(['prefix' => 'newsletters'], function () {
        Route::get('{newsletter}/reports', 'Contacts\Controllers\ReportController@index');

        Route::get('{newsletter}/reports/aperte', 'Contacts\Controllers\ReportController@showOpen');
        Route::get('{newsletter}/reports/errore', 'Contacts\Controllers\ReportController@showErrore');
        Route::get('{newsletter}/reports/unsubscribed', 'Contacts\Controllers\ReportController@showUnsubscribed');

        Route::get('{newsletter}/reports/{report}', 'Contacts\Controllers\ReportController@show');
        Route::get('{newsletter}/send-test', 'Contacts\Controllers\NewsletterController@test');
        Route::post('{newsletter}/send-test', 'Contacts\Controllers\NewsletterController@sendTest');
        Route::get('{newsletter}/send', 'Contacts\Controllers\NewsletterController@send');
        Route::post('{newsletter}/send', 'Contacts\Controllers\NewsletterController@sendOfficial');
    });
    Route::resource('newsletters', 'Contacts\Controllers\NewsletterController');

    Route::resource('users', '\App\Http\Controllers\UserController');
    Route::resource('roles', '\App\Http\Controllers\RoleController');
    Route::resource('settings', '\App\Http\Controllers\SettingController')->only(['index', 'edit', 'update']);
    Route::resource('companies', 'Contacts\Controllers\CompanyController');

    Route::group(['prefix' => 'imports'], function () {
        Route::post('peek', 'Contacts\Controllers\ImportController@peek');
        Route::get('{model}', 'Contacts\Controllers\ImportController@importForm');
        Route::post('{model}', 'Contacts\Controllers\ImportController@importUpload');
    });

    Route::group(['prefix' => 'template-builder'], function () {
        Route::get('/', 'Contacts\Controllers\EditorController@editor');
        Route::get('{id}', 'Contacts\Controllers\EditorController@editorWithTemplate');
    });

    Route::group(['prefix' => 'editor'], function () {
        Route::get('elements/{slug}', 'Contacts\Controllers\EditorController@show');
        Route::get('images', 'Contacts\Controllers\EditorController@images');
        Route::post('upload', 'Contacts\Controllers\EditorController@upload');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', '\App\Http\Controllers\NotificationController@index');
        Route::post('{notification}', '\App\Http\Controllers\NotificationController@markAsRead');
        Route::delete('{notification}', '\App\Http\Controllers\NotificationController@destroy');
    });



    Route::post('update-field', '\App\Http\Controllers\GeneralController@updateField');
    Route::post('logout', '\App\Http\Controllers\LoginController@logout');
    Route::post('contacts-validate-file', 'Contacts\Controllers\ContactController@validateFile');
    Route::post('countries', function(){
        return Country::getCountryPhone(request('iso'));
    });

});
