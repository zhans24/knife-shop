<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token', [], null)->plainTextToken;


            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            Log::error('reg error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to register'], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Неверные данные'],
                ]);
            }
            $user->tokens()->delete();

            $token = $user->createToken('auth_token', [], null)->plainTextToken;

            $request->session()->regenerate();

            Log::info('User logged in', ['user_id' => $user->id]);

            return response()->json([
                'user' => $user,
                'token' => $token,
            ])->withCookie(cookie('XSRF-TOKEN', csrf_token(), 525600));
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to login'], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();
            $request->session()->invalidate();
            return response()->json(['message' => 'Выход выполнен']);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to logout'], 500);
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                throw new \Exception('Unauthenticated');
            }
            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('User fetch error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch user'], 401);
        }
    }
}
