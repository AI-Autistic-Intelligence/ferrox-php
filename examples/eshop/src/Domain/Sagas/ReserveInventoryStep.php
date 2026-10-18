<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

use Ferrox\Utils\Types\Result;

/**
 * Step 1: Acquires distributed Redis Redlock and reserves inventory in the warehouse.
 * On downstream failure, rolls back reserved inventory and releases the Redlock.
 */
final class ReserveInventoryStep implements SagaStepInterface
{
    private RedisRedlockInterface $redlock;
    /** @var array<string, float> */
    private array $warehouseStock;

    /**
     * @param array<string, float> $initialStock
     */
    public function __construct(RedisRedlockInterface $redlock, array $initialStock = [])
    {
        $this->redlock = $redlock;
        $this->warehouseStock = !empty($initialStock) ? $initialStock : [
            'BTC/USD' => 100.0,
            'ETH/USD' => 500.0,
            'default' => 50.0,
        ];
    }

    public function getName(): string
    {
        return 'ReserveInventory';
    }

    public function execute(array &$context): Result
    {
        $sku = (string) ($context['symbol'] ?? 'BTC/USD');
        $qty = (float) ($context['quantity'] ?? 1.0);
        $resource = "lock:inventory:" . str_replace('/', '_', $sku);

        // 1. Acquire distributed Redlock
        $token = $this->redlock->acquire($resource, 5000);
        if ($token === null) {
            return Result::err("CONCURRENCY_CONFLICT: Could not acquire distributed Redlock on {$resource}. Another transaction is mutating stock.");
        }
        $context['redlock_token'] = $token;
        $context['redlock_resource'] = $resource;

        // 2. Validate stock
        $currentStock = $this->warehouseStock[$sku] ?? 0.0;
        if ($currentStock < $qty) {
            // Release lock before failing
            $this->redlock->release($resource, $token);
            unset($context['redlock_token']);
            return Result::err(sprintf(
                "INSUFFICIENT_STOCK: Required %.4f of %s, but only %.4f available in warehouse.",
                $qty,
                $sku,
                $currentStock
            ));
        }

        // 3. Reserve stock
        $this->warehouseStock[$sku] = $currentStock - $qty;
        $context['reserved_quantity'] = $qty;
        $context['remaining_stock'] = $this->warehouseStock[$sku];

        return Result::ok(true);
    }

    public function compensate(array &$context): Result
    {
        $sku = (string) ($context['symbol'] ?? 'BTC/USD');
        $reservedQty = (float) ($context['reserved_quantity'] ?? 0.0);

        if ($reservedQty > 0.0) {
            $this->warehouseStock[$sku] = ($this->warehouseStock[$sku] ?? 0.0) + $reservedQty;
            $context['remaining_stock'] = $this->warehouseStock[$sku];
            unset($context['reserved_quantity']);
        }

        // Release distributed Redlock
        if (!empty($context['redlock_resource']) && !empty($context['redlock_token'])) {
            $this->redlock->release($context['redlock_resource'], $context['redlock_token']);
            unset($context['redlock_token']);
        }

        return Result::ok(true);
    }

    public function getStock(string $sku): float
    {
        return $this->warehouseStock[$sku] ?? 0.0;
    }
}
