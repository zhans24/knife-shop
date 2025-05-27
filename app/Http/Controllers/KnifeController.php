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

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('rarity')) {
            $query->where('rarity', $request->rarity);
        }
        if ($request->filled('wear_level')) {
            $query->where('wear_level', $request->wear_level);
        }
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        if ($request->filled('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->search) . '%']);
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
