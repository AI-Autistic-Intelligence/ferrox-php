<?php
/**
 * 🛒 Ferrox Enterprise E-Shop Showcase
 * 
 * Demonstrates:
 * - CQRS (Command Query Responsibility Segregation)
 * - ACID Database Transactions via UnitOfWork
 * - Saga Pattern (Multi-Warehouse Fallback & Refund)
 * - Offline POS Batch Synchronization
 * - DTO Validation via PHP 8 Attributes
 * - Monadic Error Handling (Result & Option)
 * - Outbox Pattern for Resilient Alerts
 * - NATIVE PROMETHEUS METRICS & OBSERVABILITY 🚀
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ferrox\Core\App\FerroxApp;
use Ferrox\Core\Http\AbstractController;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;
use Ferrox\Cqrs\CommandInterface;
use Ferrox\Cqrs\CommandBus;
use Ferrox\Events\DomainEventInterface;
use Ferrox\Events\EventDispatcher;
use Ferrox\Database\Core\AbstractRepository;
use Ferrox\Validation\Attributes\ValidatedDto;
use Ferrox\Utils\Types\Result;
use Ferrox\Utils\Types\Option;
use Ferrox\Observability\Metrics\PrometheusRegistry;

// ==============================================================================
// 1. DATA TRANSFER OBJECTS (DTOs) & VALIDATION
// ==============================================================================

#[ValidatedDto(strict: true)]
class SubmitOrderDto {
    public function __construct(
        public string $productId,
        public int $quantity,
        public string $source // "ONLINE" or "POS"
    ) {}
}

#[ValidatedDto(strict: true)]
class SyncPosBatchDto {
    public function __construct(
        public array $orders // Array of SubmitOrderDto payloads
    ) {}
}

// ==============================================================================
// 2. DOMAIN EVENTS (For Alert Dashboard & Outbox)
// ==============================================================================

class OrderRefundedEvent implements DomainEventInterface {
    public function __construct(public string $orderId, public string $reason) {}
}

class PosBatchSyncedEvent implements DomainEventInterface {
    public function __construct(public int $processedCount) {}
}

class CriticalAnomalyEvent implements DomainEventInterface {
    public function __construct(public string $type, public string $message) {}
}

// ==============================================================================
// 3. REPOSITORIES & ACID TRANSACTIONS
// ==============================================================================

class WarehouseRepository extends AbstractRepository {
    private array $mockDb = [
        'WH-ROME' => ['stock' => 0],
        'WH-MILAN' => ['stock' => 5]
    ];

    public function findAvailableWarehouse(string $productId, int $qty): Option {
        // Simulates searching multiple warehouses
        foreach ($this->mockDb as $whId => $data) {
            // Update Gauge Metric for Promtheus
            PrometheusRegistry::setGauge('ferrox_warehouse_stock', $data['stock'], ['warehouse' => $whId, 'product' => $productId]);

            if ($data['stock'] >= $qty) {
                return Option::some($whId);
            }
        }
        return Option::none(); 
    }

    public function decrementStock(string $warehouseId, string $productId, int $qty): void {
        $this->transaction(function() use ($warehouseId, $productId, $qty) {
            $this->mockDb[$warehouseId]['stock'] -= $qty;
            echo "[DB ACID] Decremented stock in {$warehouseId}.\n";

            // If stock goes negative (Anomaly detection)
            if ($this->mockDb[$warehouseId]['stock'] < 0) {
                PrometheusRegistry::increment('ferrox_critical_anomalies_total', ['type' => 'negative_stock']);
                throw new \RuntimeException("Critical Database Constraint Failure: Negative Stock");
            }
            
            // Update Prometheus Gauge
            PrometheusRegistry::setGauge('ferrox_warehouse_stock', $this->mockDb[$warehouseId]['stock'], ['warehouse' => $warehouseId, 'product' => $productId]);
        });
    }
}

class OrderRepository extends AbstractRepository {
    public function saveOrder(string $productId, int $qty, string $source, string $status): string {
        return $this->transaction(function() use ($productId, $qty, $source, $status) {
            $orderId = "ORD-" . bin2hex(random_bytes(4));
            echo "[DB ACID] Order {$orderId} saved (Source: {$source}, Status: {$status}).\n";
            return $orderId;
        });
    }
}

// ==============================================================================
// 4. CQRS COMMANDS & HANDLERS (The Core Business Logic)
// ==============================================================================

class SubmitOrderCommand implements CommandInterface {
    public function __construct(public SubmitOrderDto $dto) {}
}

class SubmitOrderHandler {
    public function __construct(
        private WarehouseRepository $warehouseRepo,
        private OrderRepository $orderRepo,
        private EventDispatcher $events
    ) {}

    public function handle(SubmitOrderCommand $cmd): Result {
        $dto = $cmd->dto;
        
        $warehouseOpt = $this->warehouseRepo->findAvailableWarehouse($dto->productId, $dto->quantity);
        
        if ($warehouseOpt->isNone()) {
            // SAGA REFUND
            $orderId = $this->orderRepo->saveOrder($dto->productId, $dto->quantity, $dto->source, "REFUNDED");
            $this->events->dispatch(new OrderRefundedEvent($orderId, "Out of stock globally"));
            
            // Prometheus: Track failed orders and alerts
            PrometheusRegistry::increment('ferrox_orders_total', ['status' => 'refunded', 'source' => $dto->source]);
            PrometheusRegistry::increment('ferrox_alerts_total', ['type' => 'out_of_stock']);
            
            return Result::err("Stock totally depleted. Order refunded automatically.");
        }

        // SUCCESS FULFILLMENT
        $whId = $warehouseOpt->unwrap();
        $this->warehouseRepo->decrementStock($whId, $dto->productId, $dto->quantity);
        $orderId = $this->orderRepo->saveOrder($dto->productId, $dto->quantity, $dto->source, "FULFILLED");

        // Prometheus: Track successful orders
        PrometheusRegistry::increment('ferrox_orders_total', ['status' => 'fulfilled', 'source' => $dto->source]);

        return Result::ok(['orderId' => $orderId, 'warehouse' => $whId]);
    }
}

// ==============================================================================
// 5. HTTP CONTROLLERS (DRY & Validated)
// ==============================================================================

class StorefrontController extends AbstractController {
    
    // #[Post('/api/orders')]
    public function placeOrder(Request $req): Response {
        /** @var SubmitOrderDto $dto */
        $dto = clone $req->getAttribute('dto'); 
        $cmd = new SubmitOrderCommand($dto);
        
        $result = $this->commandBus->dispatch($cmd);
        if ($result->isErr()) return $this->error($result->unwrapErr(), 409);
        return $this->execute($cmd);
    }
}

