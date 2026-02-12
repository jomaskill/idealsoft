<?php

declare(strict_types=1);

namespace App\Domains\Shopify\DTOs;

final readonly class SyncResult
{
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $failed = 0,
    ) {}

    public function total(): int
    {
        return $this->created + $this->updated + $this->failed;
    }

    public function merge(self $other): self
    {
        return new self(
            created: $this->created + $other->created,
            updated: $this->updated + $other->updated,
            failed: $this->failed + $other->failed,
        );
    }
}
