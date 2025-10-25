<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

if (!defined('API_VER')) {
    define('API_VER', 'v3');
}

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Sanctum::actingAs($user, ['*']); // API token auth

    $response = $this->putJson('/api/' . API_VER . '/auth/profile', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Profile updated successfully',
        ]);

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/' . API_VER . '/auth/profile', [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Current password is incorrect',
        ]);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});
