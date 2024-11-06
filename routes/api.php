<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;

// Sanctum route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Posts routes - pilih salah satu cara:
// Cara 1: Menggunakan apiResource (recommended)
Route::apiResource('posts', PostController::class);