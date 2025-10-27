<?php

namespace Database\Factories;

use App\Models\Joke;
use App\Models\JokeReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JokeReactionFactory extends Factory
{
    protected $model = JokeReaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'joke_id' => Joke::factory(),
            'is_positive' => $this->faker->boolean(),
        ];
    }
}
