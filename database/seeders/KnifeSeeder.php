<?php

namespace Database\Seeders;

use App\Models\Knife;
use Illuminate\Database\Seeder;

class KnifeSeeder extends Seeder
{
    public function run()
    {
        $knives = [
            [
                'market_hash_name' => '★ Karambit | Fade (Factory New)',
                'type' => 'Karambit',
            ],
            [
                'market_hash_name' => '★ Bayonet | Doppler (Minimal Wear)',
                'type' => 'Bayonet',
            ],
            [
                'market_hash_name' => '★ Butterfly Knife | Crimson Web (Field-Tested)',
                'type' => 'Butterfly',
            ],
            [
                'market_hash_name' => '★ Skeleton Knife | Slaughter (Factory New)',
                'type' => 'Skeleton Knife',
            ],
            [
                'market_hash_name' => '★ Bowie Knife | Tiger Tooth (Minimal Wear)',
                'type' => 'Bowie Knife',
            ],
            [
                'market_hash_name' => '★ Falchion Knife | Marble Fade (Factory New)',
                'type' => 'Falchion Knife',
            ],
            [
                'market_hash_name' => '★ Paracord Knife | Blue Steel (Well-Worn)',
                'type' => 'Paracord Knife',
            ],
            [
                'market_hash_name' => '★ Navaja Knife | Stained (Battle-Scarred)',
                'type' => 'Navaja Knife',
            ],
            [
                'market_hash_name' => '★ Huntsman Knife | Black Laminate (Field-Tested)',
                'type' => 'Huntsman Knife',
            ],
            [
                'market_hash_name' => '★ Gut Knife | Lore (Minimal Wear)',
                'type' => 'Gut Knife',
            ],
        ];

        foreach ($knives as $knife) {
            Knife::create($knife);
        }
    }
}
