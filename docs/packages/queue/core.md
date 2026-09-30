---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-queue` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-queue/src/QueueInterface.php`

#### Class / Interface: `QueueInterface`
The `QueueInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-queue/src/QueueInterface.php`.

- **`push(string $queueName, string $payload, int $delaySeconds = 0) : void`**
  - Push a serialized job/event to the queue.

- **`consume(string $queueName, callable $handler) : void`**
  - Consume jobs from the queue via a worker loop.

