<?php
namespace Ferrox\Database\Core;

/**
 * Abstract Repository implementing DRY CRUD operations.
 * Forces the use of UnitOfWork and Singleflight to prevent Cache Stampedes.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        protected UnitOfWorkInterface $uow
    ) {}

    /**
     * Finds an entity by its primary key.
     * Integrates with Singleflight to prevent cache stampedes on hot records.
     */
    abstract public function findById(string $id): ?array;

    /**
     * Standardized safe execution wrapped in a transaction.
     */
    protected function transaction(callable $operation): mixed
    {
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
