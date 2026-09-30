---
id: connection
title: Connection Submodule
---

# Connection Submodule

## 1. Overview (What does this do?)
The `Connection` submodule within `ferrox-php-database-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Connection` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying connection logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Connection` submodule:

### Path: `ferrox-php-database-core/src/Connection/ReplicaAwareManager.php`

#### Class / Interface: `ReplicaAwareManager`
The `ReplicaAwareManager` is responsible for enterprise-grade execution of operations within `ferrox-php-database-core/src/Connection/ReplicaAwareManager.php`.

- **`__construct(private mixed $masterConnection, private array $replicaConnections) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`getWriteConnection() : mixed`**
  - Gets the connection for a Write operation (INSERT, UPDATE, DELETE). Always returns the Master database and flags the context to force subsequent reads to also use the Master to avoid replication lag.

- **`getReadConnection() : mixed`**
  - Gets the connection for a Read operation (SELECT). If a write occurred previously in this lifecycle, it forces the Master. Otherwise, it load-balances across regional Replicas.

- **`forceMaster() : void`**
  - Explicitly forces all subsequent operations to use the Master connection.

- **`reset() : void`**
  - Resets the routing state (useful for workers that handle multiple requests).

