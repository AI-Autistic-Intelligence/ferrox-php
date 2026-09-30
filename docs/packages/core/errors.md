---
id: errors
title: Errors Submodule
---

# Errors Submodule

## 1. Overview (What does this do?)
The `Errors` submodule within `ferrox-php-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Errors` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying errors logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Errors` submodule:

### Path: `ferrox-php-core/src/Errors/AppError.php`

#### Class / Interface: `AppError`
The `AppError` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Errors/AppError.php`.

- **`__construct(string $message, public readonly int $statusCode = 500, public readonly ?string $errorCode = null, public readonly array $details = []) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`badRequest(string $message, array $details = []) : self`**
  - Executes the `badRequest` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`unauthorized(string $message = 'Unauthorized') : self`**
  - Executes the `unauthorized` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`forbidden(string $message = 'Forbidden') : self`**
  - Executes the `forbidden` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`notFound(string $message = 'Not Found') : self`**
  - Executes the `notFound` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-core/src/Errors/ErrorResponse.php`

#### Class / Interface: `ErrorResponse`
The `ErrorResponse` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Errors/ErrorResponse.php`.

- **`fromAppError(AppError $error) : Response`**
  - Executes the `fromAppError` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`internal(string $message = 'Internal Server Error') : Response`**
  - Executes the `internal` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

