<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Models;

use App\Domains\Shopify\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_id',
        'title',
        'description',
        'handle',
        'vendor',
        'product_type',
        'status',
        'tags',
        'variants',
        'images',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'variants' => 'array',
            'images' => 'array',
            'status' => ProductStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('vendor', 'like', "%{$search}%")
                ->orWhere('handle', 'like', "%{$search}%");
        });
    }
}
