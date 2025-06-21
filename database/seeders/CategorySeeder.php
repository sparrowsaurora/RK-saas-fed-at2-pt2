<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Pun',
            'Dad Joke',
            'Dark Humor',
            'One-Liner',
            'Knock-Knock',
            'Programming',
            'Animals',
            'School',
            'Politics',
            'Workplace',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
