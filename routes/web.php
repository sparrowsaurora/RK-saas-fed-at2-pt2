<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaticPages;
use Illuminate\Support\Facades\Route;

Route::get('/', [StaticPages::class, 'welcome'])->name('home');
Route::get('/contact', [StaticPages::class, 'contact'])->name('contact');
Route::get('/privacy', [StaticPages::class, 'privacy'])->name('privacy');
Route::get('/about', [StaticPages::class, 'about'])->name('about');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
