<?php

use App\Http\Controllers\JokeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaticPages;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JokeReactionController;

Route::get('/', [StaticPages::class, 'welcome'])->name('home');
Route::get('/contact', [StaticPages::class, 'contact'])->name('contact');
Route::get('/privacy', [StaticPages::class, 'privacy'])->name('privacy');
Route::get('/about', [StaticPages::class, 'about'])->name('about');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Profile - all roles can access their own
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * USER ROUTES
     */

    // Admin and Staff: full access to all users (browse, show, edit, add, delete, trash ops)
    Route::middleware('role:Administrator|Staff')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index'); // Browse
        Route::get('users/create', [UserController::class, 'create'])->name('users.create'); // Add
        Route::post('users', [UserController::class, 'store'])->name('users.store');

        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show'); // Show
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // Edit
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy'); // Delete
        Route::get('users/{user}/delete', [UserController::class, 'delete'])->name('user.delete');

        // Trash (Admin only)
        Route::middleware('role:Administrator')->group(function () {
            Route::get('users/trash', [UserController::class, 'trash'])->name('users.trash');
            Route::patch('users/trash/{id}/recover', [UserController::class, 'recoverOne'])->name('users.recover-one');
            Route::delete('users/trash/empty', [UserController::class, 'emptyAll'])->name('users.empty-all');
            Route::delete('users/trash/{id}/delete', [UserController::class, 'emptyOne'])->name('users.empty-one');
            Route::patch('users/trash/recover-all', [UserController::class, 'recoverAll'])->name('users.recover-all');
        });
    });

    /**
     * JOKE ROUTES
     */
    // All: can browse, show
    Route::get('jokes', [JokeController::class, 'index'])->name('jokes.index');
    Route::get('jokes/{joke}', [JokeController::class, 'show'])->name('jokes.show');

    Route::post('jokes/{joke}/react', [JokeReactionController::class, 'store'])->name('jokes.react');
    Route::delete('jokes/{joke}/react', [JokeReactionController::class, 'destroy'])->name('jokes.unreact');


    // Staff/Admin: edit/delete jokes (any)
    Route::middleware('role:Administrator|Staff')->group(function () {
        Route::get('jokes/{joke}/edit', [JokeController::class, 'edit'])->name('jokes.edit');
        Route::put('jokes/{joke}', [JokeController::class, 'update'])->name('jokes.update');
        Route::delete('jokes/{joke}', [JokeController::class, 'destroy'])->name('jokes.destroy');
    });

    // Client: create and manage own jokes
    Route::middleware('role:Client')->group(function () {
        Route::get('jokes/create', [JokeController::class, 'create'])->name('jokes.create');
        Route::post('jokes', [JokeController::class, 'store'])->name('jokes.store');
    });

    // Trash routes (soft deletes)
    Route::middleware('role:Administrator|Staff|Client')->group(function () {
        Route::get('jokes-trash', [JokeController::class, 'trash'])->name('jokes.trash');
        Route::post('jokes-trash/{id}/restore', [JokeController::class, 'restore'])->name('jokes.restore');
        Route::delete('jokes-trash/{id}/force-delete', [JokeController::class, 'forceDelete'])->name('jokes.forceDelete');
    });

    // Admin only can restore/delete all
    Route::middleware('role:Administrator')->group(function () {
        Route::post('jokes-trash/restore-all', [JokeController::class, 'restoreAll'])->name('jokes.restoreAll');
        Route::delete('jokes-trash/force-delete-all', [JokeController::class, 'forceDeleteAll'])->name('jokes.forceDeleteAll');
    });

    /**
     * ADMIN-ONLY ROLES & PERMISSIONS PAGE
     */
    Route::middleware('role:Administrator')->group(function () {
        // Placeholder for roles/permissions management
        // Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });
});

require __DIR__ . '/auth.php';
