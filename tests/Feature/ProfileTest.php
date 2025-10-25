<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user profile can be retrieved', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson('/api/v3/auth/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['user' => ['id', 'name', 'email']],
        ]);
});

test('user profile can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson('/api/v3/auth/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.user.name', 'Test User')
        ->assertJsonPath('data.user.email', 'test@example.com');
});

test('password cannot be changed without current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword1'),
    ]);

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson('/api/v3/auth/profile', [
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid input',
        ]);
});

test('user can delete their account with correct credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($user, 'sanctum')
        ->deleteJson('/api/v3/auth/profile', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);

    expect(User::find($user->id))->toBeNull();
});

test('user cannot delete account with wrong password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($user, 'sanctum')
        ->deleteJson('/api/v3/auth/profile', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid password',
        ]);

    expect(User::find($user->id))->not->toBeNull();
});
