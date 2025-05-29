<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KnifeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/remove', [CartController::class, 'remove']);
    Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
});

Route::get('/knives', [KnifeController::class, 'index']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
