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

    public function withCategory()
    {
        return $this->afterCreating(function (Joke $joke) {
//            // Attach 1-3 random categories
//            $categories = Category::factory()->count(rand(1, 3))->create();
//            $joke->categories()->attach($categories->pluck('id'));
            $joke->categories()->attach(Category::factory()->create());
        });
    }


//[
//'title' => "Skeleton Fight",
//'body' => "Why don't skeletons fight each other? They don't have the guts.",
//'category' => ['Pirate'],
//],
//
//foreach ($seedJokes as $jokeData) {
//$joke = Joke::create([
//'title' => $jokeData['title'],
//'content' => $jokeData['body'],
//'user_id' => $users->random()->id,
//]);
//
//$categoryIds = collect($jokeData['category'])->map(function ($name) {
//    return Category::firstOrCreate(['name' => $name])->id;
//});
//
//$joke->categories()->attach($categoryIds);
//}
}
