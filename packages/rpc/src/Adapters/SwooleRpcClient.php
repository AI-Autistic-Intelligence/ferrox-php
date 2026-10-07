<?php
namespace Ferrox\Rpc\Adapters;

use Ferrox\Rpc\RpcClientInterface;
use Swoole\Coroutine\Http\Client;
use RuntimeException;

/**
 * Swoole Coroutine based RPC Client.
 * Enables non-blocking, lightning-fast microservice communication via HTTP/2.
 */
class SwooleRpcClient implements RpcClientInterface
{
    private array $serviceRegistry;

    public function __construct(array $serviceRegistryConfig)
    {
        // Simple registry map. E.g. ['billing' => '10.0.1.15:8080']
        // In Kubernetes, this is naturally resolved by CoreDNS (e.g., 'billing.default.svc.cluster.local')
        $this->serviceRegistry = $serviceRegistryConfig;
    }

    public function call(string $serviceName, string $endpoint, array $payload): array
    {
        if (!isset($this->serviceRegistry[$serviceName])) {
            throw new RuntimeException("RPC Error: Service '{$serviceName}' not found in registry.");
        }

        $hostPort = explode(':', $this->serviceRegistry[$serviceName]);
        $host = $hostPort[0];
        $port = $hostPort[1] ?? 80;

        $client = new Client($host, $port);
        // Enable HTTP/2 for gRPC-like multiplexing performance
        $client->set(['open_http2_protocol' => true]);
        $client->setMethod('POST');
        $client->setHeaders(['Content-Type' => 'application/json']);
        $client->setData(json_encode($payload));

        $client->execute($endpoint);
        
        if ($client->getStatusCode() !== 200) {
            throw new RuntimeException("RPC Error: {$serviceName} returned status " . $client->getStatusCode());
        }

        $response = json_decode($client->getBody(), true);
        $client->close();
        
        return $response;
    }
}
