<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Persistence;

use Ferrox\Database\Core\AbstractRepository;
use Ferrox\Database\Core\UnitOfWorkInterface;
use Ferrox\Examples\Eshop\Domain\Model\Warehouse;
use Ferrox\Examples\Eshop\Domain\Repository\WarehouseRepositoryInterface;
use Ferrox\Observability\Metrics\PrometheusRegistry;
use Ferrox\Utils\Types\Option;
use PDO;

final class PdoWarehouseRepository extends AbstractRepository implements WarehouseRepositoryInterface
{
    /** @var array<string, Warehouse> */
    private array $memoryStore = [];
    private ?PDO $pdo = null;

    public function __construct(UnitOfWorkInterface $uow, ?PDO $pdo = null)
    {
        parent::__construct($uow);
        $this->pdo = $pdo;
        $this->seedDefaults();
    }

    private function seedDefaults(): void
    {
        $whRome = new Warehouse('WH-ROME', 'Rome Hub', 'Rome, Italy', [
            'PROD-1' => 0, // Depleted in Rome
            'PROD-2' => 20
        ]);
        $whMilan = new Warehouse('WH-MILAN', 'Milan Central', 'Milan, Italy', [
            'PROD-1' => 8, // Available in Milan (Fallback target)
            'PROD-2' => 50
        ]);
        $whNaples = new Warehouse('WH-NAPLES', 'Naples Hub', 'Naples, Italy', [
            'PROD-1' => 0,
            'PROD-2' => 15
        ]);

        $this->memoryStore[$whRome->id] = $whRome;
        $this->memoryStore[$whMilan->id] = $whMilan;
        $this->memoryStore[$whNaples->id] = $whNaples;

        // Register initial gauges in Prometheus
        foreach ($this->memoryStore as $wh) {
            foreach ($wh->getAllStock() as $prodId => $stock) {
                PrometheusRegistry::setGauge('ferrox_warehouse_stock', $stock, [
                    'warehouse' => $wh->id,
                    'product' => $prodId
                ]);
            }
        }
    }

    public function findById(string $id): ?array
    {
        if (isset($this->memoryStore[$id])) {
            $wh = $this->memoryStore[$id];
            return [
                'id' => $wh->id,
                'name' => $wh->name,
                'location' => $wh->location,
                'stock' => $wh->getAllStock()
            ];
        }
        return null;
    }

    public function findWarehouseEntity(string $id): Option
    {
        if (isset($this->memoryStore[$id])) {
            return Option::some($this->memoryStore[$id]);
        }
        return Option::none();
    }

    public function findAll(): array
    {
        return array_values($this->memoryStore);
    }

    public function findAvailableWarehouse(string $productId, int $qty): Option
    {
        foreach ($this->memoryStore as $warehouse) {
            $currentStock = $warehouse->getStock($productId);
            PrometheusRegistry::setGauge('ferrox_warehouse_stock', $currentStock, [
                'warehouse' => $warehouse->id,
                'product' => $productId
            ]);

            if ($currentStock >= $qty) {
                return Option::some($warehouse);
            }
        }
        return Option::none();
    }

    public function save(Warehouse $warehouse): void
    {
        $this->transaction(function () use ($warehouse) {
            $this->memoryStore[$warehouse->id] = $warehouse;
            foreach ($warehouse->getAllStock() as $productId => $qty) {
                PrometheusRegistry::setGauge('ferrox_warehouse_stock', $qty, [
                    'warehouse' => $warehouse->id,
                    'product' => $productId
                ]);
            }
        });
    }

    public function decrementStock(string $warehouseId, string $productId, int $qty): void
    {
        $this->transaction(function () use ($warehouseId, $productId, $qty) {
            if (!isset($this->memoryStore[$warehouseId])) {
                throw new \InvalidArgumentException("Warehouse {$warehouseId} not found");
            }
            $wh = $this->memoryStore[$warehouseId];
            $wh->decrement($productId, $qty);
            $newStock = $wh->getStock($productId);

            if ($newStock < 0) {
                PrometheusRegistry::increment('ferrox_critical_anomalies_total', ['type' => 'negative_stock']);
                throw new \RuntimeException("CRITICAL ANOMALY: Negative stock invariant breached in {$warehouseId}");
            }

            PrometheusRegistry::setGauge('ferrox_warehouse_stock', $newStock, [
                'warehouse' => $warehouseId,
                'product' => $productId
            ]);
        });
    }

    public function incrementStock(string $warehouseId, string $productId, int $qty): void
    {
        $this->transaction(function () use ($warehouseId, $productId, $qty) {
            if (isset($this->memoryStore[$warehouseId])) {
                $wh = $this->memoryStore[$warehouseId];
                $wh->increment($productId, $qty);
                PrometheusRegistry::setGauge('ferrox_warehouse_stock', $wh->getStock($productId), [
                    'warehouse' => $warehouseId,
                    'product' => $productId
                ]);
            }
        });
    }
}
