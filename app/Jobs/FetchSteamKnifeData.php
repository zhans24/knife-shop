<?php

namespace App\Jobs;

use App\Models\Knife;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FetchSteamKnifeData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $knife;
    protected $requestData;

    public $tries = 3;

    public function __construct(Knife $knife, array $requestData = [])
    {
        $this->knife = $knife;
        $this->requestData = $requestData;
    }

    public function handle()
    {
        $cacheKey = 'steam_knife_data_' . md5($this->knife->market_hash_name);
        $cacheTTL = 60 * 60; // Cache for 1 hour

        // Check cache first
        $steamData = Cache::remember($cacheKey, $cacheTTL, function () {
            $response = Http::retry(3, 100, function ($exception, $request) {
                if ($exception->response && $exception->response->status() === 429) {
                    sleep(60);
                    return true;
                }
                return false;
            })->get('https://steamcommunity.com/market/search/render/', [
                'query' => $this->knife->market_hash_name,
                'appid' => 730,
                'norender' => 1,
                'count' => 1,
            ]);

            return $response->successful() && !empty($response->json()['results'])
                ? $response->json()['results'][0]
                : null;
        });

        $wearLevels = [
            'Прямо с завода' => 'Factory New',
            'Немного поношенное' => 'Minimal Wear',
            'После полевых испытаний' => 'Field-Tested',
            'Поношенное' => 'Well-Worn',
            'Закалённое в боях' => 'Battle-Scarred',
        ];

        if ($steamData) {
            $name = $steamData['name'];
            $wearLevel = null;
            foreach ($wearLevels as $ru => $en) {
                if (str_contains($name, $ru)) {
                    $wearLevel = $en;
                    break;
                }
            }

            $price = $steamData['sell_price'] / 100;

            $this->knife->update([
                'steam_name' => $name,
                'wear_level' => $wearLevel,
                'price' => $price,
                'image_url' => 'https://steamcommunity-a.akamaihd.net/economy/image/' . $steamData['asset_description']['icon_url'],
                'description' => $steamData['asset_description']['type'],
                'steam_data_updated_at' => now(),
            ]);
        } else {
            $this->knife->update([
                'steam_name' => null,
                'wear_level' => null,
                'price' => null,
                'image_url' => null,
                'description' => 'Данные недоступны',
                'steam_data_updated_at' => now(),
            ]);
        }
    }
}
