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

Route::get('articles-cache/get-all', function () {
    return \Illuminate\Support\Facades\Cache::rememberForever('articles.all', function () {
        return \App\Models\Article::all();
    });
});

Route::get('articles-cache-2/get-all', function (ArticleInterface $article) {
    return $article->all();
});

Route::get('get-all-articles', function () {
    $mostRecentlyViewArticles = collect(RedisAlias::zrevrange('articles.1.view', 0, 2))->map(function ($articleId) {
        return \App\Models\Article::find($articleId);
    });

    $articles = \App\Models\Article::all();
    return view('articles.index', compact('mostRecentlyViewArticles', 'articles'));
})->name('get-all-articles');

//Route::get('get-most-recent-view-articles', function () {
//    $mostRecentlyViewArticleIds = RedisAlias::zrevrange('articles.1.view', 0, 2);
//    if (count($mostRecentlyViewArticleIds)) {
//        $mostRecentlyViewArticles = collect($mostRecentlyViewArticleIds)->map(function ($articleId) {
//            return \App\Models\Article::find($articleId);
//        });
//
//        return view('articles.index')->withArticles($mostRecentlyViewArticles);
//    }
//})->name('get-all-articles');

Route::get('articles/{article}/show', function (\App\Models\Article $article) {
    RedisAlias::zadd('articles.1.view', [$article->id => time()]);

    return view('articles.show')->withArticle($article);
})->name('get-all-articles-detail');


App::bind(ArticleInterface::class, function () {
    return new EloquentArticle();
});

interface ArticleInterface
{
    public function all();
}

class EloquentArticle implements ArticleInterface
{
    public function all()
    {
        return \App\Models\Article::all();
    }
}

class CacheableArticle implements ArticleInterface
{
    private EloquentArticle $eloquenArticle;

    public function __construct(EloquentArticle $eloquenArticle)
    {
        $this->eloquenArticle = $eloquenArticle;
    }

    public function all()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('articles.all', function () {
            return $this->eloquenArticle->all();
        });
    }
}
