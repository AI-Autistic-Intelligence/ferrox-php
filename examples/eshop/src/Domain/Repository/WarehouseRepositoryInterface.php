<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Repository;

use Ferrox\Examples\Eshop\Domain\Model\Warehouse;
use Ferrox\Utils\Types\Option;

interface WarehouseRepositoryInterface
{
    /**
     * @return Warehouse[]
     */
    public function findAll(): array;

    /**
     * @return Option<Warehouse>
     */
    public function findById(string $warehouseId): Option;

    /**
     * Finds the first warehouse with available stock for the requested product.
     * @return Option<Warehouse>
     */
    public function findAvailableWarehouse(string $productId, int $qty): Option;

    public function save(Warehouse $warehouse): void;
}
