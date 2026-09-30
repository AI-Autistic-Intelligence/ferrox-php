<?php
namespace Ferrox\Queue;

/**
 * Universal Queue Abstraction for processing Outbox Events asynchronusly.
 */
interface QueueInterface
{
    /**
     * Push a serialized job/event to the queue.
     */
    public function push(string $queueName, string $payload, int $delaySeconds = 0): void;

    /**
     * Consume jobs from the queue via a worker loop.
     */
    public function consume(string $queueName, callable $handler): void;
}
