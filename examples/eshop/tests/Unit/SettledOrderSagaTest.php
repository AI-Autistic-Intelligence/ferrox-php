<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Tests\Unit;

use Ferrox\Examples\Eshop\Domain\Sagas\DispatchFulfillmentStep;
use Ferrox\Examples\Eshop\Domain\Sagas\OutboxCommitStep;
use Ferrox\Examples\Eshop\Domain\Sagas\RedisRedlock;
use Ferrox\Examples\Eshop\Domain\Sagas\ReserveInventoryStep;
use Ferrox\Examples\Eshop\Domain\Sagas\SettledOrderSagaWorker;
use PHPUnit\Framework\TestCase;

final class SettledOrderSagaTest extends TestCase
{
    protected function setUp(): void
    {
        RedisRedlock::clearAll();
    }

    public function testSuccessfulSagaExecutionWithRedlockAndOutbox(): void
    {
        $redlock = new RedisRedlock();
        $inventoryStep = new ReserveInventoryStep($redlock, ['BTC/USD' => 10.0]);
        $outboxStep = new OutboxCommitStep();
        $dispatchStep = new DispatchFulfillmentStep();

        $worker = new SettledOrderSagaWorker($redlock, $inventoryStep, $outboxStep, $dispatchStep);

        $settledEvent = [
            'specversion' => '1.0',
            'type' => 'dev.ferrox.settlement.orders.settled.v1',
            'data' => [
                'trade_id' => 'trade-abc-123',
                'symbol' => 'BTC/USD',
                'quantity' => 2.5,
                'price' => 67000.0,
                'buyer_id' => 'buyer-alpha',
                'seller_id' => 'seller-beta',
            ],
        ];

        $result = $worker->handleSettledOrder($settledEvent);

        $this->assertTrue($result->isSuccess);
        $this->assertSame(['ReserveInventory', 'OutboxCommit', 'DispatchFulfillment'], $result->completedSteps);
        $this->assertEmpty($result->compensatedSteps);

        // Verify remaining inventory decremented: 10.0 - 2.5 = 7.5
        $this->assertEquals(7.5, $inventoryStep->getStock('BTC/USD'));

        // Verify outbox has committed the record
        $this->assertCount(1, $outboxStep->getOutboxLog());
        $this->assertSame('order.fulfillment.started', $outboxStep->getOutboxLog()[0]['event']);

        // Verify Redlock was released after successful execution
        $this->assertFalse($redlock->isLocked('lock:inventory:BTC_USD'));

        // Metrics verification
        $metrics = $worker->getMetrics();
        $this->assertSame(1, $metrics['total_processed']);
        $this->assertSame(1, $metrics['success_count']);
        $this->assertSame(0, $metrics['compensation_count']);
    }

    public function testCompensatoryRollbackOnInsufficientInventory(): void
    {
        $redlock = new RedisRedlock();
        $inventoryStep = new ReserveInventoryStep($redlock, ['BTC/USD' => 1.0]);
        $worker = new SettledOrderSagaWorker($redlock, $inventoryStep);

        $settledEvent = [
            'data' => [
                'trade_id' => 'trade-huge-volume',
                'symbol' => 'BTC/USD',
                'quantity' => 15.0, // Exceeds 1.0 available
                'price' => 67000.0,
            ],
        ];

        $result = $worker->handleSettledOrder($settledEvent);

        $this->assertFalse($result->isSuccess);
        $this->assertStringContainsString('INSUFFICIENT_STOCK', (string) $result->errorMessage);

        // Redlock must be released even on failure
        $this->assertFalse($redlock->isLocked('lock:inventory:BTC_USD'));

        // Compensation event emitted
        $compensations = $worker->getCompensationEventsEmitted();
        $this->assertCount(1, $compensations);
        $this->assertSame('dev.ferrox.saga.compensations.v1', $compensations[0]['type']);
    }

    public function testCompensatoryRollbackOnDownstreamRiskLimitFailure(): void
    {
        $redlock = new RedisRedlock();
        $inventoryStep = new ReserveInventoryStep($redlock, ['BTC/USD' => 20.0]);
        $outboxStep = new OutboxCommitStep();
        $dispatchStep = new DispatchFulfillmentStep(100_000.0); // Limit: $100k

        $worker = new SettledOrderSagaWorker($redlock, $inventoryStep, $outboxStep, $dispatchStep);

        // Trade notional: 2.0 * $68,000 = $136,000 > $100,000 limit
        $settledEvent = [
            'data' => [
                'trade_id' => 'trade-risk-breach',
                'symbol' => 'BTC/USD',
                'quantity' => 2.0,
                'price' => 68000.0,
            ],
        ];

        $result = $worker->handleSettledOrder($settledEvent);

        $this->assertFalse($result->isSuccess);
        $this->assertStringContainsString('RISK_LIMIT_EXCEEDED', (string) $result->errorMessage);

        // Completed steps before failure
        $this->assertSame(['ReserveInventory', 'OutboxCommit'], $result->completedSteps);

        // Compensated steps in reverse order
        $this->assertSame(['OutboxCommit', 'ReserveInventory'], $result->compensatedSteps);

        // Inventory must be fully restored to 20.0
        $this->assertEquals(20.0, $inventoryStep->getStock('BTC/USD'));

        // Redlock must be released
        $this->assertFalse($redlock->isLocked('lock:inventory:BTC_USD'));

        // Outbox log should record the compensation event
        $outbox = $outboxStep->getOutboxLog();
        $this->assertCount(2, $outbox);
        $this->assertSame('order.fulfillment.started', $outbox[0]['event']);
        $this->assertSame('order.fulfillment.compensated', $outbox[1]['event']);
    }

    public function testRedlockMutualExclusionPreventsRaceConditions(): void
    {
        $redlock = new RedisRedlock();
        $resource = 'lock:order:trade-race-001';

        // Worker 1 acquires lock
        $token1 = $redlock->acquire($resource, 5000);
        $this->assertNotNull($token1);
        $this->assertTrue($redlock->isLocked($resource));

        // Worker 2 attempts to acquire same lock simultaneously -> must be rejected
        $token2 = $redlock->acquire($resource, 5000);
        $this->assertNull($token2);

        // Worker 1 releases lock
        $released = $redlock->release($resource, $token1);
        $this->assertTrue($released);
        $this->assertFalse($redlock->isLocked($resource));

        // Worker 2 can now acquire the lock
        $token3 = $redlock->acquire($resource, 5000);
        $this->assertNotNull($token3);
        $redlock->release($resource, $token3);
    }
}
