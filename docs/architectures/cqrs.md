---
id: cqrs
title: CQRS & Outbox Pattern
sidebar_position: 1
---

# 🔄 CQRS & Outbox Pattern

## 1. Philosophy / Purpose
As systems scale, the requirements for reading data (fast, cacheable, aggregated) diverge completely from writing data (transactional, validated, strictly consistent). CQRS (Command Query Responsibility Segregation) splits the application into two distinct mental and physical models. Ferrox PHP enforces this to ensure that all mutations (Commands) are 100% ACID compliant.

## 2. Architectural Layering
CQRS lives in **Layer 4 (Application)**. The HTTP Controllers (Layer 3) are completely anemic—they do nothing but extract JSON and dispatch Commands. The CommandBus routes these to Handlers (Layer 4), which then interact with the Domain (Layer 5) and Infrastructure (Layer 6).

## 3. Core Concepts (For Neophytes)
Instead of writing database updates in your HTTP route, you create a Command object.
```php
class CreateOrderCommand implements CommandInterface {
    public function __construct(public string $productId, public int $qty) {}
}
```
Then, you create a Handler:
```php
class CreateOrderHandler {
    public function __construct(private OrderRepository $repo) {}
    
    public function handle(CreateOrderCommand $cmd): Result {
        $order = new Order($cmd->productId, $cmd->qty);
        $this->repo->save($order);
        return Result::ok($order->id);
    }
}
```
The HTTP controller just calls `$bus->dispatch(new CreateOrderCommand(...))`.

## 4. How it Works Under the Hood
The `CommandBus` uses PHP Reflection and the PSR-11 Container to dynamically resolve the Handler.
Crucially, the CommandBus automatically wraps the execution of the Handler inside a **Unit of Work** (`UnitOfWorkInterface`). 
If the handler returns `Result::ok()`, the Bus commits the SQL transaction. If the handler returns `Result::err()` or throws a Panic, the Bus rolls back the transaction.

## 5. Why it was designed this way
In traditional MVC, logic is trapped in the HTTP Controller. If an external ERP (like Itsperfect) sends a Webhook, or if a CLI cronjob needs to create an order, you cannot reuse the Controller. CQRS decouples the *intent* from the *transport*. The CommandBus doesn't care if the Command came from HTTP, CLI, or GraphQL.

## 6. Anti-Patterns
- **❌ God Handlers**: A handler that executes queries, sends emails, and calls APIs. Handlers should ONLY orchestrate domain logic and state changes.
- **❌ Commands dispatching Commands**: A handler dispatching another Command. This breaks the transaction boundary. Use Domain Events instead.
- **❌ Returning Entities from Commands**: Commands should return basic scalar values (like an ID) or nothing, not massive Doctrine ORM objects.

## 7. Enterprise Usage (For Seniors): The Outbox Pattern
In enterprise systems, saving to the database and publishing a webhook to Stripe/Itsperfect is a distributed systems nightmare (Two-Phase Commit problem). If the DB saves, but the network crashes before sending the webhook, the systems are out of sync.

Ferrox PHP solves this using the **Transactional Outbox Pattern**:
1. The Handler modifies the `Order` table.
2. The Handler publishes an `OrderCreatedEvent` to the `OutboxStoreInterface`.
3. The Outbox Store inserts the JSON event into an `outbox_events` SQL table *in the exact same ACID transaction*.
4. The transaction commits. 
5. A completely separate background worker polls the `outbox_events` table and pushes the webhooks asynchronously, guaranteeing **At-Least-Once delivery** without blocking the HTTP thread.
