<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Persistence;

use Ferrox\Database\Core\UnitOfWorkInterface;
use PDO;
use RuntimeException;
use Throwable;

final class PostgreSqlUnitOfWork implements UnitOfWorkInterface
{
    private ?PDO $pdo = null;
    private int $transactionDepth = 0;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    public function beginTransaction(): void
    {
        if ($this->pdo !== null) {
            if ($this->transactionDepth === 0) {
                $this->pdo->beginTransaction();
            }
        }
        $this->transactionDepth++;
    }

    public function commit(): void
    {
        if ($this->transactionDepth <= 0) {
            throw new RuntimeException("No active transaction to commit.");
        }
        $this->transactionDepth--;
        if ($this->pdo !== null && $this->transactionDepth === 0) {
            $this->pdo->commit();
        }
    }

    public function rollback(): void
    {
        if ($this->transactionDepth > 0) {
            $this->transactionDepth = 0;
            if ($this->pdo !== null && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    public function transactional(callable $operation): mixed
    {
        $this->beginTransaction();
        try {
            $result = $operation($this);
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
