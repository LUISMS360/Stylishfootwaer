<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         $name = $this->faker->words(3, true);

        return [
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'brand_id'    => Brand::inRandomOrder()->first()?->id ?? Brand::factory(),
            'description' => $this->faker->paragraph(),
            'price'       => $this->faker->randomFloat(2, 10, 500),
            'image'       => 'products/' . $this->faker->image('public/storage/products', 640, 480, null, false),
            'is_active'   => $this->faker->boolean(80), // 80% de probabilidad de ser verdadero
        ];
    }
}
