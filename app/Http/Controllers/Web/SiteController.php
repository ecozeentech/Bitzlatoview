<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SiteController extends Controller
{

    public function index()
    {
        $pairs = \App\Models\MarketPair::query()->where('is_active', true)->with(['baseAsset','quoteAsset'])->orderByDesc('volume_24h')->limit(12)->get();
        $gainers = \App\Models\MarketPair::query()->where('is_active', true)->orderByDesc('change_24h')->limit(5)->get();
        $losers = \App\Models\MarketPair::query()->where('is_active', true)->orderBy('change_24h')->limit(5)->get();
        $news = \App\Models\NewsArticle::query()->where('status', 'published')->latest('published_at')->limit(3)->get();
        $posts = \App\Models\BlogPost::query()->where('status', 'published')->latest('published_at')->limit(3)->get();
        $traders = \App\Models\CopyTraderProfile::query()->where('status', 'active')->orderByDesc('followers')->limit(4)->get();
        $bots = \App\Models\AiBot::query()->where('is_active', true)->limit(3)->get();
        $mining = \App\Models\MiningPackage::query()->where('is_published', true)->limit(3)->get();
        $nfts = \App\Models\NftCollection::query()->where('is_active', true)->orderByDesc('volume_24h')->limit(4)->get();
        $faqs = \App\Models\FaqItem::query()->where('is_published', true)->orderBy('sort_order')->limit(6)->get();

        return view('public.home', compact('pairs','gainers','losers','news','posts','traders','bots','mining','nfts','faqs'));
    }

    public function markets()
    {
        $pairs = \App\Models\MarketPair::query()->where('is_active', true)->orderByDesc('volume_24h')->paginate(50);
        return view('public.markets', compact('pairs'));
    }

    public function topGainers()
    {
        $pairs = \App\Models\MarketPair::query()->where('is_active', true)->orderByDesc('change_24h')->limit(50)->get();
        $stocks = \App\Models\StockInstrument::query()->where('is_active', true)->orderByDesc('change_24h')->limit(20)->get();
        $nfts = \App\Models\NftCollection::query()->where('is_active', true)->orderByDesc('volume_24h')->limit(20)->get();
        return view('public.markets.top-gainers', compact('pairs','stocks','nfts'));
    }

    public function topLosers()
    {
        $pairs = \App\Models\MarketPair::query()->where('is_active', true)->orderBy('change_24h')->limit(50)->get();
        return view('public.markets.top-losers', compact('pairs'));
    }

    public function newListings()
    {
        return view('public.markets.new-listings');
    }

    public function crypto()
    {
        return view('public.crypto');
    }

    public function stocks()
    {
        return view('public.stocks');
    }

    public function forex()
    {
        return view('public.forex');
    }

    public function futures()
    {
        return view('public.futures');
    }

    public function nft()
    {
        return view('public.nft');
    }

    public function swap()
    {
        return view('public.swap');
    }

    public function buyCrypto()
    {
        return view('public.buy-crypto');
    }

    public function p2p()
    {
        $ads = \App\Models\P2PAd::query()->where('status','active')->where('is_visible',true)->with('asset')->latest()->limit(20)->get();
        return view('public.p2p', compact('ads'));
    }

    public function copyTrading()
    {
        return view('public.copy-trading');
    }

    public function aiTradingBot()
    {
        return view('public.ai-trading-bot');
    }

    public function mining()
    {
        return view('public.mining');
    }

    public function investments()
    {
        return view('public.investments');
    }

    public function metatrader5()
    {
        return view('public.metatrader-5');
    }

    public function news()
    {
        $articles = \App\Models\NewsArticle::query()->where('status', 'published')->latest('published_at')->paginate(12);
        return view('public.news', compact('articles'));
    }

    public function blog()
    {
        $posts = \App\Models\BlogPost::query()->where('status', 'published')->latest('published_at')->paginate(12);
        return view('public.blog', compact('posts'));
    }

    public function blogShow(string $slug)
    {
        $post = \App\Models\BlogPost::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('public.blog-show', compact('post'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function contactSubmit(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email'],
            'subject' => ['nullable','string','max:180'],
            'message' => ['required','string','max:5000'],
        ]);
        \App\Models\ContactMessage::query()->create($data);
        return back()->with('success', 'Message received. Our team will respond shortly.');
    }

    public function faq()
    {
        $faqs = \App\Models\FaqItem::query()->where('is_published', true)->orderBy('sort_order')->get();
        return view('public.faq', compact('faqs'));
    }

    public function fees()
    {
        return view('public.fees');
    }

    public function proofOfReserves()
    {
        return view('public.proof-of-reserves');
    }

    public function security()
    {
        return view('public.security');
    }

    public function apiDocs()
    {
        return view('public.api-docs');
    }

    public function affiliate()
    {
        return view('public.affiliate');
    }

    public function referrals()
    {
        return view('public.referrals');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function riskDisclosure()
    {
        return view('public.risk-disclosure');
    }

    public function amlKycPolicy()
    {
        return view('public.aml-kyc-policy');
    }

    public function cookiePolicy()
    {
        return view('public.cookie-policy');
    }
}
