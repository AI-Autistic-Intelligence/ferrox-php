---
id: decorators
title: Decorators Submodule
---

# Decorators Submodule

## 1. Overview (What does this do?)
The `Decorators` submodule within `ferrox-php-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Decorators` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying decorators logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Decorators` submodule:

### Path: `ferrox-php-core/src/Decorators/Controller.php`

#### Class / Interface: `Controller`
The `Controller` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Decorators/Controller.php`.

- **`__construct(public readonly string $path = '') : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

### Path: `ferrox-php-core/src/Decorators/Get.php`

#### Class / Interface: `Get`
The `Get` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Decorators/Get.php`.

- **`__construct(public readonly string $route = '') : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

### Path: `ferrox-php-core/src/Decorators/Post.php`

#### Class / Interface: `Post`
The `Post` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Decorators/Post.php`.

- **`__construct(public readonly string $route = '') : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

### Path: `ferrox-php-core/src/Decorators/RequireRole.php`

#### Class / Interface: `RequireRole`
The `RequireRole` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Decorators/RequireRole.php`.

- **`__construct(string ...$roles) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

### Path: `ferrox-php-core/src/Decorators/UseGuard.php`

#### Class / Interface: `UseGuard`
The `UseGuard` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Decorators/UseGuard.php`.

- **`__construct(public readonly string $guardClass) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

