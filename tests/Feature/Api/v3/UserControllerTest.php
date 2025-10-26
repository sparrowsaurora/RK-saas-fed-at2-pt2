<?php

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

test('retrieve all users', function () {
    // Arrange
    $users = User::factory(5)->create();

    $expectedData = [
        'message' => 'Users retrieved successfully',
        'success' => true,
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/admin/users');

    // Assert
    $response
        ->assertStatus(200)
        ->assertJsonCount(6, 'data.users') // 5 + user for test
        ->assertJson($expectedData);
});

test('retrieve one user', function () {
    // Arrange
    $user = User::factory()->create();

    $expectedData = [
        'message' => 'User retrieved successfully',
        'success' => true,
        'data' => [
            'User' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]
        ],
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/admin/users/' . $user->id);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson($expectedData)
        ->assertJsonCount(1, 'data');
});

test('return error on missing user', function () {
    $expectedData = [
        'message' => 'User not found',
        'success' => false,
        'data' => [],
    ];

    // Act
    $response = $this->getJson('/api/' . API_VER . '/admin/users/9999');

    // Assert
    $response
        ->assertStatus(404)
        ->assertJson($expectedData)
        ->assertJsonCount(0, 'data');
});

///////////////////
///
/// NOT A METHOD USER'S CREATE OWN THROUGH REGISTER
///
/// ///////////////
/*
test('create a new user', function () {
    // Arrange
    $data = [
        'name' => 'Fake User',
        'email' => 'fake@example.com',
        'password' => 'password123',
    ];

    $expectedResponse = [
        'message' => 'User created successfully',
        'success' => true,
    ];

    // Act
    $response = $this->postJson('/api/' . API_VER . '/admin/users', $data);

    // Assert
    $response
        ->assertStatus(201)
        ->assertJsonFragment($expectedResponse)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'email']
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'fake@example.com',
    ]);
});
test('create user with invalid data fails', function () {
    // Missing name, invalid email, short password
    $data = [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
    ];

    $response = $this->postJson('/api/' . API_VER . '/admin/users', $data);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
});
*/

test('update user information', function () {
    // Arrange
    $user = User::factory()->create();
    $data = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    $expected = [
        'message' => 'User updated successfully',
        'success' => true,
    ];

    // Act
    $response = $this->putJson('/api/' . API_VER . '/admin/users/' . $user->id, $data);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJsonFragment($expected)
        ->assertJsonFragment($data);

    $this->assertDatabaseHas('users', [
        'email' => 'updated@example.com',
    ]);
});

test('delete a user', function () {
    // Arrange
    $user = User::factory()->create();

    $expected = [
        'message' => 'User <'. $user->id .'> moved deleted successfully',
        'success' => true,
    ];

    // Act
    $response = $this->deleteJson('/api/' . API_VER . '/admin/users/' . $user->id);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJsonFragment($expected);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('delete missing user returns error', function () {
    $expected = [
        'message' => 'User not found',
        'success' => false,
        'data' => [],
    ];

    $response = $this->deleteJson('/api/' . API_VER . '/admin/users/9999');

    $response
        ->assertStatus(404)
        ->assertJson($expected);
});

test('admin dashboard returns counts', function () {
    $users = User::factory(3)->create(['status' => 'suspended']);

    $response = $this->getJson('/api/' . API_VER . '/admin');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'userCount' => '4', // 3 + seeded user
                'userSuspendedCount' => '3',
            ],
        ]);
});

test('search returns users by name or email', function () {
    $searchUser = User::factory()->create(['name' => 'Search Me', 'email' => 'search@example.com']);

    $response = $this->postJson('/api/' . API_VER . '/admin/users/search', ['search' => 'Search']);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Users retrieved',
            'data' => ['resultsCount' => 1],
        ]);
});

test('search with empty string returns error', function () {
    $response = $this->postJson('/api/' . API_VER . '/admin/users/search', ['search' => '']);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['search']);
});

test('search with no results', function () {
    $response = $this->postJson('/api/' . API_VER . '/admin/users/search', ['search' => 'nonexistent']);
    $response->assertStatus(404)
        ->assertJson(['success' => false, 'message' => 'No results found']);
});

test('suspend a user', function () {
    $user = User::factory()->create();

    $response = $this->postJson("/api/" . API_VER . "/admin/users/suspend/{$user->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => "suspended user <{$user->id}> successfully"]);

    $this->assertEquals('suspended', $user->fresh()->status);
});

test('unsuspend a user', function () {
    $user = User::factory()->create(['status' => 'suspended']);

    $response = $this->postJson("/api/" . API_VER . "/admin/users/unsuspend/{$user->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => "unsuspended user <{$user->id}> successfully"]);

    $this->assertNull($user->fresh()->status);
});

