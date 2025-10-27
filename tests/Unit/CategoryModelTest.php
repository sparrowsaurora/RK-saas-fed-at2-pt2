<?php

use App\Models\Category;
use App\Models\Joke;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('category has fillable attributes', function () {
    $category = new Category();

    expect($category->getFillable())->toEqualCanonicalizing([
        'title',
        'description',
    ]);
});

test('category can have many jokes', function () {
    $category = Category::factory()->create();
    $jokes = Joke::factory()->count(3)->create();

    $category->jokes()->attach($jokes->pluck('id'));

    expect($category->jokes)->toHaveCount(3)
        ->and($category->jokes->first())->toBeInstanceOf(Joke::class);
});

test('jokesByTitle returns jokes ordered by title ascending', function () {
    $category = Category::factory()->create();

    $a = Joke::factory()->create(['title' => 'A Joke']);
    $z = Joke::factory()->create(['title' => 'Z Joke']);

    $category->jokes()->attach([$a->id, $z->id]);

    $ordered = $category->jokesByTitle()->get();

    expect($ordered->first()->title)->toBe('A Joke')
        ->and($ordered->last()->title)->toBe('Z Joke');
});

test('jokesByTitleDesc returns jokes ordered by title descending', function () {
    $category = Category::factory()->create();

    $a = Joke::factory()->create(['title' => 'A Joke']);
    $z = Joke::factory()->create(['title' => 'Z Joke']);

    $category->jokes()->attach([$a->id, $z->id]);

    $ordered = $category->jokesByTitleDesc()->get();

    expect($ordered->first()->title)->toBe('Z Joke')
        ->and($ordered->last()->title)->toBe('A Joke');
});

test('jokesByDateAddedDesc returns jokes ordered by creation date descending', function () {
    $category = Category::factory()->create();

    $old = Joke::factory()->create(['created_at' => now()->subDay()]);
    $new = Joke::factory()->create(['created_at' => now()]);

    $category->jokes()->attach([$old->id, $new->id]);

    $ordered = $category->jokesByDateAddedDesc()->get();

    expect($ordered->first()->id)->toBe($new->id)
        ->and($ordered->last()->id)->toBe($old->id);
});

test('category supports soft deletes', function () {
    $category = Category::factory()->create();

    $category->delete();

    expect(Category::withTrashed()->find($category->id))->not->toBeNull()
        ->and(Category::find($category->id))->toBeNull();
});
