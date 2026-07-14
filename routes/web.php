<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [PostController::class, 'dashboard'])->name('dashboard');
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/ai/content', [PostController::class, 'generateContent'])->name('posts.ai.content');
    Route::post('/posts/ai/gemini-content', [PostController::class, 'generateGeminiContent'])->name('posts.ai.gemini-content');
    Route::post('/posts/ai/image', [PostController::class, 'generateImage'])->name('posts.ai.image');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::post('/posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
    Route::post('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::get('/connections/facebook/redirect', [ConnectionController::class, 'facebookRedirect'])->name('connections.facebook.redirect');
    Route::get('/connections/facebook/callback', [ConnectionController::class, 'facebookCallback'])->name('connections.facebook.callback');
    Route::get('/connections/facebook/pages', [ConnectionController::class, 'facebookPages'])->name('connections.facebook.pages');
    Route::post('/connections/facebook/pages', [ConnectionController::class, 'saveFacebookPages'])->name('connections.facebook.pages.save');
    Route::post('/connections', [ConnectionController::class, 'store'])->name('connections.store');
    Route::put('/connections/brand-profile', [ConnectionController::class, 'updateBrandProfile'])->name('connections.brand-profile');
    Route::delete('/connections/{connection}', [ConnectionController::class, 'destroy'])->name('connections.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
