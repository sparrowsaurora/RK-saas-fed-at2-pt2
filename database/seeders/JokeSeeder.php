<?php

namespace Database\Seeders;

use App\Models\Joke;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;

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
        $categories = Category::all();
        $users = User::all();

        Joke::factory(20)->make()->each(function ($joke) use ($categories, $users) {
            $joke->user_id = $users->random()->id;
            $joke->save();

            $joke->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