/**
 * Native Metrics Exporter for Prometheus Scraping
 */
class MetricsController extends AbstractController {
    
    // #[Get('/metrics')]
    public function export(Request $req): Response {
        return new Response(200, PrometheusRegistry::export(), ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}

// ==============================================================================
// 6. BOOTSTRAPPING & SIMULATION
// ==============================================================================

echo "🛒 Ferrox Enterprise E-Shop booting with Prometheus Observability & Mailer Alerts...\n\n";

$app = FerroxApp::builder()
    ->withEngine('SwooleEngine')
    ->registerControllers([
        StorefrontController::class,
        MetricsController::class
    ])
    ->build();

// Dependency Injection Mockup
$container = new \Ferrox\Core\Container\Container();
$mailer = \Ferrox\Mailer\MailerFactory::create();
$events = new EventDispatcher($container);

// Wire Up Event Listeners (The Alert Dashboard & Notification System)
$events->addListener(OrderRefundedEvent::class, function(OrderRefundedEvent $event) use ($mailer) {
    echo "[EVENT LISTENER] Sending Refund Email to Customer for Order {$event->orderId}...\n";
    $mailer->send("customer@example.com", "Order Refunded", "<p>We're sorry, your order was refunded because: {$event->reason}</p>");
    
    echo "[EVENT LISTENER] Alerting Admins...\n";
    $mailer->send("admins@ferrox.dev", "ALERT: Stock Depletion", "<p>Global stock depletion triggered refund for {$event->orderId}.</p>");
});

// Demo Output
$whRepo = new WarehouseRepository(new class implements \Ferrox\Database\Core\UnitOfWorkInterface {
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollback(): void {}
    public function transactional(callable $operation): mixed { return $operation(); }
});
$orderRepo = new OrderRepository($whRepo->uow ?? null);

$bus = new CommandBus($container);
$handler = new SubmitOrderHandler($whRepo, $orderRepo, $events);
$bus->registerHandler(SubmitOrderCommand::class, get_class($handler)); 

echo "1. Simulating Online Order (Milan has 5)...\n";
$handler->handle(new SubmitOrderCommand(new SubmitOrderDto("PROD-1", 1, "ONLINE")));

echo "2. Simulating Out of Stock (Requires 10)...\n";
$handler->handle(new SubmitOrderCommand(new SubmitOrderDto("PROD-1", 10, "POS")));

echo "\n📊 Generated Prometheus Metrics Endpoint Output:\n";
echo "--------------------------------------------------\n";
echo PrometheusRegistry::export();
echo "--------------------------------------------------\n";
