<?php

use App\Models\JokeReaction;
use App\Models\Joke;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('joke reaction has correct fillable attributes', function () {
    $reaction = new JokeReaction();

    expect($reaction->getFillable())->toEqualCanonicalizing([
        'user_id',
        'joke_id',
        'is_positive',
    ]);
});

test('joke reaction casts is_positive to boolean', function () {
    $reaction = new JokeReaction(['is_positive' => 1]);

    expect($reaction->is_positive)->toBeBool()
        ->and($reaction->is_positive)->toBeTrue();
});

test('joke reaction belongs to a user', function () {
    $user = User::factory()->create();
    $reaction = JokeReaction::factory()->create(['user_id' => $user->id]);

    expect($reaction->user)->toBeInstanceOf(User::class)
        ->and($reaction->user->id)->toBe($user->id);
});

test('joke reaction belongs to a joke', function () {
    $joke = Joke::factory()->create();
    $reaction = JokeReaction::factory()->create(['joke_id' => $joke->id]);

    expect($reaction->joke)->toBeInstanceOf(Joke::class)
        ->and($reaction->joke->id)->toBe($joke->id);
});
