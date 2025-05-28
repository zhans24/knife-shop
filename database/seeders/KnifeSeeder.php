<?php
namespace Database\Seeders;

use App\Models\Knife;
use Illuminate\Database\Seeder;

class KnifeSeeder extends Seeder
{
    public function run()
    {
        $knives = [
            ['market_hash_name' => '★ Karambit | Doppler (Factory New)', 'type' => 'Karambit'],
            ['market_hash_name' => '★ Bayonet | Fade (Factory New)', 'type' => 'Bayonet'],
            ['market_hash_name' => '★ Skeleton Knife', 'type' => 'Skeleton Knife'],
            ['market_hash_name' => '★ Bowie Knife', 'type' => 'Bowie Knife'],
            ['market_hash_name' => '★ Falchion Knife', 'type' => 'Falchion Knife'],
            ['market_hash_name' => '★ Paracord Knife | Scorched (Factory New)', 'type' => 'Paracord Knife'],
            ['market_hash_name' => '★ Navaja Knife | Doppler (Factory New)', 'type' => 'Navaja Knife'],
            ['market_hash_name' => '★ Huntsman Knife | Doppler (Factory New)', 'type' => 'Huntsman Knife'],
            ['market_hash_name' => '★ Gut Knife | Doppler (Factory New)', 'type' => 'Gut Knife'],
        ];

        foreach ($knives as $knife) {
            Knife::updateOrCreate(['market_hash_name' => $knife['market_hash_name']], $knife);
        }
    }
}
