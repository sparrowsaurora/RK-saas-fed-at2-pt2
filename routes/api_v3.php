<?php

use App\Http\Controllers\Api\v3\AuthController as AuthControllerV3;
use App\Http\Controllers\Api\v3\CategoryController as CategoryControllerV3;
use App\Http\Controllers\Api\v3\JokeController as JokeControllerV3;
use App\Http\Controllers\Api\v3\JokeReactionController as JokeReactionControllerV3;
use App\Http\Controllers\Api\v3\UserController as UserControllerV3;
use App\Http\Controllers\Api\v3\RoleController as RoleControllerV3;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

/**
 * API Version 3 Routes
 */

// index route
Route::get('welcome', function() {
	return ApiResponse::success([], "welcome");
});

/**
 * User Auth Controller Routes ------------------------------
 * - Register, Login (no authentication)
 * - Profile, Logout, User details (authentication required)
 */
Route::prefix('auth')
    ->group(function () {
        Route::post('register', [AuthControllerV3::class, 'register']);
        Route::post('login', [AuthControllerV3::class, 'login']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('profile', [AuthControllerV3::class, 'profile']);
            Route::post('logout', [AuthControllerV3::class, 'logout']);
            Route::put('profile', [AuthControllerV3::class, 'edit']);
            Route::delete('profile', [AuthControllerV3::class, 'delete']);
        });
    });

/**
 * admin Controller Routes ------------------------------
 * @requires auth
 * @requires role: in [ Super-User | Administrator | Staff ]
 * - dashboard
 * - Users/
 *      - browse, read, edit, delete
 *      - removeJokes
 *      - suspend, unsuspend
 *      - search
 *      - logoutUser
 *      - assignRole
 *      - removeVotes -> @requires role: in [ Super-User | Administrator ]
 * - Roles/ -> @requires role: in [ Super-User | Administrator ]
 *      - browse, read, edit, add, delete
 *      - logoutRole (all users of a role)
 */
Route::middleware(['auth:sanctum', 'role:Super-User|Administrator|Staff'])->group(function () {
    Route::prefix('admin')
        ->group(function () {
            Route::get('/', [UserControllerV3::class, 'adminDashboard']);
            Route::prefix('users')
                ->group(function () {
                    Route::get('/', [UserControllerV3::class, 'index'])->name('users.index');
                    // no store method needed
                    Route::get('{user}', [UserControllerV3::class, 'show'])->name('users.show');
                    Route::put('{user}', [UserControllerV3::class, 'update'])->name('users.update');
                    Route::delete('{user}', [UserControllerV3::class, 'destroy'])->name('users.destroy');

                    //Route::get('/', [AdminUserControllerV3::class, 'users'])->name('users');
                    Route::delete('{user}/jokes', [UserControllerV3::class, 'removeJokes']);
                    Route::post('suspend/{userId}', [UserControllerV3::class, 'suspendUser']);
                    Route::post('unsuspend/{userId}', [UserControllerV3::class, 'unsuspendUser']);
                    Route::post('search', [UserControllerV3::class, 'search']);
                    Route::get('logout/{id}', [UserControllerV3::class, 'logoutUser']);
                    Route::post('assign-role/{userId}', [UserControllerV3::class, 'assignRole']);

                    Route::delete('{id}/votes', [UserControllerV3::class, 'removeVotes'])
                        ->middleware('role:Super-User|Administrator');
                });
            Route::prefix('roles')
                ->group(function () {
                    Route::middleware(['auth:sanctum', 'role:Super-User|Administrator'])->group(function () {
                        Route::get('/', [RoleControllerV3::class, 'index'])->name('roles.index');
                        Route::post('/', [RoleControllerV3::class, 'store'])->name('roles.store');
                        Route::get('{role}', [RoleControllerV3::class, 'show'])->name('roles.show');
                        Route::put('{role}', [RoleControllerV3::class, 'update'])->name('roles.update');
                        Route::delete('{role}', [RoleControllerV3::class, 'destroy'])->name('roles.destroy');
                        Route::get('logout/{id}', [RoleControllerV3::class, 'logoutRole']);
                    });
                });
        });
});


/**
 * Category Controller Routes ------------------------------
 * @requires auth
 * @requires role: in [ Super-User | Administrator | Staff ]
 * - search
 * - browse, read
 * -> @requires role: in [ Super-User | Administrator ]
 *      - edit, add, delete
 *      - removeJokes
 *      - suspend, unsuspend
 * - Trash/
 *      - removeOne, removeAll
 *      - recoverOne, recoverAll
 */
