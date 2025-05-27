<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KnifeController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/knives', [KnifeController::class, 'index']);
Route::get('/steam-knives', [App\Http\Controllers\SteamKnifeController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/knives', [KnifeController::class, 'store']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'view']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
    Route::get('/user', [AuthController::class, 'user']);
});
