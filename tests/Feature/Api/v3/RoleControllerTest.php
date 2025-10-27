<?php

use App\Models\Category;
use App\Models\Joke;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
});

if (!defined('API_VER')) {
    define('API_VER', 'v3');
}

uses(RefreshDatabase::class);

test('super user', function () {
    $this->user->assignRole('Super-User');
    $this->actingAs($this->user, 'sanctum');

    $superUser = $this->getJson('/api/' . API_VER . '/test/super-user');
    $superUser->assertStatus(200); // ONLY PASS
    $admin = $this->getJson('/api/' . API_VER . '/test/admin');
    $admin->assertStatus(403);
    $staff = $this->getJson('/api/' . API_VER . '/test/staff');
    $staff->assertStatus(403);
    $client = $this->getJson('/api/' . API_VER . '/test/client');
    $client->assertStatus(403);
    $unauth = $this->getJson('/api/' . API_VER . '/test/un-auth');
    $unauth->assertStatus(403);
});

test('admin', function () {
    $this->user->assignRole('Administrator');
    $this->actingAs($this->user, 'sanctum');

    $superUser = $this->getJson('/api/' . API_VER . '/test/super-user');
    $superUser->assertStatus(403);
    $admin = $this->getJson('/api/' . API_VER . '/test/admin');
    $admin->assertStatus(200); // ONLY PASS
    $staff = $this->getJson('/api/' . API_VER . '/test/staff');
    $staff->assertStatus(403);
    $client = $this->getJson('/api/' . API_VER . '/test/client');
    $client->assertStatus(403);
    $unauth = $this->getJson('/api/' . API_VER . '/test/un-auth');
    $unauth->assertStatus(403);
});

test('staff', function () {
    $this->user->assignRole('Staff');
    $this->actingAs($this->user, 'sanctum');

    $superUser = $this->getJson('/api/' . API_VER . '/test/super-user');
    $superUser->assertStatus(403);
    $admin = $this->getJson('/api/' . API_VER . '/test/admin');
    $admin->assertStatus(403);
    $staff = $this->getJson('/api/' . API_VER . '/test/staff');
    $staff->assertStatus(200); // ONLY PASS
    $client = $this->getJson('/api/' . API_VER . '/test/client');
    $client->assertStatus(403);
    $unauth = $this->getJson('/api/' . API_VER . '/test/un-auth');
    $unauth->assertStatus(403);
});

test('client', function () {
    $this->user->assignRole('Client');
    $this->actingAs($this->user, 'sanctum');

    $superUser = $this->getJson('/api/' . API_VER . '/test/super-user');
    $superUser->assertStatus(403);
    $admin = $this->getJson('/api/' . API_VER . '/test/admin');
    $admin->assertStatus(403);
    $staff = $this->getJson('/api/' . API_VER . '/test/staff');
    $staff->assertStatus(403);
    $client = $this->getJson('/api/' . API_VER . '/test/client');
    $client->assertStatus(200); // ONLY PASS
    $unauth = $this->getJson('/api/' . API_VER . '/test/un-auth');
    $unauth->assertStatus(403);
});

test('unauthenticated', function () {
    $superUser = $this->getJson('/api/' . API_VER . '/test/super-user');
    $superUser->assertStatus(403);
    $admin = $this->getJson('/api/' . API_VER . '/test/admin');
    $admin->assertStatus(403);
    $staff = $this->getJson('/api/' . API_VER . '/test/staff');
    $staff->assertStatus(403);
    $client = $this->getJson('/api/' . API_VER . '/test/client');
    $client->assertStatus(403);
    $unauth = $this->getJson('/api/' . API_VER . '/test/un-auth');
    $unauth->assertStatus(200); // ONLY PASS

});
