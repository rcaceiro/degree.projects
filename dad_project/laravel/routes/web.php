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

Route::get('login/{provider}', 'MyAuth\\MyLoginController@redirectToProvider');
Route::get('login/{provider}/callback', 'MyAuth\\MyLoginController@handleProviderCallback');

Route::any('/{all}', function () {
    return view('index');
})->where(['all' => '.*'])->name('index');
