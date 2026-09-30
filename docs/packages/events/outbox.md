---
id: outbox
title: Outbox Submodule
---

# Outbox Submodule

## 1. Overview (What does this do?)
The `Outbox` submodule within `ferrox-php-events` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Outbox` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying outbox logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Outbox` submodule:

### Path: `ferrox-php-events/src/Outbox/OutboxStoreInterface.php`

#### Class / Interface: `OutboxStoreInterface`
The `OutboxStoreInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-events/src/Outbox/OutboxStoreInterface.php`.

- **`store(DomainEventInterface $event) : void`**
  - Executes the `store` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`getUnpublishedEvents(int $limit = 50) : array`**
  - Retrieves unpublished events for the background worker.

- **`markAsPublished(string $eventId) : void`**
  - Executes the `markAsPublished` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

