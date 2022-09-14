<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div>
    <h2>All articles</h2>
    <ul>
        @foreach($articles as $article)
            <li>{{$article->id}} - <a href="{{route('get-all-articles-detail', $article->id)}}">{{$article->title}}</a></li>
        @endforeach
    </ul>
</div>

<div>
    <h3>Most recent view</h3>
    <ul>
        @foreach($mostRecentlyViewArticles as $mostRecentlyViewArticle)
            <li>{{$mostRecentlyViewArticle->id}} - <a href="{{route('get-all-articles-detail', $mostRecentlyViewArticle->id)}}">{{$mostRecentlyViewArticle->title}}</a></li>
        @endforeach
    </ul>
</div>
</body>
</html>
