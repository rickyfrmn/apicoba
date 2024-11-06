<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::resource('posts', App\Http\Controllers\Api\PostController::class);
// Route::post('/posts', App\Http\Controllers\Api\PostController::class, 'store');
// Di atas route POST
// Route::post('/posts', App\Http\Controllers\Api\PostController::class, 'store')->withoutMiddleware(['csrf']);