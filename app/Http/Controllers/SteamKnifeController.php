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
        $search = $request->query('search', '');
        $start = ($page - 1) * 20;
        $cacheKey = "steam_knives_page_{$page}_search_" . md5($search);

        $data = Cache::remember($cacheKey, 300, function () use ($search, $start) {
            try {
                $response = Http::get('https://steamcommunity.com/market/search/render/', [
                    'query' => $search ? $search : 'knife',
                    'appid' => 730,
                    'norender' => 1,
                    'start' => $start,
                    'count' => 20,
                ]);

                if (!$response->successful()) {
                    Log::error('Steam API failed', ['status' => $response->status()]);
                    return ['success' => false, 'error' => 'Failed to fetch Steam data'];
                }

                return $response->json();
            } catch (\Exception $e) {
                Log::error('Steam API exception', ['error' => $e->getMessage()]);
                return ['success' => false, 'error' => 'Steam API request failed'];
            }
        });

        if (!$data['success']) {
            return response()->json(['error' => $data['error']], 500);
        }

        // Normalize Steam data to match Knife model
        $knives = collect($data['results'])->filter(function ($item) {
            // Exclude non-knife items (e.g., stickers, charms)
            return str_contains($item['asset_description']['type'], 'Knife');
        })->map(function ($item) {
            return [
                'id' => $item['hash_name'], // Unique identifier
                'name' => $item['name'],
                'type' => str_replace('★ ', '', $item['asset_description']['type']),
                'rarity' => 'Covert', // Steam knives are Covert
                'price' => $item['sell_price'] / 100, // Convert cents to dollars
                'image_url' => 'https://steamcommunity-a.akamaihd.net/economy/image/' . $item['asset_description']['icon_url'],
                'description' => $item['asset_description']['type'],
                'color' => $item['asset_description']['name_color'] === '8650AC' ? 'Purple' : 'Unknown',
                'float_value' => null, // Not provided by Steam API
                'wear_level' => null, // Not provided by Steam API
            ];
        })->values();

        // Apply client-side filters if provided
        $filters = $request->only(['type', 'rarity', 'wear_level', 'color', 'price_min', 'price_max']);
        $filteredKnives = $knives->filter(function ($knife) use ($filters) {
            if ($filters['type'] && $knife['type'] !== $filters['type']) return false;
            if ($filters['rarity'] && $knife['rarity'] !== $filters['rarity']) return false;
            if ($filters['color'] && $knife['color'] !== $filters['color']) return false;
            if ($filters['price_min'] && $knife['price'] < $filters['price_min']) return false;
            if ($filters['price_max'] && $knife['price'] > $filters['price_max']) return false;
            return true;
        })->values();

        // Simulate pagination
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
