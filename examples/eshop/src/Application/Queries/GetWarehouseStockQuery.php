<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Queries;

final class GetWarehouseStockQuery
{
    public function __construct(
        public readonly ?string $warehouseId = null,
        public readonly ?string $productId = null
    ) {}
}
