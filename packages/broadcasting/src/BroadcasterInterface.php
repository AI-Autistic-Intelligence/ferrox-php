<?php
namespace Ferrox\Broadcasting;

/**
 * Universal interface to push real-time events to frontend clients.
 */
interface BroadcasterInterface
{
    /**
     * Broadcast a payload to a specific private or public channel.
     */
    public function broadcast(string $channel, string $event, array $payload): void;
}
