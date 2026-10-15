<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Persistence;

use Ferrox\Database\Core\AbstractRepository;
use Ferrox\Database\Core\UnitOfWorkInterface;
use Ferrox\Examples\Eshop\Domain\Model\Order;
use Ferrox\Examples\Eshop\Domain\Repository\OrderRepositoryInterface;
use Ferrox\Utils\Types\Option;
use PDO;

final class PdoOrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    /** @var array<string, Order> */
    private array $orders = [];
    private ?PDO $pdo = null;

    public function __construct(UnitOfWorkInterface $uow, ?PDO $pdo = null)
    {
        parent::__construct($uow);
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?array
    {
        if (isset($this->orders[$id])) {
            return $this->orders[$id]->toArray();
        }
        return null;
    }

    public function findOrderEntity(string $id): Option
    {
        if (isset($this->orders[$id])) {
            return Option::some($this->orders[$id]);
        }
        return Option::none();
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $all = array_values($this->orders);
        return array_slice($all, $offset, $limit);
    }

    public function save(Order $order): void
    {
        $this->transaction(function () use ($order) {
            $this->orders[$order->id] = $order;
        });
    }
}
