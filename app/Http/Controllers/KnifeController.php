<?php
namespace App\Http\Controllers;

use App\Models\Knife;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class KnifeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Knife::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('wear_level')) {
            $query->where('market_hash_name', 'LIKE', '%' . $this->mapWearLevelToRussian($request->wear_level) . '%');
        }
        if ($request->filled('search')) {
            $query->where('market_hash_name', 'LIKE', '%' . strtolower($request->search) . '%');
        }

        $knives = $query->paginate(9);
        $enrichedKnives = $this->enrichWithSteamData($knives->items(), $request);

        return response()->json([
            'data' => $enrichedKnives,
            'current_page' => $knives->currentPage(),
            'last_page' => $knives->lastPage(),
            'total' => $knives->total(),
        ]);
    }

    private function enrichWithSteamData($knives, Request $request)
    {
        $cacheKey = 'steam_knives_' . md5(json_encode($knives) . json_encode($request->all()));

        return Cache::remember($cacheKey, 3600, function () use ($knives, $request) {
            $enriched = [];
            $wearLevels = [
                'Прямо с завода' => 'Factory New',
                'Немного поношенное' => 'Minimal Wear',
                'После полевых испытаний' => 'Field-Tested',
                'Поношенное' => 'Well-Worn',
                'Закалённое в боях' => 'Battle-Scarred',
            ];

            // Limit the number of API calls to avoid rate limits
            $maxApiCalls = 5; // Adjust based on Steam's rate limit
            $currentCalls = 0;

            foreach ($knives as $knife) {
                if ($currentCalls >= $maxApiCalls) {
                    // Skip API call and use fallback data
                    $enriched[] = [
                        'id' => $knife->id,
                        'name' => $knife->market_hash_name,
                        'type' => $knife->type,
                        'rarity' => 'Covert',
                        'wear_level' => null,
                        'price' => 0,
                        'image_url' => '',
                        'description' => 'Данные недоступны (ограничение API)',
                    ];
                    continue;
                }

                $response = Http::retry(3, 100)->get('https://steamcommunity.com/market/search/render/', [
                    'query' => $knife->market_hash_name,
                    'appid' => 730,
                    'norender' => 1,
                    'count' => 1,
                ]);

                $currentCalls++;

                if ($response->successful() && !empty($response->json()['results'])) {
                    $steamData = $response->json()['results'][0];
                    $name = $steamData['name'];
                    $wearLevel = null;
                    foreach ($wearLevels as $ru => $en) {
                        if (str_contains($name, $ru)) {
                            $wearLevel = $en;
                            break;
                        }
                    }

                    $price = $steamData['sell_price'] / 100;
                    if ($request->filled('price_min') && $price < $request->price_min) {
                        continue;
                    }
                    if ($request->filled('price_max') && $price > $request->price_max) {
                        continue;
                    }

                    $enriched[] = [
                        'id' => $knife->id,
                        'name' => $name,
                        'type' => $knife->type,
                        'rarity' => 'Covert',
                        'wear_level' => $wearLevel,
                        'price' => $price,
                        'image_url' => 'https://steamcommunity-a.akamaihd.net/economy/image/' . $steamData['asset_description']['icon_url'],
                        'description' => $steamData['asset_description']['type'],
                    ];
                } else {
                    $enriched[] = [
                        'id' => $knife->id,
                        'name' => $knife->market_hash_name,
                        'type' => $knife->type,
                        'rarity' => 'Covert',
                        'wear_level' => null,
                        'price' => 0,
                        'image_url' => '',
                        'description' => 'Данные недоступны',
                    ];
                }
            }

            return $enriched;
        });
    }

    private function mapWearLevelToRussian($wearLevel)
    {
        $map = [
            'Factory New' => 'Прямо с завода',
            'Minimal Wear' => 'Немного поношенное',
            'Field-Tested' => 'После полевых испытаний',
            'Well-Worn' => 'Поношенное',
            'Battle-Scarred' => 'Закалённое в боях',
        ];
        return $map[$wearLevel] ?? '';
    }

    public function store(Request $request)
    {
        $this->authorize('create', Knife::class);
        $validated = $request->validate([
            'market_hash_name' => 'required|string|max:255|unique:knives',
            'type' => 'required|string',
        ]);

        $knife = Knife::create($validated);
        return response()->json($knife, 201);
    }
}
