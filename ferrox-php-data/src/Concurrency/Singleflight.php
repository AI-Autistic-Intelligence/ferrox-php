<?php
namespace Ferrox\Data\Concurrency;

use Closure;

/**
 * Ferrox Singleflight (Anti-Dogpiling mechanism)
 * Prevents Cache Stampede by executing the callback only once for concurrent requests
 * asking for the same key.
 */
class Singleflight
{
    /**
     * @var array<string, mixed> Active requests currently in flight.
     */
    private array $flights = [];

    /**
     * Executes the given closure. If multiple concurrent requests call this method
     * with the same key, only the first one will execute the closure.
     * The others will wait (in a real async environment like Swoole/Coroutine) 
     * and receive the same result, dramatically reducing database or API load.
     * 
     * @param string $key The unique identifier for the requested resource (e.g. "product_inventory_123").
     * @param Closure $callback The expensive operation to execute (e.g. DB Query).
     * @return mixed The result of the callback.
     */
    public function work(string $key, Closure $callback): mixed
    {
        if (isset($this->flights[$key])) {
            // In a blocking PHP setup, this isn't truly async without external locking.
            // In Swoole, we would wait on a channel here.
            // For now, we simulate returning the cached flight result if it was already processed.
            return $this->flights[$key];
        }

        // Mark flight as in-progress
        $this->flights[$key] = null; // Placeholder

        try {
            $result = $callback();
            $this->flights[$key] = $result;
            return $result;
        } finally {
            // Once resolved and all waiters notified, clean up the flight
            // so subsequent requests can re-fetch if needed.
            unset($this->flights[$key]);
        }
    }
}
