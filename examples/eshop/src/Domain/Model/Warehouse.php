<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Model;

final class Warehouse
{
    /** @var array<string, int> productId => availableStock */
    private array $stock = [];

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $location,
        array $initialStock = []
    ) {
        $this->stock = $initialStock;
    }

    public function getStock(string $productId): int
    {
        return $this->stock[$productId] ?? 0;
    }

    public function setStock(string $productId, int $qty): void
    {
        $this->stock[$productId] = $qty;
    }

    public function hasStock(string $productId, int $qty): bool
    {
        return $this->getStock($productId) >= $qty;
    }

    public function decrement(string $productId, int $qty): void
    {
        $current = $this->getStock($productId);
        if ($current < $qty) {
            throw new \UnderflowException("Warehouse {$this->id} insufficient stock for {$productId}: has {$current}, requested {$qty}");
        }
        $this->stock[$productId] = $current - $qty;
    }

    public function increment(string $productId, int $qty): void
    {
        $current = $this->getStock($productId);
        $this->stock[$productId] = $current + $qty;
    }

    public function getAllStock(): array
    {
        return $this->stock;
    }
}
