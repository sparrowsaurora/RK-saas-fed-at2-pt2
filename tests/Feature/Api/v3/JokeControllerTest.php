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

});

if (!defined('API_VER')) {
    define('API_VER', 'v3');
}

uses(RefreshDatabase::class);

test('retrieve all Jokes', function () {
    // Arrange
    $jokes = Joke::factory(5)->create();

    $data = [
        'message' => "Jokes retrieved",
        'success' => true,
        'data' => $jokes->toArray(),
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/jokes');

    // Assert
    $response
        ->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJson($data);
});

test('retrieve one Joke', function () {
    // Arrange
    $joke = Joke::factory()->create();

    $data = [
        'message' => "Joke retrieved",
        'success' => true,
        'data' => $joke->toArray()
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/jokes/' . $joke->id);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson($data);
});


test('return error on missing Joke', function () {
    $data = [
        'message' => "Joke not found",
        'success' => false,
        'data' => [],
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/jokes/9999');

    // Assert
    $response
        ->assertStatus(404)
        ->assertJson($data)
        ->assertJsonCount(0, 'data');
});


test('create a new Joke', function () {
    $category = Category::factory()->create();

    // Arrange
    $data = [
        'title' => 'Fake Joke',
        'content' => 'Fake Joke Description',
        'category_id' => $category->id,
    ];

    $dataResponse = [
        'message' => "Joke created successfully",
        'success' => true,
        'data' => [
            'title' => $data['title'],
            'content' => $data['content'],
            'categories' => [
                [
                    'id' => $category->id,
                    'title' => $category->title,
                    'description' => $category->description,
                ]
            ]
        ]
    ];

    // Act
    $response = $this->postJson('/api/' . API_VER . '/jokes', $data);

    // Assert
    $response
        ->assertStatus(201)
        ->assertJsonPath('data.categories.0.id', $category->id)
        ->assertJson([
            'message' => 'Joke created successfully',
            'success' => true,
            'data' => [
                'title' => $data['title'],
                'content' => $data['content'],
            ],
        ]);
});


test('create Joke with title and content errors', function () {
    $data = [
        'title' => '',
        'content' => '',
    ];

    $response = $this->postJson('/api/' . API_VER . '/jokes', $data);

    // 422 Unprocessable Entity
    // The HTTP 422 Unprocessable Entity status code means that while the server was able to interpret
    // the request sent, it is still not able to process it. The major issue here is when a server is
    // capable of interpreting a request, understanding its message, format, and structure, but still
    // cannot process due to some logical error.
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
            'content'
        ]);
});


test('create Joke title too short error', function () {
    $data = [
        'title' => '',
    ];

    $response = $this->postJson('/api/' . API_VER . '/jokes', $data);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
        ]);
});


test('create Joke description too short error', function () {
    $data = [
        'title' => 'This is a test category',
        'content' => 'short' // The content is too short
    ];

    $response = $this->postJson('/api/' . API_VER . '/jokes', $data);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'content',
        ]);
});

test('update joke successfully', function () {
    $joke = Joke::factory()->create(['user_id' => $this->user->id]);
    $data = ['title' => 'Updated Title'];

    $response = $this->putJson("/api/" . API_VER . "/jokes/{$joke->id}", $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Title');
});

test('update joke not found', function () {
    $response = $this->putJson("/api/" . API_VER . "/jokes/9999", ['title' => 'xyz:']);
    $response->assertStatus(404)
        ->assertJson(['success' => false, 'message' => 'Joke not found']);
});

test('update joke not owned by user', function () {
    $otherUser = User::factory()->create();
    $joke = Joke::factory()->create(['user_id' => $otherUser->id]);
    $response = $this->putJson("/api/" . API_VER . "/jokes/{$joke->id}", ['title' => 'xyz:']);
    $response->assertStatus(404)
        ->assertJson(['success' => false, 'message' => 'You does not own the joke']);
});

test('delete joke successfully', function () {
    $joke = Joke::factory()->create(['user_id' => $this->user->id]);
    $response = $this->deleteJson("/api/" . API_VER . "/jokes/{$joke->id}");
    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => "Joke <{$joke->id}> moved to trash"]);
});

test('delete joke not owned by user', function () {
    $otherUser = User::factory()->create();
    $joke = Joke::factory()->create(['user_id' => $otherUser->id]);
    $response = $this->deleteJson("/api/" . API_VER . "/jokes/{$joke->id}");
    $response->assertStatus(403)
        ->assertJson(['success' => false, 'message' => 'You do not have permission to delete this joke']);
});

test('delete joke not found', function () {
    $response = $this->deleteJson("/api/" . API_VER . "/jokes/9999");
    $response->assertStatus(404)
        ->assertJson(['success' => false, 'message' => 'Joke not found']);
});

test('random joke retrieved', function () {
    $joke = Joke::factory()->create();
    $response = $this->getJson("/api/" . API_VER . "/jokes/random");
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('jokes by category retrieved', function () {
    $category = Category::factory()->create();
    $joke = Joke::factory()->create();
    $joke->categories()->attach($category->id);

    $response = $this->getJson("/api/" . API_VER . "/jokes/{$category->id}/all");
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('jokes by category not found', function () {
    $response = $this->getJson("/api/" . API_VER . "/jokes/9999/all");
    $response->assertStatus(200)
        ->assertJson(['success' => false, 'message' => 'Category not found']);
});

