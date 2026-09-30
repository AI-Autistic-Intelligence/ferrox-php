<?php
namespace Ferrox\Database\Core\Connection;

/**
 * Manages database connections with Master-Replica routing.
 * Ensures "Read-Your-Writes" consistency to prevent replication lag issues.
 */
class ReplicaAwareManager
{
    private bool $forceMasterForReads = false;

    public function __construct(
        private mixed $masterConnection,
        private array $replicaConnections
    ) {}

    /**
     * Gets the connection for a Write operation (INSERT, UPDATE, DELETE).
     * Always returns the Master database and flags the context to force
     * subsequent reads to also use the Master to avoid replication lag.
     */
    public function getWriteConnection(): mixed
    {
        $this->forceMasterForReads = true;
        return $this->masterConnection;
    }

    /**
     * Gets the connection for a Read operation (SELECT).
     * If a write occurred previously in this lifecycle, it forces the Master.
     * Otherwise, it load-balances across regional Replicas.
     */
    public function getReadConnection(): mixed
    {
        if ($this->forceMasterForReads || empty($this->replicaConnections)) {
            return $this->masterConnection;
        }

        // Simple Random Load Balancing for Replicas
        $index = array_rand($this->replicaConnections);
        return $this->replicaConnections[$index];
    }

    /**
     * Explicitly forces all subsequent operations to use the Master connection.
     */
    public function forceMaster(): void
    {
        $this->forceMasterForReads = true;
    }
    
    /**
     * Resets the routing state (useful for workers that handle multiple requests).
     */
    public function reset(): void
    {
        $this->forceMasterForReads = false;
    }
}
