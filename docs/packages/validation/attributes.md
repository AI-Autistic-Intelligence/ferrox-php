---
id: attributes
title: Attributes Submodule
---

# Attributes Submodule

## 1. Overview (What does this do?)
The `Attributes` submodule within `ferrox-php-validation` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Attributes` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying attributes logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Attributes` submodule:

### Path: `ferrox-php-validation/src/Attributes/ValidatedDto.php`

#### Class / Interface: `ValidatedDto`
The `ValidatedDto` is responsible for enterprise-grade execution of operations within `ferrox-php-validation/src/Attributes/ValidatedDto.php`.

- **`__construct(public bool $strict = true) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

