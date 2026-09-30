# FERROX-PHP-DATABASE-CORE

## 1. Overview (What does this do?)
The `ferrox-php-database-core` component is an essential part of the robust backend development framework, centered around the 7-Layer Onion Request Pipeline. It adapts the stringent, enterprise-grade conventions established by the original Ferrox ecosystem specifically for PHP 8.3+. 
Abstract Repository and Unit of Work interfaces for enterprise-grade transactional persistence.

## 2. Philosophy (Why does it exist?)
The overarching philosophy of `ferrox-php-database-core` is zero-trust security and maximum decoupling. It exists to solve the common issue of unmaintainable, tightly coupled backend monoliths in standard PHP applications. By enforcing strict boundaries, it prevents developers from taking shortcuts that would compromise the system architecture.

## 3. Target Audience (Who is it for?)
This module is intended for backend engineers, system architects, and technical leads who are building large-scale Enterprise SaaS applications, Data Platforms, or intricate microservice ecosystems. It is meant for teams that prioritize long-term maintainability, strict typing, and robust security policies over "quick-and-dirty" prototyping.

## 4. Architecture (How does it work?)
`ferrox-php-database-core` integrates seamlessly into the Ferrox-PHP layered architecture. It relies on PHP 8.3+ features like readonly classes, Enums, and Attributes. Under the hood, this module uses highly optimized constructs to achieve O(1) runtime execution speed, operating precisely where it belongs in the strict Onion Architecture.

## 5. Installation / Setup
To get started with `ferrox-php-database-core`, ensure you have a PHP 8.3+ environment.
```bash
composer require ferrox/ferrox-php-database-core
```
Ensure that no legacy middleware or global states bypass the built-in pipelines.

## 6. Quickstart (Usage)
Initializing the foundational structure for `ferrox-php-database-core` is straightforward:
```php
use Ferrox\DatabaseCore\ExampleComponent;

// Typical enterprise usage involves registering it in the core Container
$container->singleton(ExampleComponent::class, fn() => new ExampleComponent());
```

## 7. Ecosystem Integration
The concepts described in this architectural overview integrate closely with every other component in the ferrox-php ecosystem. `ferrox-php-database-core` interacts natively with the Dependency Injection container, the Singleflight components, and the Security guards to form a highly resilient application core.

## 8. Path & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within `ferrox-php-database-core`:

### Path: `ferrox-php-database-core/src/RepositoryInterface.php`

#### Class / Interface: `RepositoryInterface`
The `RepositoryInterface` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-database-core/src/RepositoryInterface.php`.

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
The `UnitOfWorkInterface` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-database-core/src/UnitOfWorkInterface.php`.

- **`beginTransaction() : void`**
  - Executes `beginTransaction` with strict type safety and boundary validation.

- **`commit() : void`**
  - Executes `commit` with strict type safety and boundary validation.

- **`rollback() : void`**
  - Executes `rollback` with strict type safety and boundary validation.

- **`transactional(callable $operation) : mixed`**
  - Executes the given callable inside a transaction. Automatically commits on success, and rolls back on Exception.

