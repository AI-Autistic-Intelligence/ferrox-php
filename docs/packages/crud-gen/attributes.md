---
id: attributes
title: Attributes Submodule
---

# Attributes Submodule

## 1. Overview (What does this do?)
The `Attributes` submodule within `ferrox-php-crud-gen` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Attributes` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying attributes logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Attributes` submodule:

### Path: `ferrox-php-crud-gen/src/Attributes/CrudResource.php`

#### Class / Interface: `automatically`
The `automatically` is responsible for enterprise-grade execution of operations within `ferrox-php-crud-gen/src/Attributes/CrudResource.php`.

- *No explicitly defined methods found (or relies on inheritance/magic methods).*
#### Class / Interface: `CrudResource`
The `CrudResource` is responsible for enterprise-grade execution of operations within `ferrox-php-crud-gen/src/Attributes/CrudResource.php`.

- **`__construct(public readonly string $basePath, public readonly array $allowedRoles = ['ADMIN'], // Default to strictly internal public readonly bool $publishEvents = true // Emit "Created", "Updated" Domain Events automatically) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

