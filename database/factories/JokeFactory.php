<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Joke;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Joke>
 */
class JokeFactory extends Factory
{
    protected $model = Joke::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'user_id' => User::factory(),
        ];
    }

    /*
     * creates a joke w/ a category attached
     */
    public function withCategory()
    {
        return $this->afterCreating(function (Joke $joke) {
            $joke->categories()->attach(Category::factory()->create());
        });
    }
}
