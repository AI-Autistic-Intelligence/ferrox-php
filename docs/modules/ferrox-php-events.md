# FERROX-PHP-EVENTS

## 1. Overview (What does this do?)
The `ferrox-php-events` component is an essential part of the robust backend development framework, centered around the 7-Layer Onion Request Pipeline. It adapts the stringent, enterprise-grade conventions established by the original Ferrox ecosystem specifically for PHP 8.3+. 
Event-driven architecture core, providing domain events and the Outbox pattern for microservices.

## 2. Philosophy (Why does it exist?)
The overarching philosophy of `ferrox-php-events` is zero-trust security and maximum decoupling. It exists to solve the common issue of unmaintainable, tightly coupled backend monoliths in standard PHP applications. By enforcing strict boundaries, it prevents developers from taking shortcuts that would compromise the system architecture.

## 3. Target Audience (Who is it for?)
This module is intended for backend engineers, system architects, and technical leads who are building large-scale Enterprise SaaS applications, Data Platforms, or intricate microservice ecosystems. It is meant for teams that prioritize long-term maintainability, strict typing, and robust security policies over "quick-and-dirty" prototyping.

## 4. Architecture (How does it work?)
`ferrox-php-events` integrates seamlessly into the Ferrox-PHP layered architecture. It relies on PHP 8.3+ features like readonly classes, Enums, and Attributes. Under the hood, this module uses highly optimized constructs to achieve O(1) runtime execution speed, operating precisely where it belongs in the strict Onion Architecture.

## 5. Installation / Setup
To get started with `ferrox-php-events`, ensure you have a PHP 8.3+ environment.
```bash
composer require ferrox/ferrox-php-events
```
Ensure that no legacy middleware or global states bypass the built-in pipelines.

## 6. Quickstart (Usage)
Initializing the foundational structure for `ferrox-php-events` is straightforward:
```php
use Ferrox\Events\ExampleComponent;

// Typical enterprise usage involves registering it in the core Container
$container->singleton(ExampleComponent::class, fn() => new ExampleComponent());
```

## 7. Ecosystem Integration
The concepts described in this architectural overview integrate closely with every other component in the ferrox-php ecosystem. `ferrox-php-events` interacts natively with the Dependency Injection container, the Singleflight components, and the Security guards to form a highly resilient application core.

## 8. Path & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within `ferrox-php-events`:

### Path: `ferrox-php-events/src/DomainEventInterface.php`

#### Class / Interface: `DomainEventInterface`
The `DomainEventInterface` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-events/src/DomainEventInterface.php`.

- **`getEventId() : string`**
  - Executes `getEventId` with strict type safety and boundary validation.

- **`getOccurredOn() : \DateTimeImmutable`**
  - Executes `getOccurredOn` with strict type safety and boundary validation.

- **`getEventName() : string`**
  - Executes `getEventName` with strict type safety and boundary validation.

- **`getPayload() : array`**
  - Serializes the event payload for Outbox storage or Message Brokers (Redis/RabbitMQ).

### Path: `ferrox-php-events/src/EventDispatcherInterface.php`

#### Class / Interface: `EventDispatcherInterface`
The `EventDispatcherInterface` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-events/src/EventDispatcherInterface.php`.

- **`dispatch(DomainEventInterface $event) : void`**
  - Dispatches an event to all registered synchronous listeners.

### Path: `ferrox-php-events/src/Outbox/OutboxStoreInterface.php`

#### Class / Interface: `OutboxStoreInterface`
The `OutboxStoreInterface` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-events/src/Outbox/OutboxStoreInterface.php`.

- **`store(DomainEventInterface $event) : void`**
  - Executes `store` with strict type safety and boundary validation.

- **`getUnpublishedEvents(int $limit = 50) : array`**
  - Retrieves unpublished events for the background worker.

- **`markAsPublished(string $eventId) : void`**
  - Executes `markAsPublished` with strict type safety and boundary validation.

