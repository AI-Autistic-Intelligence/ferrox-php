<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Http\Controllers;

use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;
use Ferrox\Examples\Eshop\Application\Handlers\GetWarehouseStockHandler;
use Ferrox\Examples\Eshop\Application\Queries\GetWarehouseStockQuery;

final class WarehouseController
{
    public function __construct(
        private GetWarehouseStockHandler $handler
    ) {}

    public function listWarehouses(Request $request): Response
    {
        $result = $this->handler->handle(new GetWarehouseStockQuery());
        return Response::json([
            'success' => true,
            'data' => $result->unwrap()
        ], 200);
    }

    public function getWarehouse(Request $request, string $warehouseId): Response
    {
        $result = $this->handler->handle(new GetWarehouseStockQuery(warehouseId: $warehouseId));
        if ($result->isErr()) {
            return Response::json([
                'type' => 'https://ferrox.dev/errors/not-found',
                'title' => 'Warehouse Not Found',
                'status' => 404,
                'detail' => $result->unwrapErr()
            ], 404);
        }

        return Response::json([
            'success' => true,
            'data' => $result->unwrap()
        ], 200);
    }
}
