<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Model;

final class OrderItem
{
    public function __construct(
        public readonly string $productId,
        public readonly string $productName,
        public readonly int $quantity,
        public readonly int $unitPriceCents
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }
        if ($unitPriceCents < 0) {
            throw new \InvalidArgumentException('Unit price cannot be negative.');
        }
    }

    public function subtotalCents(): int
    {
        return $this->quantity * $this->unitPriceCents;
    }
}
