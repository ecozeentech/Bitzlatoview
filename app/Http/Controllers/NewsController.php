<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;

class NewsController extends Controller
{
    public function index()
    {
        $articles = NewsArticle::latest('published_at')->paginate(12);

        return view('public.news.index', compact('articles'));
    }
}
