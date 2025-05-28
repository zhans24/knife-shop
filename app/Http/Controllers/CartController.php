<?php

namespace App\Http\Controllers;

use App\Models\Knife;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function add(Request $request): JsonResponse
    {
        $request->validate(['knife_id' => 'required|exists:knives,id']);
        $cart = Session::get('cart', []);
        $knifeId = (string)$request->knife_id;

        if (!in_array($knifeId, $cart)) {
            $cart[] = $knifeId;
            Session::put('cart', $cart);
        }

        return response()->json(['cart' => $this->getCartItems($cart)]);
    }

    public function view(): JsonResponse
    {
        $cart = Session::get('cart', []);
        return response()->json($this->getCartItems($cart));
    }

    public function clear(): JsonResponse
    {
        Session::forget('cart');
        return response()->json(['message' => 'Корзина очищена']);
    }

    public function checkout(): JsonResponse
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Корзина пуста'], 400);
        }

        Session::forget('cart');
        return response()->json(['message' => 'Заказ оформлен']);
    }

    public function remove(Request $request): JsonResponse
    {
        $request->validate(['knife_id' => 'required|exists:knives,id']);
        $cart = Session::get('cart', []);
        $knifeId = (string)$request->knife_id;

        $cart = array_values(array_filter($cart, fn($id) => $id !== $knifeId));
        Session::put('cart', $cart);

        return response()->json(['cart' => $this->getCartItems($cart)]);
    }

    private function getCartItems(array $cart): array
    {
        if (empty($cart)) return [];

        $cacheKey = 'cart_knives_' . md5(json_encode($cart));
        return Cache::remember($cacheKey, 3600, function () use ($cart) {
            $knives = Knife::whereIn('id', $cart)->get();
            $enriched = [];

            foreach ($knives as $knife) {
                try {
                    $response = Http::retry(3, 100)->get('https://steamcommunity.com/market/search/render', [
                        'query' => $knife->market_hash_name,
                        'appid' => 730,
                        'norender' => true,
                        'count' => 1,
                    ]);

                    $data = $response->successful() && !empty($response->json()['results'])
                        ? $response->json()['results'][0]
                        : [];

                    $name = $data['name'] ?? $knife->market_hash_name;
                    $wearLevels = ['Factory New', 'Minimal Wear', 'Field-Tested', 'Well-Worn', 'Battle-Scarred'];
                    $wearLevel = null;
                    foreach ($wearLevels as $level) {
                        if (str_contains($name, $level)) {
                            $wearLevel = $level;
                            break;
                        }
                    }

                    $enriched[] = [
                        'id' => (string)$knife->id,
                        'name' => $name,
                        'price' => ($data['sell_price'] ?? 0) / 100,
                        'type' => $knife->type,
                        'wear_level' => $wearLevel,
                        'image_url' => $data ? 'https://steamcommunity-a.akamaihd.net/economy/image/' . ($data['asset_description']['icon_url'] ?? '') : '',
                    ];
                } catch (\Exception $ex) {
                    Log::warning('Failed to fetch Steam data for cart knife', [
                        'knife' => $knife->market_hash_name,
                        'error' => $ex->getMessage(),
                    ]);
                    $enriched[] = [
                        'id' => (string)$knife->id,
                        'name' => $knife->market_hash_name,
                        'price' => 0,
                        'type' => $knife->type,
                        'wear_level' => null,
                        'image_url' => '',
                    ];
                }
            }

            return $enriched;
        });
    }
}
