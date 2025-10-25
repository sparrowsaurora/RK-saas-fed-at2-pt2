<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

if (!defined('API_VER')) {
    define('API_VER', 'v3');
}

test('users can register', function () {
    $response = $this->postJson('/api/' . API_VER . '/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ])
        ->assertJson(['success' => true, 'message' => 'User successfully created']);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});
