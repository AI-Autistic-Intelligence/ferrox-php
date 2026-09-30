---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-validation` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-validation/src/ValidationPipe.php`

#### Class / Interface: `ValidationPipe`
The `ValidationPipe` is responsible for enterprise-grade execution of operations within `ferrox-php-validation/src/ValidationPipe.php`.

- **`__construct(string $dtoClass) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`process(Request $request, RequestHandlerInterface $handler) : Response`**
  - Executes the `process` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

#### Class / Interface: `not`
The `not` is responsible for enterprise-grade execution of operations within `ferrox-php-validation/src/ValidationPipe.php`.

- *No explicitly defined methods found (or relies on inheritance/magic methods).*
