<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\MarketPair;
use App\Models\NewsArticle;
use App\Models\NftCollection;

class HomeController extends Controller
{
    public function index()
    {
        $markets = MarketPair::with(['baseAsset', 'quote'])->get();

        $topGainers = $markets->sortByDesc(fn ($m) => $m->quote?->change_24h_pct)->take(5);
        $topLosers = $markets->sortBy(fn ($m) => $m->quote?->change_24h_pct)->take(5);

        $news = NewsArticle::latest('published_at')->take(3)->get();
        $blogPosts = BlogPost::where('status', 'published')->latest('published_at')->take(3)->get();
        $collections = NftCollection::orderByDesc('volume')->take(3)->get();

        return view('public.home', [
            'markets' => $markets->take(8),
            'topGainers' => $topGainers,
            'topLosers' => $topLosers,
            'news' => $news,
            'blogPosts' => $blogPosts,
            'collections' => $collections,
        ]);
    }
}
