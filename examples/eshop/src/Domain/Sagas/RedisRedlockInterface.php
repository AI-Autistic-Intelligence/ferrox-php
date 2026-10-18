<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

interface RedisRedlockInterface
{
    /**
     * Attempts to acquire a distributed lock.
     * @return string|null Lock token if acquired, null if already locked.
     */
    public function acquire(string $resource, int $ttlMs = 5000): ?string;

    /**
     * Releases a distributed lock using its token.
     */
    public function release(string $resource, string $token): bool;

    /**
     * Checks if a resource is currently locked.
     */
    public function isLocked(string $resource): bool;
}
