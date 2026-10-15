<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Handlers;

use Ferrox\Examples\Eshop\Application\Queries\GetOrderQuery;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoOrderRepository;
use Ferrox\Utils\Types\Result;

final class GetOrderHandler
{
    public function __construct(
        private PdoOrderRepository $orderRepo
    ) {}

    public function handle(GetOrderQuery $query): Result
    {
        $order = $this->orderRepo->findById($query->orderId);
        if ($order === null) {
            return Result::err("Order {$query->orderId} not found");
        }
        return Result::ok($order);
    }
}
