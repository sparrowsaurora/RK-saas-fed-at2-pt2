<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

if (!defined('API_VER')) {
    define('API_VER', 'v3');
}

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/' . API_VER . '/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ])
        ->assertJson(['success' => true, 'message' => 'Login successful']);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/api/' . API_VER . '/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = $this->postJson('/api/' . API_VER . '/auth/logout', [], [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'message' => 'Logout successful']);
});
