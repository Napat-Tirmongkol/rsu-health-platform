<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// OAuth Routes
Route::prefix('auth')->group(function () {
    Route::get('/line', [\App\Http\Controllers\Auth\OAuthController::class, 'redirectToLine'])->name('auth.line');
    Route::get('/line/callback', [\App\Http\Controllers\Auth\OAuthController::class, 'handleLineCallback']);
    
    Route::get('/google', [\App\Http\Controllers\Auth\OAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/google/callback', [\App\Http\Controllers\Auth\OAuthController::class, 'handleGoogleCallback']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
