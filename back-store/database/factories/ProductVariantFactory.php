<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

   public function definition(): array
{
    return [
        'product_id' => \App\Models\Product::inRandomOrder()->first()?->id,
        'size_id' => \App\Models\Size::inRandomOrder()->first()?->id,
        'color_id' => \App\Models\Color::inRandomOrder()->first()?->id,
        'original_price' => $this->faker->numberBetween(100000, 500000),
        'sale_price' => $this->faker->optional()->numberBetween(50000, 400000),
        'stock' => $this->faker->numberBetween(1, 50),
        'status' => 1,
    ];
}

}
