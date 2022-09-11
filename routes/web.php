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

Route::get('/videos/{id}', function ($id) {
    $downloadCount = RedisAlias::get("videos.$id.download-count");

    return view('welcome')->with('downloadCount', $downloadCount);
});


Route::get('/videos/download/{id}', function ($id) {
     RedisAlias::incr("videos.$id.download-count");

     return back();
});

