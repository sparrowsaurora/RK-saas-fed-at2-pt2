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
});

test('admin', function () {
    $this->user->assignRole('Administrator');
    $this->actingAs($this->user, 'sanctum');
});

test('staff', function () {
    $this->user->assignRole('Staff');
    $this->actingAs($this->user, 'sanctum');
});

test('client', function () {
    $this->user->assignRole('Client');
    $this->actingAs($this->user, 'sanctum');
});

test('unauthenticated', function () {
    $response = $this->getJson('/api/' . API_VER . '/jokes/random');
});
