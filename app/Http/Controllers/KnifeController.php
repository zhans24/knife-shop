<?php

namespace App\Http\Controllers;

use App\Jobs\FetchSteamKnifeData;
use App\Models\Knife;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class KnifeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = Knife::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('wear_level')) {
            $query->where('wear_level', $this->mapWearLevelToRussian($request->wear_level));
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('market_hash_name', 'LIKE', '%' . strtolower($request->search) . '%')
                    ->orWhere('steam_name', 'LIKE', '%' . strtolower($request->search) . '%');
            });
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $knives = $query->paginate(9);

        $enrichedKnives = $knives->map(function ($knife) {
            return [
                'id' => $knife->id,
                'name' => $knife->steam_name ?? $knife->market_hash_name,
                'type' => $knife->type,
                'rarity' => 'Covert',
                'wear_level' => $this->mapWearLevelToRussian($knife->wear_level),
                'price' => $knife->price ?? 0,
                'image_url' => $knife->image_url ?? '',
                'description' => $knife->description ?? 'Данные недоступны',
            ];
        })->toArray();

        return response()->json([
            'data' => $enrichedKnives,
            'current_page' => $knives->currentPage(),
            'last_page' => $knives->lastPage(),
            'total' => $knives->total(),
        ]);
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
        return $map[$wearLevel] ?? $wearLevel ?? '';
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Knife::class);
        $validated = $request->validate([
            'market_hash_name' => 'required|string|max:255|unique:knives',
            'type' => 'required|string',
        ]);

        $knife = Knife::create($validated);

        FetchSteamKnifeData::dispatch($knife, []);

        return response()->json($knife, 201);
    }
}
