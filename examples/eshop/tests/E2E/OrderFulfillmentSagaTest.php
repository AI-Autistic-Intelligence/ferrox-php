<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Tests\E2E;

use Ferrox\Core\Container\Container;
use Ferrox\Events\EventDispatcher;
use Ferrox\Examples\Eshop\Application\Commands\SubmitOrderCommand;
use Ferrox\Examples\Eshop\Application\Commands\SyncPosBatchCommand;
use Ferrox\Examples\Eshop\Application\DTOs\SubmitOrderDto;
use Ferrox\Examples\Eshop\Application\DTOs\SyncPosBatchDto;
use Ferrox\Examples\Eshop\Application\Handlers\SubmitOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\SyncPosBatchHandler;
use Ferrox\Examples\Eshop\Domain\Model\OrderStatus;
use Ferrox\Examples\Eshop\Infrastructure\Outbox\RedisOutboxPublisher;
use Ferrox\Examples\Eshop\Infrastructure\Payment\SimulatedPaymentGateway;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoOrderRepository;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoWarehouseRepository;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PostgreSqlUnitOfWork;
use PHPUnit\Framework\TestCase;

final class OrderFulfillmentSagaTest extends TestCase
{
    private PdoWarehouseRepository $warehouseRepo;
    private PdoOrderRepository $orderRepo;
    private SimulatedPaymentGateway $paymentGateway;
    private EventDispatcher $events;
    private RedisOutboxPublisher $outbox;
    private SubmitOrderHandler $submitHandler;
    private SyncPosBatchHandler $batchHandler;

    protected function setUp(): void
    {
        $container = new Container();
        $uow = new PostgreSqlUnitOfWork(null);
        $this->warehouseRepo = new PdoWarehouseRepository($uow);
        $this->orderRepo = new PdoOrderRepository($uow);
        $this->paymentGateway = new SimulatedPaymentGateway();
        $this->events = new EventDispatcher($container);
        $this->outbox = new RedisOutboxPublisher();

        $this->submitHandler = new SubmitOrderHandler(
            $this->warehouseRepo,
            $this->orderRepo,
            $this->paymentGateway,
            $this->events,
            $this->outbox
        );

        $this->batchHandler = new SyncPosBatchHandler($this->submitHandler);
    }

    public function testMultiWarehouseFallbackSuccess(): void
    {
        // PROD-1 has stock 0 in Rome, 8 in Milan.
        // Requesting 2 units should skip Rome and fulfill from Milan.
        $dto = new SubmitOrderDto(
            productId: 'PROD-1',
            quantity: 2,
            customerId: 'CUST-E2E-1',
            source: 'ONLINE',
            unitPriceCents: 2999
        );

        $result = $this->submitHandler->handle(new SubmitOrderCommand($dto));

        $this->assertTrue($result->isOk());
        $response = $result->unwrap();
        $this->assertSame(OrderStatus::FULFILLED->value, $response->status);
        $this->assertSame('WH-MILAN', $response->warehouseId);
        $this->assertNotNull($response->paymentTransactionId);

        // Verify outbox has recorded the OrderFulfilledEvent
        $this->assertNotEmpty($this->outbox->getPublishedHistory());
        $lastEvent = end($this->outbox->getPublishedHistory());
        $this->assertSame('OrderFulfilledEvent', $lastEvent->eventName);
    }

    public function testGlobalOutOfStockTriggersRefundCompensation(): void
    {
        // Total PROD-1 stock across all warehouses is 8.
        // Requesting 99 units should fail and trigger refund.
        $dto = new SubmitOrderDto(
            productId: 'PROD-1',
            quantity: 99,
            customerId: 'CUST-E2E-2',
            source: 'ONLINE',
            unitPriceCents: 2999
        );

        $result = $this->submitHandler->handle(new SubmitOrderCommand($dto));

        $this->assertTrue($result->isErr());
        $this->assertStringContainsString('Stock totally depleted', $result->unwrapErr());

        // Verify outbox recorded OrderRefundedEvent
        $refundEvent = null;
        foreach ($this->outbox->getPublishedHistory() as $msg) {
            if ($msg->eventName === 'OrderRefundedEvent') {
                $refundEvent = $msg;
                break;
            }
        }
        $this->assertNotNull($refundEvent);
    }

    public function testOfflinePosBatchSynchronization(): void
    {
        $dto = new SyncPosBatchDto(
            orders: [
                ['productId' => 'PROD-2', 'quantity' => 2, 'customerId' => 'POS-WALKIN-1', 'unitPriceCents' => 999],
                ['productId' => 'PROD-2', 'quantity' => 1, 'customerId' => 'POS-WALKIN-2', 'unitPriceCents' => 999],
            ],
            posTerminalId: 'POS-TEST-TERMINAL'
        );

        $result = $this->batchHandler->handle(new SyncPosBatchCommand($dto));

        $this->assertTrue($result->isOk());
        $data = $result->unwrap();
        $this->assertSame(2, $data['successful']);
        $this->assertSame(0, $data['failed']);
    }
}