Route::middleware(['auth:sanctum', 'role:Super-User|Administrator|Staff'])->group(function () {
    Route::prefix('categories')
        ->group(function () {
            Route::post('search', [CategoryControllerV3::class, 'search']);
            Route::get('/', [CategoryControllerV3::class, 'index'])->name('category.index');
            Route::get('{category}', [CategoryControllerV3::class, 'show'])->name('category.show');
            Route::middleware('role:Super-User|Administrator')
                ->group(function () {
                    Route::post('/', [CategoryControllerV3::class, 'store'])->name('category.store');
                    Route::put('{category}', [CategoryControllerV3::class, 'update'])->name('category.update');
                    Route::delete('{category}', [CategoryControllerV3::class, 'destroy'])->name('category.destroy');
                    Route::get('trash', [CategoryControllerV3::class, 'trash']);

                    Route::prefix('trash')
                        ->group(function () {
                            Route::delete('empty', [CategoryControllerV3::class, 'removeAll']);
                            Route::post('recover', [CategoryControllerV3::class, 'recoverAll']);
                            Route::delete('{id}/remove', [CategoryControllerV3::class, 'removeOne']);
                            Route::post('{id}/recover', [CategoryControllerV3::class, 'recoverOne']);
                        });
                });
        });
});

/**
 * Jokes Controller Routes ------------------------------
 * @requires auth
 * - react
 * - browseByCategory
 * - browse, read, edit, add, delete
 * - Trash/ -> @requires role: in [ Super-User | Administrator | Staff]
 *      - browse
 *      - removeOne, removeAll
 *      - recoverOne, recoverAll
 */
Route::get('jokes/random', [JokeControllerV3::class, 'random']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('jokes')
        ->group(function () {
            /* Joke Reaction Route */
            Route::post('{id}/react', [JokeReactionControllerV3::class, 'store']);

            Route::get('{categoryId}/all', [JokeControllerV3::class, 'jokesByCategory']);

            // Replacement of Route::resource('jokes', JokeControllerV3::class);
            Route::get('/', [JokeControllerV3::class, 'index'])->name('jokes.index');
            Route::get('{joke}', [JokeControllerV3::class, 'show'])->name('jokes.show');

            Route::post('/', [JokeControllerV3::class, 'store'])->name('jokes.store');

            Route::put('{joke}', [JokeControllerV3::class, 'update'])->name('jokes.update');
            Route::delete('{joke}', [JokeControllerV3::class, 'destroy'])->name('jokes.destroy');

            Route::middleware(['auth:sanctum', 'role:Super-User|Administrator|Staff'])
                ->group(function () {
                    Route::prefix('trash')
                        ->group(function () {
                            Route::get('/', [JokeControllerV3::class, 'trash'])->name('jokes.trash');
                            Route::post('recover', [JokeControllerV3::class, 'recoverAll']);
                            Route::delete('empty', [JokeControllerV3::class, 'removeAll']);
                            Route::post('{id}/recover', [JokeControllerV3::class, 'recoverOne']);
                            Route::delete("{id}/remove", [JokeControllerV3::class, 'removeOne']);
                        });
                });
        });
});


/**
 * Permission Testing Routes ------------------------------
 * - Test/
 *      - base
 *      - superUser -> @requires role: Super-User
 *      - admin -> @requires role: Administrator
 *      - staff -> @requires role: Staff
 *      - client -> @requires role: Client
 *      - unAuth -> @denies role: any
 */
/* Permission Testing Routes ------------------------------------------------- */

// For testing API base returns :200
route::get('test/base', function () {
    return ApiResponse::success([], "success");
});
// For testing superUser only route :200
route::get('test/super-user', function () {
    return ApiResponse::success([], "success");
})->middleware('role:Super-User');
// For testing admin only route :200
route::get('test/admin', function () {
    return ApiResponse::success([], "success");
})->middleware('role:Administrator');
// For testing staff only route :200
route::get('test/staff', function () {
    return ApiResponse::success([], "success");
})->middleware('role:Staff');
// For testing client only route :200
route::get('test/client', function () {
    return ApiResponse::success([], "success");
})->middleware('role:Client');
route::get('test/un-auth', function () {
    return ApiResponse::success([], "success");
})->middleware('deny.role');

// auth???
require __DIR__ . '/auth.php';
