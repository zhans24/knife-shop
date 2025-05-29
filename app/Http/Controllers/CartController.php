<?php

namespace App\Http\Controllers;

use App\Models\Knife;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CartController extends Controller
{
    protected function getCart()
    {
        try {
            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $cartItems = Cart::where('user_id', $user->id)
                ->with('knife')
                ->get()
                ->map(function ($item) {
                    $knife = $item->knife;
                    return [
                        'id' => $knife->id,
                        'steam_name' => $knife->steam_name ?? 'Unknown',
                        'image_url' => $knife->image_url ?? '/placeholder.jpg',
                        'price' => $knife->price ? (float)$knife->price : null,
                        'quantity' => $item->quantity,
                        'total' => $knife->price ? (float)$knife->price * $item->quantity : null,
                    ];
                });

            return response()->json($cartItems);
        } catch (\Exception $e) {
            Log::error('Cart fetch error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch cart'], 500);
        }
    }

    public function add(Request $request)
    {
        try {
            $request->validate(['knife_id' => ['required', 'exists:knives,id']]);

            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $knife = Knife::findOrFail($request->knife_id);

            $cartItem = Cart::where('user_id', $user->id)
                ->where('knife_id', $knife->id)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity');
            } else {
                Cart::create([
                    'user_id' => $user->id,
                    'knife_id' => $knife->id,
                    'quantity' => 1,
                ]);
            }

            return $this->getCart();
        }  catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add to cart'], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $request->validate(['knife_id' => ['required', 'exists:knives,id']]);

            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            Cart::where('user_id', $user->id)
                ->where('knife_id', $request->knife_id)
                ->delete();

            return $this->getCart();
        } catch (QueryException $e) {
            Log::error('Cart remove error: ' . $e->getMessage());
            return response()->json([]);
        } catch (\Exception $e) {
            Log::error('Cart remove error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to remove from cart'], 500);
        }
    }

    public function updateQuantity(Request $request)
    {
        try {
            $request->validate([
                'knife_id' => ['required', 'exists:knives,id'],
                'quantity' => ['required', 'integer', 'min:1'],
            ]);

            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $cartItem = Cart::where('user_id', $user->id)
                ->where('knife_id', $request->knife_id)
                ->first();

            if ($cartItem) {
                $cartItem->update(['quantity' => $request->quantity]);
            }

            return $this->getCart();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update quantity'], 500);
        }
    }

    public function clear()
    {
        try {
            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            Cart::where('user_id', $user->id)->delete();

            return response()->json([]);
        }  catch (\Exception $e) {
            Log::error('Cart clear error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to clear cart'], 500);
        }
    }

    public function index()
    {
        return $this->getCart();
    }
}
