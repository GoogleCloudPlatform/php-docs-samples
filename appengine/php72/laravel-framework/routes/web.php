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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/log/{message}', function ($message) {
    Log::info("Hello my log, message: $message");
    return view('welcome');
});

Route::get('/exception/{message}', function ($message) {
    throw new Exception("Intentional exception, message: $message");
});
