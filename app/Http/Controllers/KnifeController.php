<?php

namespace App\Http\Controllers;

use App\Models\Knife;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KnifeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'knives_filtered_' . md5(json_encode($request->all()));
            $perPage = 9;
            $page = max(1, (int)$request->input('page', 1));

            $filteredKnives = Cache::remember($cacheKey, 3600, function () use ($request) {
                $knives = Knife::all();
                $enrichedKnives = $this->enrichWithSteamData($knives->all());

                return array_filter($enrichedKnives, function ($knife) use ($request) {
                    if ($request->filled('type') && $knife['type'] !== $request->type) return false;
                    if ($request->filled('wear_level') && $knife['wear_level'] !== $request->wear_level) return false;
                    if ($request->filled('price_min') && $knife['price'] < (float)$request->price_min) return false;
                    if ($request->filled('price_max') && $knife['price'] > (float)$request->price_max) return false;
                    if ($request->filled('search') && !str_contains(strtolower($knife['name']), strtolower($request->search))) return false;
                    return true;
                });
            });

            $filteredKnives = array_values($filteredKnives);
            $total = count($filteredKnives);
            $paginatedKnives = array_slice($filteredKnives, ($page - 1) * $perPage, $perPage);

            return response()->json([
                'data' => $paginatedKnives,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'total' => $total,
            ]);
        } catch (\Exception $ex) {
            Log::error('KnifeController::index failed', ['error' => $ex->getMessage(), 'trace' => $ex->getTraceAsString()]);
            return response()->json(['error' => 'Не удалось загрузить ножи'], 500);
        }
    }

    private function enrichWithSteamData(array $knives): array
    {
        $cacheKey = 'steam_knives_' . md5(json_encode($knives));

        return Cache::remember($cacheKey, 300, function () use ($cacheKey, $knives) {
            $enriched = [];
            $wearLevels = ['Factory New', 'Minimal Wear', 'Field-Tested', 'Well-Worn', 'Battle-Scarred'];

            foreach ($knives as $knife) {
                try {
                    $response = Http::retry(3, 100)->get('https://steamcommunity.com/market/search/render', [
                        'query' => $knife->market_hash_name,
                        'appid' => 730,
                        'norender' => true,
                        'count' => 1,
                    ]);

                    if ($response->successful() && !empty($response->json()['results'])) {
                        $data = $response->json()['results'][0];
                        $name = $data['name'] ?? $knife->market_hash_name;
                        $wearLevel = null;
                        foreach ($wearLevels as $level) {
                            if (str_contains($name, $level)) {
                                $wearLevel = $level;
                                break;
                            }
                        }

                        $enriched[] = [
                            'id' => $knife->id,
                            'name' => $name,
                            'type' => $knife->type,
                            'rarity' => 'Covert',
                            'wear_level' => $wearLevel,
                            'price' => ($data['sell_price'] ?? 0) / 100,
                            'image_url' => isset($data['asset_description']['icon_url'])
                                ? 'https://steamcommunity-a.akamaihd.net/economy/image/' . $data['asset_description']['icon_url']
                                : '',
                            'description' => $data['asset_description']['type'] ?? 'Данные недоступны',
                        ];
                    } else {
                        throw new \Exception('Empty or invalid Steam API response');
                    }
                } catch (\Exception $ex) {
                    Log::warning('Failed to fetch Steam data for knife', [
                        'knife' => $knife->market_hash_name,
                        'error' => $ex->getMessage(),
                        'response' => isset($response) ? $response->body() : 'No response',
                    ]);

                    Cache::forget($cacheKey);

                    $enriched[] = [
                        'id' => $knife->id,
                        'name' => $knife->market_hash_name,
                        'type' => $knife->type,
                        'rarity' => 'Covert',
                        'wear_level' => null,
                        'price' => 0,
                        'image_url' => 'https://via.placeholder.com/150?text=No+Image',
                        'description' => 'Данные из Steam API недоступны',
                    ];
                }
            }

            return $enriched;
        });
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Knife::class);

        $validated = $request->validate([
            'market_hash_name' => 'required|string|max:255|unique:knives',
            'type' => 'required|string',
        ]);

        $knife = Knife::create($validated);
        Cache::flush();
        return response()->json($knife, 201);
    }
}
