<?php

use App\Http\Controllers\JokeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaticPages;
use App\Http\Controllers\UserController;
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

    // Trash routes - users
    Route::get('users/trash', [UserController::class, 'trash'])->name('users.trash');
    Route::patch('users/trash/{id}/recover', [UserController::class, 'recoverOne'])->name('users.recover-one');
    Route::delete('users/trash/empty', [UserController::class, 'emptyAll'])->name('users.empty-all');
    Route::delete('users/trash/{id}/delete', [UserController::class, 'emptyOne'])->name('users.empty-one');
    Route::patch('users/trash/recover-all', [UserController::class, 'recoverAll'])->name('users.recover-all');

    Route::resource('jokes', JokeController::class);

    // Trash routes - jokes
    Route::get('jokes-trash', [JokeController::class, 'trash'])->name('jokes.trash');
    Route::post('jokes-trash/{id}/restore', [JokeController::class, 'restore'])->name('jokes.restore');
    Route::post('jokes-trash/restore-all', [JokeController::class, 'restoreAll'])->name('jokes.restoreAll');
    Route::delete('jokes-trash/{id}/force-delete', [JokeController::class, 'forceDelete'])->name('jokes.forceDelete');
    Route::delete('jokes-trash/force-delete-all', [JokeController::class, 'forceDeleteAll'])->name('jokes.forceDeleteAll');

    Route::resource('users', UserController::class);
    Route::get('users/{user}/delete', [UserController::class, 'delete'])->name('user.delete');
});

require __DIR__ . '/auth.php';
