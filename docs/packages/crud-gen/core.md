---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-crud-gen` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-crud-gen/src/CrudGenerator.php`

#### Class / Interface: `CrudGenerator`
The `CrudGenerator` is responsible for enterprise-grade execution of operations within `ferrox-php-crud-gen/src/CrudGenerator.php`.

- **`generateRoutesForEntity(Container $container, string $entityClass, bool $exposeBusinessMetrics = true) : void`**
  - In a real application, this scans the `src/Entities` directory, reads #[CrudResource] attributes, and dynamically registers CQRS Handlers and Http Routes into the Container and Pipeline.

