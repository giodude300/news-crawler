<?php

use App\Http\Controllers\Api\StoryProcessingController;
use Illuminate\Support\Facades\Route;

Route::prefix('stories')->group(function () {
    Route::post('/process', [StoryProcessingController::class, 'process']);
});

Route::prefix('executions')->group(function () {
    Route::get('/', [StoryProcessingController::class, 'index']);
    Route::get('/{id}', [StoryProcessingController::class, 'show']);
});
