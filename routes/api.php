<?php

use App\Http\Controllers\FollowController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TweetController;
use Illuminate\Support\Facades\Route;

// Grupo de rutas para Tweets
Route::prefix('tweet')->group(function () {
    Route::post('/', [TweetController::class, 'store']);
});

// Grupo de rutas para Follows
Route::prefix('follow')->group(function () {
    Route::post('/', [FollowController::class, 'store']);
});

// Grupo de rutas para Timelines
Route::prefix('timeline')->group(function () {
    Route::get('/{userId}', [TimelineController::class, 'getTimeline']);
});
