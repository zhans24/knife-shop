<?php

namespace Database\Seeders;

use App\Models\Knife;
use Illuminate\Database\Seeder;

class KnifeSeeder extends Seeder
{
    public function run()
    {
        Knife::create([
            'name' => 'Karambit Fade',
            'type' => 'Karambit',
            'rarity' => 'Covert',
            'float_value' => 0.01,
            'wear_level' => 'Factory New',
            'price' => 1200.00,
            'description' => 'A beautiful fade pattern karambit',
            'color' => 'Fade',
            'image_url' => 'https://via.placeholder.com/150',
        ]);
        Knife::create([
            'name' => 'Bayonet Blue Steel',
            'type' => 'Bayonet',
            'rarity' => 'Classified',
            'float_value' => 0.45,
            'wear_level' => 'Field-Tested',
            'price' => 350.00,
            'description' => 'Blue steel finish bayonet',
            'color' => 'Blue',
            'image_url' => 'https://via.placeholder.com/150',
        ]);
    }
}
