---
id: adapters
title: Adapters Submodule
---

# Adapters Submodule

## 1. Overview (What does this do?)
The `Adapters` submodule within `ferrox-php-rpc` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Adapters` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying adapters logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Adapters` submodule:

### Path: `ferrox-php-rpc/src/Adapters/SwooleRpcClient.php`

#### Class / Interface: `SwooleRpcClient`
The `SwooleRpcClient` is responsible for enterprise-grade execution of operations within `ferrox-php-rpc/src/Adapters/SwooleRpcClient.php`.

- **`__construct(array $serviceRegistryConfig) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`call(string $serviceName, string $endpoint, array $payload) : array`**
  - Executes the `call` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

