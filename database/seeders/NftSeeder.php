<?php

namespace Database\Seeders;

use App\Models\NftCollection;
use App\Models\NftItem;
use Illuminate\Database\Seeder;

class NftSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            ['name' => 'Neon Foxes', 'slug' => 'neon-foxes', 'floor_price' => 0.85, 'volume' => 1240, 'owners_count' => 3200, 'items_count' => 8888],
            ['name' => 'Astral Punks', 'slug' => 'astral-punks', 'floor_price' => 2.35, 'volume' => 5600, 'owners_count' => 4100, 'items_count' => 10000],
            ['name' => 'Cyber Cats Club', 'slug' => 'cyber-cats-club', 'floor_price' => 0.42, 'volume' => 680, 'owners_count' => 2200, 'items_count' => 6000],
        ];

        foreach ($collections as $collection) {
            $model = NftCollection::updateOrCreate(['slug' => $collection['slug']], $collection + [
                'description' => 'Simulated NFT collection for demonstration purposes only.',
            ]);

            for ($i = 1; $i <= 6; $i++) {
                NftItem::updateOrCreate(
                    ['nft_collection_id' => $model->id, 'token_id' => (string) $i],
                    [
                        'name' => $collection['name'].' #'.$i,
                        'price' => round($collection['floor_price'] * (1 + $i * 0.1), 3),
                        'is_listed' => true,
                    ]
                );
            }
        }
    }
}
