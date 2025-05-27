<?php

namespace App\Http\Controllers;

use App\Models\Knife;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class KnifeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Knife::query();

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('rarity') && $request->rarity) {
            $query->where('rarity', $request->rarity);
        }
        if ($request->has('wear_level') && $request->wear_level) {
            $query->where('wear_level', $request->wear_level);
        }
        if ($request->has('color') && $request->color) {
            $query->where('color', $request->color);
        }
        if ($request->has('price_min') && $request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Knife::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'rarity' => 'required|string',
            'float_value' => 'required|numeric|between:0,1',
            'wear_level' => 'required|in:Factory New,Minimal Wear,Field-Tested,Well-Worn,Battle-Scarred',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'color' => 'required|string',
            'image_url' => 'required|url'
        ]);

        $knife = Knife::create($validated);
        return response()->json($knife, 201);
    }
}
