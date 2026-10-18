<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

/**
 * Distributed Redlock implementation providing mutual exclusion.
 * Prevents double-spend and race conditions in concurrent saga executions.
 */
final class RedisRedlock implements RedisRedlockInterface
{
    private mixed $redis;
    /** @var array<string, array{token: string, expires_at: float}> */
    private static array $inMemoryLocks = [];

    public function __construct(mixed $redis = null)
    {
        $this->redis = $redis;
    }

    public function acquire(string $resource, int $ttlMs = 5000): ?string
    {
        $token = bin2hex(random_bytes(16));
        $now = microtime(true);

        if ($this->redis !== null && method_exists($this->redis, 'set')) {
            // SET resource token NX PX ttlMs
            $acquired = $this->redis->set($resource, $token, ['nx', 'px' => $ttlMs]);
            return $acquired ? $token : null;
        }

        // Deterministic in-memory atomic emulation (for testing and zero-dep runtime)
        if (isset(self::$inMemoryLocks[$resource])) {
            if (self::$inMemoryLocks[$resource]['expires_at'] > $now) {
                return null; // Resource locked
            }
        }

        self::$inMemoryLocks[$resource] = [
            'token' => $token,
            'expires_at' => $now + ($ttlMs / 1000.0),
        ];

        return $token;
    }

    public function release(string $resource, string $token): bool
    {
        if ($this->redis !== null && method_exists($this->redis, 'eval')) {
            // Atomic check and delete Lua script
            $lua = 'if redis.call("get",KEYS[1]) == ARGV[1] then return redis.call("del",KEYS[1]) else return 0 end';
            return (bool) $this->redis->eval($lua, [$resource, $token], 1);
        }

        if (isset(self::$inMemoryLocks[$resource]) && self::$inMemoryLocks[$resource]['token'] === $token) {
            unset(self::$inMemoryLocks[$resource]);
            return true;
        }

        return false;
    }

    public function isLocked(string $resource): bool
    {
        $now = microtime(true);
        if ($this->redis !== null && method_exists($this->redis, 'exists')) {
            return (bool) $this->redis->exists($resource);
        }

        if (isset(self::$inMemoryLocks[$resource])) {
            if (self::$inMemoryLocks[$resource]['expires_at'] > $now) {
                return true;
            }
            unset(self::$inMemoryLocks[$resource]);
        }

        return false;
    }

    public static function clearAll(): void
    {
        self::$inMemoryLocks = [];
    }
}
