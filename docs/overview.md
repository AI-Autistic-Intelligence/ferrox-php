---
id: overview
title: Ferrox PHP Architecture
sidebar_position: 1
---

# 🐘 Ferrox PHP: The Enterprise Port

While PHP is traditionally viewed as a request-response scripting language, **Ferrox PHP** reimagines it for the memory-resident era (Swoole, RoadRunner) by porting the strict guarantees, zero-trust security, and functional paradigms of Ferrox Rust.

---

## 1. Philosophy / Purpose

The core philosophy of Ferrox PHP is **Fail-Safe Execution & Zero-Trust**.
In massive enterprise SaaS environments (like E-Commerce or Fashion ERPs), unexpected exceptions or SQL deadlocks can cause catastrophic data loss. Ferrox PHP eradicates these risks by moving away from traditional PHP patterns (`try/catch`, Fat Controllers) in favor of **CQRS, Unit of Work, and Result Monads**.

---

## 2. Architectural Layering (Strict Onion)

Ferrox PHP strictly adheres to the 7-Layer Onion Architecture.
You cannot inject a Repository (Layer 6) directly into a Controller (Layer 4). All interactions must flow through the **CommandBus**.

- **Layer 1-3 (Security & Entry)**: `FerroxApp` Pipeline, `PasetoAuthGuard`, and `SentinelThreatEngineMiddleware`.
- **Layer 4-5 (Application & Domain)**: `CommandBus`, Handlers, and `DomainEventInterface`.
- **Layer 6 (Infrastructure)**: `UnitOfWorkInterface` and `Singleflight` (Anti-Dogpiling).

---

## 3. The `#[CrudResource]` Generator

Building standard CRUD endpoints generates massive amounts of repetitive boilerplate. Ferrox PHP solves this with the `ferrox-php-crud-gen` module, simulating Rust macros via PHP 8 Attributes.

### Usage Example

```php
use Ferrox\CrudGen\Attributes\CrudResource;

#[CrudResource(
    basePath: '/api/v1/orders',
    allowedRoles: ['ADMIN'],
    publishEvents: true // Automatically dispatches to the Outbox
)]
class OrderEntity {
    public string $id;
    public string $status;
}
```

Under the hood, at boot time, the `CrudGenerator` scans this attribute and automatically wires the HTTP Routes, the CQRS `CreateEntityCommand`, and the `UnitOfWork` wrappers. It even registers OpenTelemetry metrics (`ferrox_crud_order_requests_total`).

---

## 4. Why it was designed this way

- **Result Monads (`Result<T, E>`)**: PHP Exceptions act like `goto` statements, destroying control flow predictability. By forcing Handlers to return a `Result::ok()` or `Result::err()`, the developer is forced to explicitly handle the `err()` case (e.g. routing it to a Dead Letter Queue) rather than letting the thread crash.
- **Outbox Pattern**: Integrating with external ERPs (like Itsperfect) via webhooks requires zero data-loss. When an Order is saved, the Domain Event is saved in the same SQL transaction (via `OutboxStoreInterface`), ensuring eventual consistency even if the ERP goes offline.

---

## 5. ✅ Best Practices

- **Use the Singleflight Module**: For heavy database queries or external API calls, wrap them in `$singleflight->work()`. If 1,000 requests hit the endpoint simultaneously, the query executes only once, preventing cache stampedes.
- **Never use JWT**: Always use the `PasetoAuthGuard`.

---

## 6. ❌ Anti-Patterns

- **Fat Controllers**: Executing business logic or saving to the DB directly in an HTTP Middleware or Controller. *Always dispatch a Command to the CommandBus.*
- **Throwing Exceptions for Control Flow**: Do not throw `Exception` to signal a failed validation or API timeout. Return `Result::err()` instead.
- **Synchronous Webhooks**: Never block the HTTP thread waiting for an external API (Stripe/ERP). Emit an event and let the background worker handle it.
