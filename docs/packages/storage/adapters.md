---
id: adapters
title: Adapters Submodule
---

# Adapters Submodule

## 1. Overview (What does this do?)
The `Adapters` submodule within `ferrox-php-storage` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Adapters` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying adapters logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Adapters` submodule:

### Path: `ferrox-php-storage/src/Adapters/LocalStorageAdapter.php`

#### Class / Interface: `LocalStorageAdapter`
The `LocalStorageAdapter` is responsible for enterprise-grade execution of operations within `ferrox-php-storage/src/Adapters/LocalStorageAdapter.php`.

- **`__construct(string $basePath) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`put(string $path, string $contents, array $options = []) : string`**
  - Executes the `put` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`get(string $path) : ?string`**
  - Executes the `get` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`delete(string $path) : bool`**
  - Executes the `delete` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`getPresignedUrl(string $path, int $expiresInSeconds = 3600) : string`**
  - Executes the `getPresignedUrl` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`buildPath(string $path) : string`**
  - Executes the `buildPath` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

