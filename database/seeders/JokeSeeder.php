<?php

namespace Database\Seeders;

use App\Models\Joke;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class JokeSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->count(5)->create();
        }

        $users = User::all();

        $seedJokes = [
            [
                'title' => "Skeleton Fight",
                'body' => "Why don't skeletons fight each other? They don't have the guts.",
                'category' => ['Pirate'],
            ],
            [
                'title' => "Parallel Lines",
                'body' => "Parallel lines have so much in common. It's a shame they'll never meet.",
                'category' => ['Maths'],
            ],
            [
                'title' => "Embracing Mistakes",
                'body' => "I told my wife she should embrace her mistakes. She gave me a hug.",
                'category' => ['One-liner'],
            ],
            [
                'title' => "Broken Pencil",
                'body' => "I was going to tell a joke about a broken pencil, but it was pointless.",
                'category' => ['One-liner'],
            ],
            [
                'title' => "Light Sleeper",
                'body' => "I told my wife she should stop sleeping in the fridge. She said she's just a light sleeper.",
                'category' => ['One-liner'],
            ],
            [
                'title' => "Elevator Business",
                'body' => "I'm thinking of starting a business installing elevators. I hear it has its ups and downs.",
                'category' => ['One-liner'],
            ],
            [
                'title' => 'What is a pirate’s favourite element?',
                'body' => 'Arrrrrrrrgon',
                'category' => ['Science', 'Pirate']
            ],
            [
                'title' => 'Why did the amoeba fail the maths class?',
                'body' => 'Because it multiplied by dividing.',
                'category' => ['Science']
            ],
            [
                'title' => 'Why did the physicist break up with the biologist?',
                'body' => 'Because there was no chemistry.',
                'category' => ['Science']
            ],
            [
                'title' => 'What did the mum say to their messy kid?',
                'body' => 'I have a black belt in laundry.',
                'category' => ['Mum', 'Kids']
            ],
            [
                'title' => 'What did the toddler say to the tired mum?',
                'body' => 'Naptime for you, not me.',
                'category' => ['Mum', 'Kids']
            ],
            [
                'title' => 'What did the ocean say to the pirate?',
                'body' => 'Nothing. It just waved.',
                'category' => ['Pirate']
            ],
            [
                'title' => 'What is a pirate’s least favourite vegetable?',
                'body' => 'Leeks.',
                'category' => ['Food', 'Pirate']
            ],
            [
                'title' => 'I used to be a baker…',
                'body' => 'But I could not make enough dough.',
                'category' => ['Food', 'Puns']
            ],
            [
                'title' => 'What types of maths are pirates best at?',
                'body' => 'Algebra, because they are good at finding X.',
                'category' => ['Pirate', 'Maths']
            ],
        ];

//        $seedJokes = [
//            [
//                'title' => 'Skeleton Fight',
//                'content' => "Why don't skeletons fight each other? ".
//                    "Because they don't have the guts.",
//                'user_id' => 100,
//                'categories' => ['Skeleton'],
//            ],
//            [
//                'title' => 'Pirate Maths',
//                'content' => 'What type of Maths are pirates best at?'.
//                    'Algebra. Because they are good at finding X.',
//                'user_id' => 100,
//                'categories' => ['Pirate', 'Maths'],
//            ],
//            [
//                'title' => 'Why do Java developers wear glasses?',
//                'content' => 'Because they can\'t C#.',
//                'user_id' => 200,
//                'categories' => ['Programming'],
//            ],
//            [
//                'title' => 'Why did the programmer quit his job?',
//                'content' => 'Because he didn\'t get arrays.',
//                'user_id' => 300,
//                'categories' => ['Programming'],
//            ],
//            [
//                'title' => 'A SQL query walks into a bar...',
//                'content' => 'He walks up to two tables and asks, \'Can I join you?\'',
//                'user_id' => 301,
//                'categories' => ['Programming'],
//            ],
//        ];

        foreach ($seedJokes as $jokeData) {
            $joke = Joke::create([
                'title' => $jokeData['title'],
                'content' => $jokeData['body'],
                'user_id' => $users->random()->id,
            ]);

            $categoryIds = collect($jokeData['category'])->map(function ($name) {
                return Category::firstOrCreate(
                    ['title' => $name],
                    ['description' => fake()->sentence(4)]   // provide description if new
                );
            });

            $joke->categories()->attach($categoryIds);
        }
    }
}
