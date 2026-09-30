<?php
namespace Ferrox\Gateway;

use RuntimeException;
use Swoole\Coroutine\Http\Client;

/**
 * Enterprise API Gateway / Edge Router.
 * Routes traffic to internal microservices after passing Edge Security.
 */
class GatewayRouter
{
    private array $serviceRegistry = [];

    public function registerService(string $prefix, string $internalUri): self
    {
        $this->serviceRegistry[$prefix] = rtrim($internalUri, '/');
        return $this;
    }

    /**
     * Proxies the request to the underlying microservice using Swoole HTTP Client.
     */
    public function proxy(string $uri, string $method, array $headers, string $body): array
    {
        foreach ($this->serviceRegistry as $prefix => $target) {
            if (str_starts_with($uri, $prefix)) {
                $servicePath = substr($uri, strlen($prefix));
                $parsedUrl = parse_url($target);
                
                // High-performance Coroutine HTTP Client
                $client = new Client($parsedUrl['host'], $parsedUrl['port'] ?? 80);
                $client->setMethod($method);
                $client->setHeaders($headers);
                $client->setData($body);
                
                $client->execute($servicePath);
                
                $status = $client->getStatusCode();
                $responseBody = $client->getBody();
                $client->close();
                
                return ['status' => $status, 'body' => $responseBody];
            }
        }

        throw new RuntimeException("No microservice mapped for URI: {$uri}");
    }
}
