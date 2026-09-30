---
id: app
title: App Submodule
---

# App Submodule

## 1. Overview (What does this do?)
The `App` submodule within `ferrox-php-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `App` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying app logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `App` submodule:

### Path: `ferrox-php-core/src/App/FerroxApp.php`

#### Class / Interface: `FerroxApp`
The `FerroxApp` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/App/FerroxApp.php`.

- **`__construct() : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`builder() : self`**
  - Initializes a new Ferrox Application Builder.

- **`withEngine(string $engineClass) : self`**
  - Specifies the execution engine adapter (e.g., Swoole, RoadRunner).

#### Class / Interface: `names`
The `names` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/App/FerroxApp.php`.

- **`addPipeline(array $middlewares) : self`**
  - Executes the `addPipeline` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

#### Class / Interface: `names`
The `names` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/App/FerroxApp.php`.

- **`registerControllers(array $controllers) : self`**
  - Executes the `registerControllers` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`build() : self`**
  - Finalizes the application build process and verifies security invariants.

- **`start() : void`**
  - Executes the `start` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`getContainer() : Container`**
  - Executes the `getContainer` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

