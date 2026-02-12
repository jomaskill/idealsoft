<?php

declare(strict_types=1);

use App\Domains\Shopify\Models\Product;

it('returns paginated products', function (): void {
    Product::factory()->count(20)->active()->create();

    $response = $this->postJson('/graphql', [
        'query' => '{
            products(first: 5) {
                data {
                    id
                    title
                    status
                }
                paginatorInfo {
                    total
                    currentPage
                    lastPage
                }
            }
        }',
    ]);

    $response->assertSuccessful();
    $response->assertJsonCount(5, 'data.products.data');
    $response->assertJsonPath('data.products.paginatorInfo.total', 20);
});

it('filters products by status', function (): void {
    Product::factory()->count(3)->active()->create();
    Product::factory()->count(2)->draft()->create();

    $response = $this->postJson('/graphql', [
        'query' => '{
            products(first: 50, status: DRAFT) {
                data {
                    id
                    status
                }
            }
        }',
    ]);

    $response->assertSuccessful();
    $response->assertJsonCount(2, 'data.products.data');
});

it('filters products by vendor', function (): void {
    Product::factory()->create(['vendor' => 'Acme Corp']);
    Product::factory()->create(['vendor' => 'Other Brand']);

    $response = $this->postJson('/graphql', [
        'query' => '{
            products(first: 50, vendor: "Acme Corp") {
                data {
                    id
                    vendor
                }
            }
        }',
    ]);

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data.products.data');
    $response->assertJsonPath('data.products.data.0.vendor', 'Acme Corp');
});

it('searches products by title', function (): void {
    Product::factory()->create(['title' => 'Blue Widget']);
    Product::factory()->create(['title' => 'Red Gadget']);

    $response = $this->postJson('/graphql', [
        'query' => '{
            products(first: 50, search: "Widget") {
                data {
                    id
                    title
                }
            }
        }',
    ]);

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data.products.data');
    $response->assertJsonPath('data.products.data.0.title', 'Blue Widget');
});

it('returns a single product by ID', function (): void {
    $product = Product::factory()->create();

    $response = $this->postJson('/graphql', [
        'query' => "{
            product(id: {$product->id}) {
                id
                title
                shopify_id
                variants {
                    sku
                    price
                }
                images {
                    src
                }
            }
        }",
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.product.title', $product->title);
});

it('returns a single product by shopify_id', function (): void {
    $product = Product::factory()->create(['shopify_id' => 'shp_12345']);

    $response = $this->postJson('/graphql', [
        'query' => '{
            product(shopify_id: "shp_12345") {
                id
                title
                shopify_id
            }
        }',
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.product.shopify_id', 'shp_12345');
});

it('returns null for non-existent product', function (): void {
    $response = $this->postJson('/graphql', [
        'query' => '{
            product(id: 99999) {
                id
                title
            }
        }',
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.product', null);
});
