<?php
namespace Ferrox\Broadcasting\Adapters;

use Ferrox\Broadcasting\BroadcasterInterface;

/**
 * Native Swoole WebSocket Broadcaster.
 * Avoids the need for external services like Pusher or Soketi by pushing
 * directly through the Swoole HTTP/WS Server.
 */
class SwooleBroadcasterAdapter implements BroadcasterInterface
{
    private \Swoole\WebSocket\Server $server;

    public function __construct(\Swoole\WebSocket\Server $server)
    {
        $this->server = $server;
    }

    public function broadcast(string $channel, string $event, array $payload): void
    {
        $message = json_encode([
            'channel' => $channel,
            'event'   => $event,
            'data'    => $payload
        ]);

        // In a real implementation, we track which fd (file descriptors) are subscribed to which channel via Redis.
        // For simplicity, we broadcast to all active connections.
        foreach ($this->server->connections as $fd) {
            if ($this->server->isEstablished($fd)) {
                $this->server->push($fd, $message);
            }
        }
        
        error_log("[BROADCASTER] Pushed '{$event}' to channel '{$channel}' via Swoole WebSockets.");
    }
}
