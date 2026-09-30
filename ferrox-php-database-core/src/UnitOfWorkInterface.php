<?php
namespace Ferrox\Database\Core;

interface UnitOfWorkInterface
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
    
    /**
     * Executes the given callable inside a transaction.
     * Automatically commits on success, and rolls back on Exception.
     */
    public function transactional(callable $operation): mixed;
}
