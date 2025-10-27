<?php

use \App\Models\Category;
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

test('retrieve all categories', function () {
    // Arrange
    $categories = Category::factory(5)->create();

    $data = [
        'message' => "Categories retrieved",
        'success' => true,
        'data' => $categories->toArray(),
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/categories');

    // Assert
    $response
        ->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJson($data);
});

test('retrieve one category', function () {
    // Arrange
    $category = Category::factory()->create();

    $data = [
        'message' => "Category retrieved",
        'success' => true,
        'data' => [
            'id' => $category->id,
            'title' => $category->title,
            'description' => $category->description,
            'created_at' => $category->created_at->toJSON(),
            'updated_at' => $category->updated_at->toJSON(),
            'deleted_at' => null,
            'jokes' => [],
        ],
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/categories/' . $category->id);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson($data);
//        ->assertJsonCount(1, 'data');
});


test('return error on missing category', function () {
    // Arrange
    $categories = Category::factory(1)->create();

    $data = [
        'message' => "Category not found",
        'success' => false,
        'data' => [],
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/categories/9999');

    // Assert
    $response
        ->assertStatus(404)
        ->assertJson($data)
        ->assertJsonCount(0, 'data');
});


test('create a new category', function () {
    // Arrange
    $data = [
        'title' => 'Fake Category',
        'description' => 'Fake Category Description',
    ];

    $dataResponse = [
        'message' => "Category created",
        'success' => true,
        'data' => $data
    ];

    // Act
    $response = $this->postJson('/api/' . API_VER . '/categories', $data);

    // Assert
    $response
        ->assertStatus(201)
        ->assertJson($dataResponse)
        ->assertJsonCount(5, 'data');
});


test('create category with title and description errors', function () {
    $data = [
        'title' => '',
        'description' => '1234',
    ];

    $response = $this->postJson('/api/' . API_VER . '/categories', $data);

    // 422 Unprocessable Entity
    // The HTTP 422 Unprocessable Entity status code means that while the server was able to interpret
    // the request sent, it is still not able to process it. The major issue here is when a server is
    // capable of interpreting a request, understanding its message, format, and structure, but still
    // cannot process due to some logical error.
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
            'description'
        ]);
});

test('create category title too short error', function () {
    $data = [
        'title' => '',
    ];

    $response = $this->postJson('/api/' . API_VER . '/categories', $data);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
        ]);
});

test('create category description too short error', function () {
    $data = [
        'title' => 'This is a test category',
        'description' => 'short' // The description is too short
    ];

    $response = $this->postJson('/api/' . API_VER . '/categories', $data);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'description',
        ]);
});

test('search returns results', function () {
    Category::factory()->create(['title' => 'Funny']);
    $response = $this->postJson("/api/" . API_VER . "/categories/search", ['search' => 'Funny']);
    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonStructure(['data' => ['Categories', 'resultsCount']]);
});

test('search returns no results', function () {
    $response = $this->postJson("/api/" . API_VER . "/categories/search", ['search' => 'NoMatch']);
    $response->assertStatus(200)
        ->assertJson(['success' => false, 'message' => 'No results found']);
});

test('search with empty string returns error', function () {
    $response = $this->postJson("/api/" . API_VER . "/categories/search", ['search' => '']);
    $response->assertStatus(200)
        ->assertJson(['success' => false, 'message' => 'search parameter is empty']);
});

test('update category successfully', function () {
    $category = Category::factory()->create(['title' => 'Old Title']);
    $response = $this->putJson('/api/' . API_VER . '/categories/' . $category->id, [
        'title' => 'Updated Title',
    ]);
    $response->assertStatus(200)
        ->assertJson(['message' => 'Category updated', 'success' => true]);
});

test('update category not found', function () {
    $response = $this->putJson('/api/' . API_VER . '/categories/9999', ['title' => 'Anything']);
    $response->assertStatus(404)
        ->assertJson(['success' => false, 'message' => 'Category not found']);
});

test('delete category moves to trash', function () {
    $category = Category::factory()->create();
    $response = $this->deleteJson('/api/' . API_VER . '/categories/' . $category->id);
    $response->assertStatus(200)
        ->assertJson(['message' => "Category <{$category->id}> moved to trash"]);
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('delete missing category returns error', function () {
    $response = $this->deleteJson('/api/' . API_VER . '/categories/9999');
    $response->assertStatus(404)
        ->assertJson(['success' => false, 'message' => 'Category not found']);
});

test('recoverAll restores trashed categories', function () {
    $category = Category::factory()->create();
    $category->delete();

    $response = $this->postJson("/api/" . API_VER . "/categories/trash/recover");
    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => 'all categories restored successfully']);
});

test('removeAll permanently deletes all trashed categories', function () {
    $category = Category::factory()->create();
    $category->delete();

    $response = $this->deleteJson("/api/" . API_VER . "/categories/trash/empty");
    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => 'all categories permanently deleted']);
});

test('recoverOne restores specific trashed category', function () {
    $category = Category::factory()->create();
    $category->delete();

    $response = $this->postJson("/api/" . API_VER . "/categories/trash/{$category->id}/recover");
    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => 'Category restored successfully']);
});

test('removeOne permanently deletes specific trashed category', function () {
    $category = Category::factory()->create();
    $category->delete();

    $response = $this->deleteJson("/api/" . API_VER . "/categories/trash/{$category->id}/remove");
    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => 'Category permanently deleted']);
});

