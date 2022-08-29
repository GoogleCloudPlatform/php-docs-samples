<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;

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
    /** 
     * Populate useful information from the running service. 
     */

    // [START cloudrun_laravel_display_metadata] 
    $long_region = explode("/", request_metadata("instance/region"));

    $view_variables = [
        "service" =>  env("K_SERVICE") ?? "Unknown",
        "revision" => env("K_REVISION") ?? "Unknown",
        "project" => request_metadata("project/project-id"),
        "region" => end($long_region),
    ];

    return view('welcome', $view_variables);
    // [END cloudrun_laravel_display_metadata] 
});


// A basic CRUD example
Route::resource('products', ProductController::class);
