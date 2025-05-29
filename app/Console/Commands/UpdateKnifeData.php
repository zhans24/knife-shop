<?php

namespace App\Console\Commands;

use App\Models\Knife;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UpdateKnifeData extends Command
{
    protected $signature = 'knives:update';
    protected $description = 'Update knife data from Steam API';

    public function handle()
    {
        $knives = Knife::all();
        $wearLevels = [
            'Прямо с завода' => 'Factory New',
            'Немного поношенное' => 'Minimal Wear',
            'После полевых испытаний' => 'Field-Tested',
            'Поношенное' => 'Well-Worn',
            'Закалённое в боях' => 'Battle-Scarred',
        ];

        foreach ($knives as $knife) {
            $cacheKey = 'steam_knife_data_' . md5($knife->market_hash_name);
            $cacheTTL = 60 * 60;

            $steamData = Cache::remember($cacheKey, $cacheTTL, function () use ($knife) {
                $response = Http::retry(3, 100, function ($exception, $request) {
                    if ($exception->response && $exception->response->status() === 429) {
                        sleep(60);
                        return true;
                    }
                    return false;
                })->get('https://steamcommunity.com/market/search/render/', [
                    'query' => $knife->market_hash_name,
                    'appid' => 730,
                    'norender' => 1,
                    'count' => 1,
                ]);

                return $response->successful() && !empty($response->json()['results'])
                    ? $response->json()['results'][0]
                    : null;
            });

            if ($steamData) {
                $name = $steamData['name'];
                $wearLevel = null;
                foreach ($wearLevels as $ru => $en) {
                    if (str_contains($name, $en)) {
                        $wearLevel = $ru;
                        break;
                    }
                }

                $price = $steamData['sell_price'] / 100;

                $knife->update([
                    'steam_name' => $name,
                    'wear_level' => $wearLevel,
                    'price' => $price,
                    'image_url' => 'https://steamcommunity-a.akamaihd.net/economy/image/' . $steamData['asset_description']['icon_url'],
                    'description' => $steamData['asset_description']['type'],
                    'steam_data_updated_at' => now(),
                ]);

                $this->info("Updated data for knife: {$knife->market_hash_name}");
            } else {
                $knife->update([
                    'steam_name' => null,
                    'wear_level' => null,
                    'price' => null,
                    'image_url' => null,
                    'description' => 'Данные недоступны',
                    'steam_data_updated_at' => now(),
                ]);

                $this->warn("Failed to fetch data for knife: {$knife->market_hash_name}");
            }

            sleep(5);
        }

        $this->info('All knife data updated successfully.');
    }
}
