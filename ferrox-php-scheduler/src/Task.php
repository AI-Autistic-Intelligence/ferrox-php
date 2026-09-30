<?php
namespace Ferrox\Scheduler;

class Task
{
    private string $cronExpression;
    private $callback;

    public function __construct(string $cronExpression, callable $callback)
    {
        $this->cronExpression = $cronExpression;
        $this->callback = $callback;
    }

    public function isDue(\DateTimeImmutable $now): bool
    {
        // Dummy implementation of cron parsing for demonstration.
        // In a real scenario, we use a Cron Expression parser library like dragonmantank/cron-expression.
        // For Ferrox, we evaluate if the current minute/hour matches the string.
        return true; // Assume due for the mock
    }

    public function run(): void
    {
        call_user_func($this->callback);
    }
}
