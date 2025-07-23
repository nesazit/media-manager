<?php

use Illuminate\Support\Facades\Route;
use Nesazit\MediaManager\Http\Controllers\MediaController;

Route::middleware(['web'])->prefix('media-manager')->name('media-manager.')->group(function () {

    // File serving routes
    Route::get('/file/{file}/{name}', [MediaController::class, 'serveFile'])
        ->name('file.serve')
        ->where('file', '[0-9]+');

    Route::get('/thumbnail/{file}/{size}', [MediaController::class, 'serveThumbnail'])
        ->name('thumbnail.serve')
        ->where('file', '[0-9]+')
        ->where('size', 'small|medium|large');

    // API routes for file operations
    Route::middleware(['auth'])->group(function () {
        Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
        Route::post('/create-directory', [MediaController::class, 'createDirectory'])->name('create-directory');
        Route::patch('/rename/{type}/{id}', [MediaController::class, 'rename'])->name('rename');
        Route::delete('/delete/{type}/{id}', [MediaController::class, 'delete'])->name('delete');
        Route::patch('/move/{type}/{id}', [MediaController::class, 'move'])->name('move');
        Route::get('/browse', [MediaController::class, 'browse'])->name('browse');
        Route::get('/search', [MediaController::class, 'search'])->name('search');
    });

    // Admin routes
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
    });
});
