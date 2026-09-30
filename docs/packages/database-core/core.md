---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-database-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-database-core/src/AbstractRepository.php`

#### Class / Interface: `AbstractRepository`
The `AbstractRepository` is responsible for enterprise-grade execution of operations within `ferrox-php-database-core/src/AbstractRepository.php`.

- **`__construct(protected UnitOfWorkInterface $uow) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`transaction(callable $operation) : mixed`**
  - Finds an entity by its primary key. Integrates with Singleflight to prevent cache stampedes on hot records. / abstract public function findById(string $id): ?array; /** Standardized safe execution wrapped in a transaction.

- **`paginateQuery(string $sql, \Ferrox\Utils\Pagination\PageRequest $request) : \Ferrox\Utils\Pagination\PageResult`**
  - Executes a paginated query, returning a standardized PageResult.

### Path: `ferrox-php-database-core/src/RepositoryInterface.php`

#### Class / Interface: `RepositoryInterface`
The `RepositoryInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-database-core/src/RepositoryInterface.php`.

- **`findById(PublicId|int|string $id) : ?object`**
  - Queries the persistence layer securely to retrieve entities matching the `findById` criteria, utilizing Singleflight to prevent cache stampedes.

- **`findAll(PageRequest $request) : PageResult`**
  - Queries the persistence layer securely to retrieve entities matching the `findAll` criteria, utilizing Singleflight to prevent cache stampedes.

- **`save(object $entity) : void`**
  - Persists the domain entity to the database strictly within a transactional Unit of Work boundary.

- **`delete(object $entity) : void`**
  - Safely removes the entity from the database or applies an audited soft-delete mechanism.

### Path: `ferrox-php-database-core/src/UnitOfWorkInterface.php`

#### Class / Interface: `UnitOfWorkInterface`
The `UnitOfWorkInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-database-core/src/UnitOfWorkInterface.php`.

- **`beginTransaction() : void`**
  - Executes the `beginTransaction` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`commit() : void`**
  - Executes the `commit` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`rollback() : void`**
  - Executes the `rollback` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`transactional(callable $operation) : mixed`**
  - Executes the given callable inside a transaction. Automatically commits on success, and rolls back on Exception.

