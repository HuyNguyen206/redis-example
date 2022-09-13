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

Route::get('/articles/all', function () { // sort by most viewed articles
    $articles = RedisAlias::zrevrange('articles_trending', 0, 3);
    $articlesHydrate = \App\Models\Article::query()->hydrate(array_map('json_decode', $articles));

    return $articlesHydrate;
});

Route::get('/articles/{article}', function (\App\Models\Article $article) {
    RedisAlias::zincrby('articles_trending', 1, $article->toJson());

    return $article;
});

Route::get('users/{userId}/stats/seed', function ($userId) {
    $stat = [
        'favorites' => random_int(10, 100),
        'watchLaters' => random_int(10, 100),
        'completions' => random_int(10, 100),
    ];
    $key = "user.$userId.stats";
    RedisAlias::hmset($key, $stat);

    return RedisAlias::hgetall($key);
});

Route::get('users/{userId}/stats', function ($userId) {
//    \Illuminate\Support\Facades\Cache::put('name', 'huy');
//    dd(\Illuminate\Support\Facades\Cache::get('name'));

    $key = "user.$userId.stats";

    return RedisAlias::hgetall($key);
});

Route::get('users/{userId}/favorite', function ($userId) {
    $key = "user.$userId.stats";
    RedisAlias::hincrby($key, 'favorites', 1);

    return RedisAlias::hgetall($key);
});
