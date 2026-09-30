---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-gateway` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-gateway/src/EdgeAuthMiddleware.php`

#### Class / Interface: `EdgeAuthMiddleware`
The `EdgeAuthMiddleware` is responsible for enterprise-grade execution of operations within `ferrox-php-gateway/src/EdgeAuthMiddleware.php`.

- **`__construct(PasetoEngine $pasetoEngine) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`process(array $request, RequestHandlerInterface $handler) : array`**
  - Intercepts the HTTP request within the 7-Layer Pipeline, enforcing strict security, logging, and compliance constraints before passing it to the next handler.

### Path: `ferrox-php-gateway/src/GatewayRouter.php`

#### Class / Interface: `GatewayRouter`
The `GatewayRouter` is responsible for enterprise-grade execution of operations within `ferrox-php-gateway/src/GatewayRouter.php`.

- **`registerService(string $prefix, string $internalUri) : self`**
  - Executes the `registerService` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`proxy(string $uri, string $method, array $headers, string $body) : array`**
  - Proxies the request to the underlying microservice using Swoole HTTP Client.

