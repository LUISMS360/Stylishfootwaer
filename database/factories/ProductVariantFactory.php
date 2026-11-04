<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
    {
        $size = $this->faker->randomElement(['38', '39', '40', '41', '42', '43', '44']);
        $color = $this->faker->randomElement(['Negro', 'Blanco', 'Azul', 'Rojo', 'Gris', 'Beige']);

        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'size'       => $size,
            'color'      => $color,
            'stock'      => $this->faker->numberBetween(0, 50),
            'sku'        => strtoupper(Str::random(3)) . '-' . $size . '-' . strtoupper(substr($color, 0, 3)),
        ];
    }
}
