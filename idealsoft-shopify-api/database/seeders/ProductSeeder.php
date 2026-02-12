<?php

namespace Database\Seeders;

use App\Domains\Shopify\Models\Product;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()
            ->count(10)
            ->active()
            ->create();

        Product::factory()
            ->count(3)
            ->draft()
            ->create();

        Product::factory()
            ->count(2)
            ->archived()
            ->create();

        Product::factory()
            ->count(5)
            ->active()
            ->withMultipleVariants(4)
            ->create();
    }
}
