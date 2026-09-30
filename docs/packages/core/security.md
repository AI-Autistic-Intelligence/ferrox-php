---
id: security
title: Security Submodule
---

# Security Submodule

## 1. Overview (What does this do?)
The `Security` submodule within `ferrox-php-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Security` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying security logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Security` submodule:

### Path: `ferrox-php-core/src/Security/MandatoryComplianceMiddleware.php`

#### Class / Interface: `MandatoryComplianceMiddleware`
The `MandatoryComplianceMiddleware` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Security/MandatoryComplianceMiddleware.php`.

- **`__construct(private array $allowedHosts = ['localhost', '127.0.0.1'] // Injected by Config in reality) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`process(Request $request, RequestHandlerInterface $handler) : Response`**
  - Intercepts the HTTP request within the 7-Layer Pipeline, enforcing strict security, logging, and compliance constraints before passing it to the next handler.

