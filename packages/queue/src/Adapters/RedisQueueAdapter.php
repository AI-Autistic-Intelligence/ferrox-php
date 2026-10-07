<?php
namespace Ferrox\Queue\Adapters;

use Ferrox\Queue\QueueInterface;
use Redis;

/**
 * High-performance queue driver using Redis Lists and swoole coroutines.
 */
class RedisQueueAdapter implements QueueInterface
{
    private Redis $redis;

    public function __construct(string $host = '127.0.0.1', int $port = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }

    public function push(string $queueName, string $payload, int $delaySeconds = 0): void
    {
        if ($delaySeconds > 0) {
            // Use Sorted Sets for delayed jobs
            $this->redis->zAdd("queue:delayed:{$queueName}", time() + $delaySeconds, $payload);
        } else {
            // Use Standard Lists for immediate jobs
            $this->redis->lPush("queue:{$queueName}", $payload);
        }
    }

    public function consume(string $queueName, callable $handler): void
    {
        echo "[QUEUE WORKER] Listening on Redis queue '{$queueName}'...\n";
        
        while (true) {
            // Blocking pop (waits efficiently without CPU spin)
            $job = $this->redis->brPop(["queue:{$queueName}"], 5);
            
            if ($job) {
                try {
                    $handler($job[1]);
                } catch (\Throwable $e) {
                    error_log("[QUEUE ERROR] Failed to process job: " . $e->getMessage());
                    // In a real system, push to a Dead Letter Queue (DLQ)
                    $this->redis->lPush("queue:{$queueName}:dlq", $job[1]);
                }
            }
        }
    }
}
