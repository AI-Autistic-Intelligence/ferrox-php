<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

use Ferrox\Observability\Metrics\PrometheusRegistry;

/**
 * Worker orchestrating the FrankenPHP/Swoole saga fulfillment lifecycle
 * for trades received from Java Loom Ledger via topic 'orders.settled.v1'.
 */
final class SettledOrderSagaWorker
{
    private RedisRedlockInterface $redlock;
    private ReserveInventoryStep $inventoryStep;
    private OutboxCommitStep $outboxStep;
    private DispatchFulfillmentStep $dispatchStep;

    /** @var array<int, array<string, mixed>> */
    private array $compensationEventsEmitted = [];
    private int $totalProcessed = 0;
    private int $successCount = 0;
    private int $compensationCount = 0;

    public function __construct(
        ?RedisRedlockInterface $redlock = null,
        ?ReserveInventoryStep $inventoryStep = null,
        ?OutboxCommitStep $outboxStep = null,
        ?DispatchFulfillmentStep $dispatchStep = null
    ) {
        $this->redlock = $redlock ?? new RedisRedlock();
        $this->inventoryStep = $inventoryStep ?? new ReserveInventoryStep($this->redlock);
        $this->outboxStep = $outboxStep ?? new OutboxCommitStep();
        $this->dispatchStep = $dispatchStep ?? new DispatchFulfillmentStep();
    }

    /**
     * Consumes an orders.settled.v1 payload, coordinating the OrderFulfillmentSaga.
     *
     * @param array<string, mixed> $settledEvent
     */
    public function handleSettledOrder(array $settledEvent): SagaExecutionResult
    {
        $this->totalProcessed++;

        // Extract data payload from CloudEvent or raw format
        $data = $settledEvent['data'] ?? $settledEvent;
        $tradeId = (string) ($data['trade_id'] ?? 'trade-' . uniqid());
        $symbol = (string) ($data['symbol'] ?? 'BTC/USD');
        $qty = (float) ($data['quantity'] ?? 1.0);
        $price = (float) ($data['price'] ?? 65000.0);
        $buyerId = (string) ($data['buyer_id'] ?? 'buyer-alpha');
        $sellerId = (string) ($data['seller_id'] ?? 'seller-beta');

        $saga = new OrderFulfillmentSaga();
        $saga->addStep($this->inventoryStep)
             ->addStep($this->outboxStep)
             ->addStep($this->dispatchStep);

        $initialContext = [
            'trade_id' => $tradeId,
            'order_id' => 'ord-' . substr(md5($tradeId), 0, 10),
            'symbol' => $symbol,
            'quantity' => $qty,
            'price' => $price,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'simulate_risk_failure' => $data['simulate_risk_failure'] ?? false,
        ];

        $result = $saga->execute($initialContext);

        if ($result->isSuccess) {
            $this->successCount++;
            // Release the Redlock once saga successfully finishes
            if (!empty($result->context['redlock_resource']) && !empty($result->context['redlock_token'])) {
                $this->redlock->release($result->context['redlock_resource'], $result->context['redlock_token']);
            }

            PrometheusRegistry::increment('ferrox_sagas_completed_total', [
                'status' => 'SUCCESS',
                'symbol' => $symbol,
            ]);
        } else {
            $this->compensationCount++;
            // Publish compensation CloudEvent to topic 'saga.compensations.v1'
            $compensationEvent = [
                'specversion' => '1.0',
                'id' => 'comp-' . uniqid(),
                'type' => 'dev.ferrox.saga.compensations.v1',
                'source' => 'ferrox-php-frankenphp-saga',
                'time' => gmdate('Y-m-d\TH:i:s\Z'),
                'datacontenttype' => 'application/json',
                'data' => [
                    'trade_id' => $tradeId,
                    'order_id' => $result->context['order_id'] ?? null,
                    'error_message' => $result->errorMessage,
                    'compensated_steps' => $result->compensatedSteps,
                    'status' => 'COMPENSATED',
                ],
            ];

            $this->compensationEventsEmitted[] = $compensationEvent;

            PrometheusRegistry::increment('ferrox_sagas_compensated_total', [
                'status' => 'COMPENSATED',
                'symbol' => $symbol,
            ]);
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCompensationEventsEmitted(): array
    {
        return $this->compensationEventsEmitted;
    }

    /**
     * @return array<string, int|float>
     */
    public function getMetrics(): array
    {
        return [
            'total_processed' => $this->totalProcessed,
            'success_count' => $this->successCount,
            'compensation_count' => $this->compensationCount,
            'active_redlocks' => 0,
        ];
    }
}
