<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SteamKnifeController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $start = ($page - 1) * 20;
        $cacheKey = "steam_knives_page_{$page}";

        $data = Cache::remember($cacheKey, 300, function () use ($start) {
            try {
                $response = Http::get('https://steamcommunity.com/market/search/render/', [
                    'query' => 'knife',
                    'appid' => 730,
                    'norender' => 1,
                    'start' => $start,
                    'count' => 5,
                ]);

                if (!$response->successful()) {
                    return ['success' => false, 'error' => 'Failed to fetch Steam data'];
                }

                return $response->json();
            } catch (\Exception $e) {
                return ['success' => false, 'error' => 'Steam API request failed'];
            }
        });

        if (!$data['success']) {
            return response()->json(['error' => $data['error']], 500);
        }

        $knives = collect($data['results'])->filter(function ($item) {
            return str_contains($item['asset_description']['type'], 'Knife');
        })->map(function ($item) {
            $type = str_replace('★ ', '', $item['asset_description']['type']);
            return [
                'id' => $item['hash_name'],
                'name' => $item['name'],
                'type' => $type,
                'rarity' => 'Covert',
                'price' => $item['sell_price'] / 100,
                'image_url' => 'https://steamcommunity-a.akamaihd.net/economy/image/' . $item['asset_description']['icon_url'],
                'description' => $item['asset_description']['type'],
                'color' => $item['asset_description']['name_color'] === '8650AC' ? 'Purple' : 'Unknown',
                'float_value' => null,
                'wear_level' => null,
            ];
        })->values();

        $search = strtolower($request->input('search', ''));
        $filters = $request->only(['type', 'rarity', 'color', 'price_min', 'price_max']);

        $filteredKnives = $knives->filter(function ($knife) use ($filters, $search) {
            if ($filters['type'] && $knife['type'] != $filters['type']) return false;
            if ($filters['rarity'] && $knife['rarity'] != $filters['rarity']) return false;
            if ($filters['color'] && $knife['color'] != $filters['color']) return false;
            if ($filters['price_min'] && $knife['price'] < $filters['price_min']) return false;
            if ($filters['price_max'] && $knife['price'] > $filters['price_max']) return false;
            if ($search && !strpos(strtolower($knife['name']), $search)) return false;
            return true;
        });

        $total = $data['total_count'];
        $perPage = 20;
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'data' => $filteredKnives,
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
        ]);
    }
}
