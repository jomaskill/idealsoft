<?php

namespace Database\Factories;

use App\Domains\Shopify\Enums\ProductStatus;
use App\Domains\Shopify\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'shopify_id' => (string) fake()->unique()->numberBetween(1000000000, 9999999999),
            'title' => $title,
            'description' => fake()->paragraph(),
            'handle' => str($title)->slug()->toString(),
            'vendor' => fake()->company(),
            'product_type' => fake()->randomElement(['T-Shirt', 'Hoodie', 'Sneakers', 'Hat', 'Jacket']),
            'status' => fake()->randomElement(ProductStatus::cases()),
            'tags' => fake()->randomElements(['sale', 'new', 'featured', 'seasonal', 'bestseller'], 2),
            'variants' => [
                [
                    'title' => 'Default',
                    'sku' => fake()->unique()->bothify('SKU-####-??'),
                    'price' => fake()->randomFloat(2, 9.99, 199.99),
                    'compare_at_price' => fake()->optional(0.3)->randomFloat(2, 29.99, 299.99),
                    'inventory_quantity' => fake()->numberBetween(0, 500),
                ],
            ],
            'images' => [
                [
                    'src' => fake()->imageUrl(640, 480, 'products'),
                    'alt' => $title,
                    'position' => 1,
                    'width' => 640,
                    'height' => 480,
                ],
            ],
            'published_at' => fake()->optional(0.8)->dateTimeBetween('-1 year'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => ProductStatus::Active]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => ProductStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => ProductStatus::Archived]);
    }

    public function withMultipleVariants(int $count = 3): static
    {
        return $this->state(fn (): array => [
            'variants' => collect(range(1, $count))->map(fn (int $i): array => [
                'title' => "Variant {$i}",
                'sku' => fake()->unique()->bothify('SKU-####-??'),
                'price' => (string) fake()->randomFloat(2, 9.99, 199.99),
                'compare_at_price' => fake()->optional(0.3)->randomFloat(2, 29.99, 299.99),
                'inventory_quantity' => fake()->numberBetween(0, 500),
            ])->all(),
        ]);
    }
}
