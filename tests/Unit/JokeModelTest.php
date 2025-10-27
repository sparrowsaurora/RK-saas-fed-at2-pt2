<?php

use App\Models\Joke;
use App\Models\User;
use App\Models\Category;
use App\Models\JokeReaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('joke has fillable attributes', function () {
    $joke = new Joke();

    expect($joke->getFillable())->toEqualCanonicalizing([
        'title',
        'content',
        'user_id',
        'published_at',
    ]);
});

test('joke belongs to a user', function () {
    $user = User::factory()->create();
    $joke = Joke::factory()->create(['user_id' => $user->id]);

    expect($joke->user)->toBeInstanceOf(User::class)
        ->and($joke->user->id)->toBe($user->id);
});

test('joke can belong to many categories', function () {
    $joke = Joke::factory()->create();
    $categories = Category::factory()->count(2)->create();

    $joke->categories()->attach($categories->pluck('id'));

    expect($joke->categories)->toHaveCount(2)
        ->and($joke->categories->first())->toBeInstanceOf(Category::class);
});

test('joke can have many reactions', function () {
    $joke = Joke::factory()->create();
    JokeReaction::factory()->count(3)->create(['joke_id' => $joke->id]);

    expect($joke->reactions)->toHaveCount(3);
});

test('positive and negative reaction counts are accurate', function () {
    $joke = Joke::factory()->create();

    JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => true]);
    JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => true]);
    JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => false]);

    expect($joke->positive_count)->toBe(2)
        ->and($joke->negative_count)->toBe(1);
});

test('positiveReactions scope returns only positive reactions', function () {
    $joke = Joke::factory()->create();
    $positive = JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => true]);
    JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => false]);

    $results = $joke->positiveReactions()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($positive->id);
});

test('negativeReactions scope returns only negative reactions', function () {
    $joke = Joke::factory()->create();
    $negative = JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => false]);
    JokeReaction::factory()->create(['joke_id' => $joke->id, 'is_positive' => true]);

    $results = $joke->negativeReactions()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($negative->id);
});

test('joke supports soft deletes', function () {
    $joke = Joke::factory()->create();
    $joke->delete();

    expect(Joke::withTrashed()->find($joke->id))->not->toBeNull()
        ->and(Joke::find($joke->id))->toBeNull();
});
