<?php

declare(strict_types=1);

namespace App\Domains\Shopify\DTOs;

/**
 * @phpstan-type VariantShape array{title: string|null, sku: string|null, price: string, compare_at_price: string|null, inventory_quantity: int|null}
 * @phpstan-type ImageShape array{src: string, alt: string|null, position: int|null, width: int|null, height: int|null}
 */
final readonly class ShopifyProductData
{
    public function __construct(
        public string $shopifyId,
        public string $title,
        public ?string $description,
        public string $handle,
        public ?string $vendor,
        public ?string $productType,
        public string $status,
        public array $tags,
        public array $variants,
        public array $images,
        public ?string $publishedAt,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            shopifyId: (string) $data['id'],
            title: $data['title'] ?? '',
            description: $data['body_html'] ?? null,
            handle: $data['handle'] ?? '',
            vendor: $data['vendor'] ?? null,
            productType: $data['product_type'] ?? null,
            status: $data['status'] ?? 'active',
            tags: self::parseTags($data['tags'] ?? ''),
            variants: self::mapVariants($data['variants'] ?? []),
            images: self::mapImages($data['images'] ?? []),
            publishedAt: $data['published_at'] ?? null,
        );
    }

    private static function parseTags(string $tags): array
    {
        if (trim($tags) === '') {
            return [];
        }

        return array_map('trim', explode(',', $tags));
    }

    private static function mapVariants(array $variants): array
    {
        return array_map(fn (array $v): array => [
            'title' => $v['title'] ?? null,
            'sku' => $v['sku'] ?? null,
            'price' => $v['price'] ?? '0.00',
            'compare_at_price' => $v['compare_at_price'] ?? null,
            'inventory_quantity' => $v['inventory_quantity'] ?? null,
        ], $variants);
    }

    private static function mapImages(array $images): array
    {
        return array_map(fn (array $img): array => [
            'src' => $img['src'] ?? '',
            'alt' => $img['alt'] ?? null,
            'position' => $img['position'] ?? null,
            'width' => $img['width'] ?? null,
            'height' => $img['height'] ?? null,
        ], $images);
    }
}
