<?php

use App\Models\User;
use App\Models\Joke;
use App\Models\JokeReaction;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user can have many jokes', function () {
    $user = User::factory()->create();
    Joke::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->jokes)->toHaveCount(3)
        ->and($user->jokes->first())->toBeInstanceOf(Joke::class);
});

test('user can have many joke reactions', function () {
    $user = User::factory()->create();
    JokeReaction::factory()->count(2)->create(['user_id' => $user->id]);

    expect($user->jokeReactions)->toHaveCount(2)
        ->and($user->jokeReactions->first())->toBeInstanceOf(JokeReaction::class);
});

test('user can be suspended and unsuspended', function () {
    $user = User::factory()->create(['status' => null]);

    $user->suspendUser();
    expect($user->fresh()->status)->toBe('suspended');

    $user->unsuspendUser();
    expect($user->fresh()->status)->toBeNull();
});

test('user can have roles assigned and accessed', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);

    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->role()->first()->name)->toBe('admin');
});

test('user has the correct casts', function () {
    $user = new User();

    expect($user->getCasts())
        ->toHaveKey('email_verified_at', 'datetime')
        ->toHaveKey('password', 'hashed');
});

test('user has the correct fillable attributes', function () {
    $user = new User();

    expect($user->getFillable())->toEqualCanonicalizing([
        'name',
        'email',
        'password',
        'status',
    ]);
});

