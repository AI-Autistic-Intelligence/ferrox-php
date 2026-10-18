<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

use Ferrox\Utils\Types\Result;

/**
 * Step 2: Commits transaction into transactional Outbox log.
 * On compensation, marks the order compensated and records a compensation event
 * for publication to 'saga.compensations.v1'.
 */
final class OutboxCommitStep implements SagaStepInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $outboxLog = [];

    public function getName(): string
    {
        return 'OutboxCommit';
    }

    public function execute(array &$context): Result
    {
        $orderId = (string) ($context['order_id'] ?? 'ord-' . uniqid());
        $tradeId = (string) ($context['trade_id'] ?? 'trade-unknown');

        $outboxRecord = [
            'id' => count($this->outboxLog) + 1,
            'event' => 'order.fulfillment.started',
            'order_id' => $orderId,
            'trade_id' => $tradeId,
            'symbol' => $context['symbol'] ?? 'BTC/USD',
            'quantity' => $context['quantity'] ?? 0.0,
            'status' => 'PENDING_DISPATCH',
            'created_at' => microtime(true),
        ];

        $this->outboxLog[] = $outboxRecord;
        $context['outbox_record_id'] = $outboxRecord['id'];
        $context['saga_status'] = 'OUTBOX_COMMITTED';

        return Result::ok(true);
    }

    public function compensate(array &$context): Result
    {
        $orderId = (string) ($context['order_id'] ?? 'ord-unknown');
        $tradeId = (string) ($context['trade_id'] ?? 'trade-unknown');

        $compensationRecord = [
            'id' => count($this->outboxLog) + 1,
            'event' => 'order.fulfillment.compensated',
            'order_id' => $orderId,
            'trade_id' => $tradeId,
            'reason' => $context['compensation_reason'] ?? 'Downstream saga step failed',
            'status' => 'COMPENSATED',
            'created_at' => microtime(true),
        ];

        $this->outboxLog[] = $compensationRecord;
        $context['saga_status'] = 'COMPENSATED';
        $context['compensation_recorded'] = true;

        return Result::ok(true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOutboxLog(): array
    {
        return $this->outboxLog;
    }
}
