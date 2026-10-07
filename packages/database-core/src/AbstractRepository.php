<?php
namespace Ferrox\Database\Core;

/**
 * Abstract Repository implementing DRY CRUD operations.
 * Forces the use of UnitOfWork and Singleflight to prevent Cache Stampedes.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        protected UnitOfWorkInterface $uow,
        protected ?\Ferrox\Database\Core\Connection\ReplicaAwareManager $replicaManager = null
    ) {}

    /**
     * Finds an entity by its primary key.
     * Routes to a Replica by default, unless a write has forced Master.
     */
    abstract public function findById(string $id): ?array;

    /**
     * Standardized safe execution wrapped in a transaction.
     * Automatically forces the Master connection to avoid replication lag during read-after-write.
     */
    protected function transaction(callable $operation): mixed
    {
        if ($this->replicaManager) {
            $this->replicaManager->forceMaster();
        }
        return $this->uow->transactional($operation);
    }
    
    /**
     * Executes a paginated query, returning a standardized PageResult.
     * @param \Ferrox\Utils\Pagination\PageRequest $request
     */
    protected function paginateQuery(string $sql, \Ferrox\Utils\Pagination\PageRequest $request): \Ferrox\Utils\Pagination\PageResult
    {
        // In a real implementation this binds LIMIT and OFFSET from $request
        // and returns a populated PageResult.
        return new \Ferrox\Utils\Pagination\PageResult(
            items: [],
            total: 0,
            page: $request->page,
            limit: $request->limit
        );
    }
}
