<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Repository;

use Ferrox\Examples\Eshop\Domain\Model\Order;
use Ferrox\Utils\Types\Option;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;
    /**
     * @return Option<Order>
     */
    public function findById(string $orderId): Option;
    /**
     * @return Order[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array;
}
