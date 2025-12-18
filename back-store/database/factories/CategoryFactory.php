<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'name' => $name,
            'description' => $this->faker->sentence(),
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
        ];
    }
}
