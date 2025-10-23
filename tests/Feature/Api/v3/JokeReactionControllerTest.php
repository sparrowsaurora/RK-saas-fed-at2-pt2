<?php

use App\Models\Category;
use App\Models\Joke;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Super-User');
    $this->actingAs($this->user, 'sanctum');
    $this->joke = Joke::factory()->create();

});

if (!defined('API_VER')) {
    define('API_VER', 'v3');
}

uses(RefreshDatabase::class);

test('Add positive vote', function () {
    $initialPositive = $this->joke->getPositiveCountAttribute();
    $payload = ["is_positive" => true];

    $data = [
            'success' => true,
            'message' => 'Reaction added',
        ];

    // Act
    $response = $this->postJson('/api/' . API_VER . '/jokes/' . $this->joke->id . '/react', $payload);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson($data)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'content',
                'user_id',
                'positive_count',
                'negative_count',
                'created_at',
                'updated_at',
            ],
        ]);


    // Assert the count increased
    $responseData = $response->json('data');
    expect($responseData['positive_count'])->toBe($initialPositive + 1);
});

test('Add negative vote', function () {
    $initialNegative = $this->joke->getNegativeCountAttribute();
    $payload = ["is_positive" => false];

    $data = [
        'success' => true,
        'message' => 'Reaction added',
    ];

    // Act
    $response = $this->postJson('/api/' . API_VER . '/jokes/' . $this->joke->id . '/react', $payload);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson($data);

    // Assert the count increased
    $responseData = $response->json('data');
    expect($responseData['negative_count'])->toBe($initialNegative + 1);
});

test('Remove positive vote when same reaction sent twice', function () {
    // Arrange
    $payload = ["is_positive" => true];

    // First vote — adds reaction
    $this->postJson('/api/' . API_VER . '/jokes/' . $this->joke->id . '/react', $payload)
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Reaction added',
        ]);

    // Get the current reaction count from DB
    $initialPositive = $this->joke->fresh()->positive_count;

    // Second vote — same reaction, should remove it (toggle off)
    $response = $this->postJson('/api/' . API_VER . '/jokes/' . $this->joke->id . '/react', $payload);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Reaction removed',
        ]);

    // Verify the joke’s positive count decreased by 1
    $updatedPositive = $this->joke->fresh()->positive_count;
    expect($updatedPositive)->toBe($initialPositive - 1);
});

