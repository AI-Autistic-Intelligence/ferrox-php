<?php
namespace Ferrox\Scheduler;

/**
 * Enterprise Task Scheduler for Ferrox.
 * Allows defining recurring jobs directly in PHP syntax.
 */
class Scheduler
{
    /** @var Task[] */
    private array $tasks = [];

    public function schedule(string $cronExpression, callable $callback): void
    {
        $this->tasks[] = new Task($cronExpression, $callback);
    }

    /**
     * Called every minute by the server (e.g. Swoole Timer or Crontab)
     */
    public function runDueTasks(): void
    {
        $now = new \DateTimeImmutable();
        foreach ($this->tasks as $task) {
            if ($task->isDue($now)) {
                try {
                    // Fire asynchronously via Swoole Coroutine if available
                    if (extension_loaded('swoole')) {
                        \Swoole\Coroutine::create(fn() => $task->run());
                    } else {
                        $task->run();
                    }
                } catch (\Throwable $e) {
                    error_log("[SCHEDULER] Task failed: " . $e->getMessage());
                }
            }
        }
    }
}
