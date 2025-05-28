<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KnifeController;
use Illuminate\Support\Facades\Route;

Route::get('/knives', [KnifeController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/knives', [KnifeController::class, 'store']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'view']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
    Route::post('/cart/remove', [CartController::class, 'remove']);
});
