<?php
namespace Ferrox\Rpc;

/**
 * Interface for fast internal microservice-to-microservice communication.
 */
interface RpcClientInterface
{
    /**
     * Call an internal microservice method.
     */
    public function call(string $serviceName, string $endpoint, array $payload): array;
}
