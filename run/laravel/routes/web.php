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

    if (!Google\Auth\Credentials\GCECredentials::onGce()) {
        return view('welcome', [
            'service' => 'Unknown',
            'revision' => 'Unknown',
            'project' => 'Unknown',
            'region' => 'Unknown'
        ];
    }
    // [START cloudrun_laravel_display_metadata]
    $metadata = new Google\Cloud\Core\Compute\Metadata();
    $longRegion = explode('/', $metadata->get('instance/region'));
    
    return view('welcome', [
        'service' => env('K_SERVICE'),
        'revision' => env('K_REVISION'),
        'project' => $metadata->get('project/project-id'),
        'region' => end($longRegion),
    ]);
    // [END cloudrun_laravel_display_metadata]
});

// A basic CRUD example
Route::resource('products', ProductController::class);
