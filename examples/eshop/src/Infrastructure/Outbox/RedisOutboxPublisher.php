<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Outbox;

use Ferrox\Events\DomainEventInterface;
use Ferrox\Observability\Metrics\PrometheusRegistry;

final class RedisOutboxPublisher
{
    /** @var OutboxMessage[] */
    private array $pendingMessages = [];
    /** @var OutboxMessage[] */
    private array $publishedHistory = [];
    private mixed $redis = null;

    public function __construct(mixed $redis = null)
    {
        $this->redis = $redis;
    }

    /**
     * Stash event in outbox transactionally.
     */
    public function record(DomainEventInterface $event): OutboxMessage
    {
        $msg = new OutboxMessage($event);
        $this->pendingMessages[] = $msg;
        return $msg;
    }

    /**
     * Flushes and delivers pending messages to the Redis stream/list.
     * @return int Count of published messages
     */
    public function flush(): int
    {
        $count = 0;
        while (!empty($this->pendingMessages)) {
            $msg = array_shift($this->pendingMessages);

            if ($this->redis !== null && method_exists($this->redis, 'lpush')) {
                try {
                    $this->redis->lpush('ferrox:eshop:events', $msg->toJson());
                } catch (\Throwable $e) {
                    error_log("[REDIS OUTBOX ERROR] Failed pushing message {$msg->id}: " . $e->getMessage());
                    // Push back for retry
                    array_unshift($this->pendingMessages, $msg);
                    break;
                }
            }

            $msg->markPublished();
            $this->publishedHistory[] = $msg;
            $count++;

            PrometheusRegistry::increment('ferrox_outbox_events_published_total', [
                'event_name' => $msg->eventName
            ]);
        }

        return $count;
    }

    /**
     * @return OutboxMessage[]
     */
    public function getPublishedHistory(): array
    {
        return $this->publishedHistory;
    }

    public function getPendingCount(): int
    {
        return count($this->pendingMessages);
    }
}
