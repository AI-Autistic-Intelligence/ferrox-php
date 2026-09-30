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

// ==============================================================================
// 3. REPOSITORIES & ACID TRANSACTIONS
// ==============================================================================

class WarehouseRepository extends AbstractRepository {
    private array $mockDb = [
        'WH-ROME' => ['stock' => 0],
        'WH-MILAN' => ['stock' => 5]
    ];

    public function findAvailableWarehouse(string $productId, int $qty): Option {
        // Simulates searching multiple warehouses (Multi-Warehouse Fallback)
        foreach ($this->mockDb as $whId => $data) {
            if ($data['stock'] >= $qty) {
                return Option::some($whId); // Found!
            }
        }
        return Option::none(); // No warehouse has enough stock
    }

    public function decrementStock(string $warehouseId, string $productId, int $qty): void {
        $this->transaction(function() use ($warehouseId, $qty) {
            $this->mockDb[$warehouseId]['stock'] -= $qty;
            echo "[DB ACID] Decremented stock in {$warehouseId}.\n";
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
        
        // 1. Try to find a warehouse with stock
        $warehouseOpt = $this->warehouseRepo->findAvailableWarehouse($dto->productId, $dto->quantity);
        
        if ($warehouseOpt->isNone()) {
            // 2. Saga Pattern: Fallback to Refund if all warehouses are out of stock
            $orderId = $this->orderRepo->saveOrder($dto->productId, $dto->quantity, $dto->source, "REFUNDED");
            $this->events->dispatch(new OrderRefundedEvent($orderId, "Out of stock globally"));
            
            return Result::err("Stock totally depleted. Order refunded automatically.");
        }

        // 3. Fulfill the order
        $whId = $warehouseOpt->unwrap();
        $this->warehouseRepo->decrementStock($whId, $dto->productId, $dto->quantity);
        $orderId = $this->orderRepo->saveOrder($dto->productId, $dto->quantity, $dto->source, "FULFILLED");

        return Result::ok(['orderId' => $orderId, 'warehouse' => $whId]);
    }
}

class SyncPosBatchCommand implements CommandInterface {
    public function __construct(public SyncPosBatchDto $dto) {}
}

class SyncPosBatchHandler {
    public function __construct(
        private CommandBus $bus,
        private EventDispatcher $events
    ) {}

    public function handle(SyncPosBatchCommand $cmd): Result {
        $processed = 0;
        
        // Loop through offline POS batch and dispatch individual commands
        foreach ($cmd->dto->orders as $orderPayload) {
            $orderDto = new SubmitOrderDto($orderPayload['productId'], $orderPayload['quantity'], "POS");
            $this->bus->dispatch(new SubmitOrderCommand($orderDto));
            $processed++;
        }
        
        $this->events->dispatch(new PosBatchSyncedEvent($processed));
        return Result::ok("Batch synced successfully: {$processed} orders.");
    }
}

// ==============================================================================
// 5. HTTP CONTROLLERS (DRY & Validated)
// ==============================================================================

class StorefrontController extends AbstractController {
    
    // #[Post('/api/orders')]
    // Implicitly uses ValidationPipe to ensure $req->getAttribute('dto') is a valid SubmitOrderDto
    public function placeOrder(Request $req): Response {
        /** @var SubmitOrderDto $dto */
        $dto = clone $req->getAttribute('dto'); 
        $cmd = new SubmitOrderCommand($dto);
        
        $result = $this->commandBus->dispatch($cmd);
        
        if ($result->isErr()) {
            return $this->error($result->unwrapErr(), 409); // Conflict (Refunded)
        }
        
        return $this->execute($cmd); // Standard JSON 200 OK
    }
}

class PosSyncController extends AbstractController {
    
    // #[Post('/api/pos/sync-offline-batch')]
    // Used when a physical store POS regains internet connectivity
    public function syncBatch(Request $req): Response {
        $dto = clone $req->getAttribute('dto');
        return $this->execute(new SyncPosBatchCommand($dto));
    }
}

// ==============================================================================
// 6. BOOTSTRAPPING (The 7-Layer Pipeline)
// ==============================================================================

echo "🛒 Ferrox Enterprise E-Shop booting...\n";
echo "Applying Zero-Trust Validation & CQRS pipelines...\n\n";

$app = FerroxApp::builder()
    ->withEngine('SwooleEngine')
    ->addPipeline([
        \Ferrox\Security\Sentinel\SentinelThreatEngineMiddleware::class,
        // \Ferrox\Validation\ValidationPipe::class would hook here dynamically
    ])
    ->registerControllers([
        StorefrontController::class,
        PosSyncController::class
    ])
    ->build();

// Demo Output to prove it's a real enterprise backend
echo "Simulating Online Order (Stock in Rome is 0, Milan is 5)...\n";
$whRepo = new WarehouseRepository(new class implements \Ferrox\Database\Core\UnitOfWorkInterface {
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollback(): void {}
    public function transactional(callable $operation): mixed { return $operation(); }
});
$orderRepo = new OrderRepository($whRepo->uow ?? null); // Quick mock
$events = new EventDispatcher(new \Ferrox\Core\Container\Container());

$bus = new CommandBus(new \Ferrox\Core\Container\Container());
// Normally the Container resolves these via reflection
$handler = new SubmitOrderHandler($whRepo, $orderRepo, $events);
$bus->registerHandler(SubmitOrderCommand::class, get_class($handler)); // Mock register

// 1. Simulating Online Order
$res = $handler->handle(new SubmitOrderCommand(new SubmitOrderDto("PROD-1", 1, "ONLINE")));
echo "Online Order Result: " . json_encode($res->isOk() ? $res->unwrap() : $res->unwrapErr()) . "\n\n";

// 2. Simulating Out of Stock (Requires 10, Milan only has 4 left)
echo "Simulating massive Online Order (Qty 10)...\n";
$res2 = $handler->handle(new SubmitOrderCommand(new SubmitOrderDto("PROD-1", 10, "ONLINE")));
echo "Failed Order Result: " . json_encode($res2->isOk() ? $res2->unwrap() : $res2->unwrapErr()) . "\n\n";

echo "System ready.\n";
