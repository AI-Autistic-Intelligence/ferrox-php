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
     * The others will wait via Swoole Coroutine Channels and receive the same result,
     * dramatically reducing database or API load.
     * 
     * @param string $key The unique identifier for the requested resource (e.g. "product_inventory_123").
     * @param Closure $callback The expensive operation to execute (e.g. DB Query).
     * @return mixed The result of the callback.
     */
    public function work(string $key, Closure $callback): mixed
    {
        if (isset($this->flights[$key])) {
            // A request is already in flight. Suspend this coroutine and wait for the result.
            // If the channel is already closed (result retrieved), it will return false.
            if ($this->flights[$key] instanceof \Swoole\Coroutine\Channel) {
                $result = $this->flights[$key]->pop();
                return $result;
            }
            // If it's not a channel, the result is already cached in the flight array temporarily
            return $this->flights[$key];
        }

        // Mark flight as in-progress by creating a waiting channel
        $channel = new \Swoole\Coroutine\Channel(1);
        $this->flights[$key] = $channel;

        try {
            $result = $callback();
            
            // Store the result temporarily for immediate subsequent readers in this tick
            $this->flights[$key] = $result;
            
            // Wake up all waiting coroutines by pushing the result to the channel
            // Note: in a real singleflight, we might need to loop through waiters
            // but Swoole channel push will wake up the first waiter.
            // A better approach is to close the channel after pushing, but Swoole channels
            // drain sequentially. For full broadcast, we loop based on stats.
            $waitCount = $channel->stats()['consumer_num'];
            for ($i = 0; $i < $waitCount; $i++) {
                $channel->push($result);
            }
            
            return $result;
        } finally {
            // Clean up the flight so subsequent new requests can re-fetch if needed.
            // We use a deferred timer to allow current event loop tick to finish reading
            \Swoole\Event::defer(function() use ($key) {
                unset($this->flights[$key]);
            });
        }
    }
}
