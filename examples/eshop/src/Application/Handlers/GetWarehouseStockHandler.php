<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Handlers;

use Ferrox\Examples\Eshop\Application\Queries\GetWarehouseStockQuery;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoWarehouseRepository;
use Ferrox\Utils\Types\Result;

final class GetWarehouseStockHandler
{
    public function __construct(
        private PdoWarehouseRepository $warehouseRepo
    ) {}

    public function handle(GetWarehouseStockQuery $query): Result
    {
        if ($query->warehouseId !== null) {
            $wh = $this->warehouseRepo->findById($query->warehouseId);
            if ($wh === null) {
                return Result::err("Warehouse {$query->warehouseId} not found");
            }
            return Result::ok($wh);
        }

        $all = $this->warehouseRepo->findAll();
        $list = array_map(static fn ($w) => [
            'id' => $w->id,
            'name' => $w->name,
            'location' => $w->location,
            'stock' => $w->getAllStock()
        ], $all);

        return Result::ok($list);
    }
}
