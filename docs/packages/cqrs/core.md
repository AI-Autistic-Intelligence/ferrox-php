---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-cqrs` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-cqrs/src/CommandBus.php`

#### Class / Interface: `CommandBus`
The `CommandBus` is responsible for enterprise-grade execution of operations within `ferrox-php-cqrs/src/CommandBus.php`.

- **`__construct(private Container $container) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`registerHandler(string $commandClass, string $handlerClass) : void`**
  - Executes the `registerHandler` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`dispatch(CommandInterface $command) : mixed`**
  - Dispatches a Command to its registered Handler.

### Path: `ferrox-php-cqrs/src/CommandInterface.php`

#### Class / Interface: `CommandInterface`
The `CommandInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-cqrs/src/CommandInterface.php`.

- *No explicitly defined methods found (or relies on inheritance/magic methods).*
