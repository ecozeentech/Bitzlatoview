<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\NewsArticle;
use Illuminate\Database\Seeder;

class BlogNewsSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            ['title' => 'Getting Started with Bitzlatoview', 'category' => 'Guides', 'excerpt' => 'A walkthrough of the dashboard, wallets, and where to find every product.'],
            ['title' => 'Understanding Double-Entry Ledgers in Crypto Platforms', 'category' => 'Education', 'excerpt' => 'Why every balance change should be auditable — and how Bitzlatoview implements it.'],
            ['title' => '5 Risk Management Tips Before You Start Copy Trading', 'category' => 'Education', 'excerpt' => 'Copy trading can amplify both gains and losses — here is how to manage exposure.'],
            ['title' => 'What Is Paper Trading and Why Bitzlatoview Starts There', 'category' => 'Company', 'excerpt' => 'Simulation mode lets you learn every product risk-free while we finish licensing work.'],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['title' => $post['title']],
                $post + [
                    'slug' => \Illuminate\Support\Str::slug($post['title']),
                    'content' => "<p>{$post['excerpt']}</p><p>This is placeholder editorial content for the Bitzlatoview blog CMS demo.</p>",
                    'author' => 'Bitzlatoview Team',
                    'status' => 'published',
                    'published_at' => now()->subDays(random_int(1, 30)),
                ]
            );
        }

        $news = [
            ['title' => 'BTC Holds Above Key Support as Volume Climbs', 'sentiment' => 'bullish', 'related_assets' => ['BTC']],
            ['title' => 'ETH Network Activity Rebounds Following Upgrade Chatter', 'sentiment' => 'bullish', 'related_assets' => ['ETH']],
            ['title' => 'Altcoins Mixed as Traders Rotate Into Majors', 'sentiment' => 'neutral', 'related_assets' => ['SOL', 'XRP']],
            ['title' => 'Regulatory Watch: What Exchanges Are Tracking This Quarter', 'sentiment' => 'neutral', 'related_assets' => []],
        ];

        foreach ($news as $article) {
            NewsArticle::updateOrCreate(
                ['title' => $article['title']],
                $article + [
                    'slug' => \Illuminate\Support\Str::slug($article['title']),
                    'summary' => 'Simulated market news for demo purposes — not financial advice.',
                    'content' => '<p>Simulated market news content.</p>',
                    'source' => 'Bitzlatoview Newsroom',
                    'published_at' => now()->subHours(random_int(1, 72)),
                ]
            );
        }
    }
}
