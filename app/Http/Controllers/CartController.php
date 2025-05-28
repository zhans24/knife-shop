<?php

namespace App\Http\Controllers;

use App\Models\Knife;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate(['knife_id' => 'required|exists:knives,id']);
        $cart = session()->get('cart', []);
        if (!in_array($request->knife_id, $cart)) {
            $cart[] = $request->knife_id;
            session()->put('cart', $cart);
        }
        return response()->json(['cart' => $cart]);
    }

    public function view()
    {
        $cart = session()->get('cart', []);
        $knives = Knife::whereIn('id', $cart)->get();
        return response()->json($knives);
    }

    public function clear()
    {
        session()->forget('cart');
        return response()->json(['message' => 'Cart cleared']);
    }
}
