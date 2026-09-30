---
id: ferrox-php-cqrs
title: CQRS & Events
sidebar_position: 4
---

# 🔄 Ferrox CQRS & Domain Events

## 1. Philosophy / Purpose
When an application grows, mixing "reading data" and "writing data" in the same place creates a tangled mess. CQRS (Command Query Responsibility Segregation) splits these two. Commands change data (Create User), Queries read data (Get User). This module provides the infrastructure to guarantee that when you change data, it is done safely, transactionally, and predictably.

## 2. Architectural Layering
This resides in Layer 4 (Application) and Layer 5 (Domain). The `CommandBus` orchestrates the flow from the HTTP controller down to the Handlers. The `OutboxStoreInterface` connects Layer 5 to Layer 6 (Infrastructure) for database persistence.

## 3. Core Concepts (For Neophytes)
- **Command**: A simple object carrying data (e.g., `CreateOrderCommand($items, $price)`).
- **Handler**: The worker that actually does the job (e.g., `CreateOrderHandler`).
- **CommandBus**: The postal service. You give it the Command, and it delivers it to the correct Handler.

## 4. How it Works Under the Hood
When you call `$commandBus->dispatch($cmd)`, the Bus uses Reflection to find the registered handler in the Dependency Injection `Container`. It then executes the handler within a `UnitOfWork`. If the handler succeeds, the transaction commits. If it returns an Error Result, it rolls back automatically.

## 5. Why it was designed this way
In standard MVC frameworks (like Laravel or Symfony), developers write massive logic in the Controllers. If you need to trigger that same logic from a CLI script or a Webhook, you have to duplicate code. By moving logic into Handlers, Controllers become completely empty routing mechanisms.

## 6. Anti-Patterns
- **❌ Logic in Controllers**: Writing `if ($price > 100)` inside the HTTP route.
- **❌ Cross-Handler Dispatching**: Having one Handler dispatch another Command. This breaks the single-transaction boundary. If a Handler needs to trigger something else, it must publish a `DomainEvent`.

## 7. Enterprise Usage (For Seniors)
Integrating with external ERPs (like *Itsperfect*) requires 100% guarantee that Webhooks will fire even if the ERP is temporarily offline. We implement the **Transactional Outbox Pattern**. When the Handler saves an Order to the DB, it also saves a `DomainEvent` stringified payload to an `outbox` SQL table in the *same ACID transaction* via `OutboxStoreInterface::store()`. A separate polling worker calls `getUnpublishedEvents()`, sends them to the ERP, and calls `markAsPublished()`, ensuring Zero Data Loss and Eventual Consistency.
