<?php

namespace Database\Seeders;

use App\Models\Joke;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class JokeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->count(5)->create();
        }

        // Get random users to assign jokes to
        $userIds = User::pluck('id')->toArray();

        // Seed 20 jokes
        Joke::factory()->count(20)->create([
            'user_id' => function () use ($userIds) {
                return $userIds[array_rand($userIds)];
            }
        ]);
    }
}
