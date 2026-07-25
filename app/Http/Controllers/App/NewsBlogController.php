<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\NewsArticle;

class NewsBlogController extends Controller
{
    public function news()
    {
        $articles = NewsArticle::latest('published_at')->paginate(12);

        return view('app.news.index', compact('articles'));
    }

    public function blog()
    {
        $posts = BlogPost::where('status', 'published')->latest('published_at')->paginate(9);

        return view('app.blog.index', compact('posts'));
    }
}
