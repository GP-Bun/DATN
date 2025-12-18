<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

   public function definition(): array
{
    $name = $this->faker->words(2, true);

    return [
        'name' => $name,
        'description' => $this->faker->sentence(),
        'price' => $this->faker->numberBetween(100000, 500000),
        'stock' => $this->faker->numberBetween(10, 100),
        'auto_status' => 1,
        'category_id' => \App\Models\Category::inRandomOrder()->first()?->id 
                        ?? \App\Models\Category::factory()->create()->id,
        'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
    ];
}

}
