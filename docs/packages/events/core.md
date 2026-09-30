---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-events` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-events/src/DomainEventInterface.php`

#### Class / Interface: `DomainEventInterface`
The `DomainEventInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-events/src/DomainEventInterface.php`.

- **`getEventId() : string`**
  - Executes the `getEventId` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`getOccurredOn() : \DateTimeImmutable`**
  - Executes the `getOccurredOn` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`getEventName() : string`**
  - Executes the `getEventName` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`getPayload() : array`**
  - Serializes the event payload for Outbox storage or Message Brokers (Redis/RabbitMQ).

### Path: `ferrox-php-events/src/EventDispatcher.php`

#### Class / Interface: `EventDispatcher`
The `EventDispatcher` is responsible for enterprise-grade execution of operations within `ferrox-php-events/src/EventDispatcher.php`.

- **`__construct(private Container $container, private ?OutboxStoreInterface $outbox = null) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`listen(string $eventClass, string $listenerClass) : void`**
  - Registers a listener for a specific Domain Event.

- **`dispatch(DomainEventInterface $event) : void`**
  - Dispatches an event. If an Outbox is configured, it safely persists the event for asynchronous background processing (DRY and resilient).

- **`executeSync(DomainEventInterface $event) : void`**
  - Executes the listeners synchronously.

### Path: `ferrox-php-events/src/EventDispatcherInterface.php`

#### Class / Interface: `EventDispatcherInterface`
The `EventDispatcherInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-events/src/EventDispatcherInterface.php`.

- **`dispatch(DomainEventInterface $event) : void`**
  - Dispatches an event to all registered synchronous listeners.

