---
id: pagination
title: Pagination Submodule
---

# Pagination Submodule

## 1. Overview (What does this do?)
The `Pagination` submodule within `ferrox-php-utils` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Pagination` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying pagination logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Pagination` submodule:

### Path: `ferrox-php-utils/src/Pagination/PageRequest.php`

#### Class / Interface: `PageRequest`
The `PageRequest` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Pagination/PageRequest.php`.

- **`__construct(public int $page = 1, public int $limit = 20, public ?string $sortBy = null, public string $sortOrder = 'ASC') : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`getOffset() : int`**
  - Executes the `getOffset` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-utils/src/Pagination/PageResult.php`

#### Class / Interface: `PageResult`
The `PageResult` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Pagination/PageResult.php`.

- **`__construct(public array $items, public int $total, public int $page, public int $limit) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`hasNextPage() : bool`**
  - Executes the `hasNextPage` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`hasPreviousPage() : bool`**
  - Executes the `hasPreviousPage` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

