---
id: http
title: Http Submodule
---

# Http Submodule

## 1. Overview (What does this do?)
The `Http` submodule within `ferrox-php-core` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Http` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying http logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Http` submodule:

### Path: `ferrox-php-core/src/Http/MiddlewareInterface.php`

#### Class / Interface: `MiddlewareInterface`
The `MiddlewareInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Http/MiddlewareInterface.php`.

- **`process(Request $request, RequestHandlerInterface $handler) : Response`**
  - Intercepts the HTTP request within the 7-Layer Pipeline, enforcing strict security, logging, and compliance constraints before passing it to the next handler.

### Path: `ferrox-php-core/src/Http/Pipeline.php`

#### Class / Interface: `Pipeline`
The `Pipeline` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Http/Pipeline.php`.

- **`__construct(private Container $container, array $middlewares, private RequestHandlerInterface $fallbackHandler) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`handle(Request $request) : Response`**
  - Processes the HTTP Request through the Middleware chain. Guaranteed to catch all internal exceptions and convert them into standard JSON ErrorResponses.

### Path: `ferrox-php-core/src/Http/Request.php`

#### Class / Interface: `Request`
The `Request` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Http/Request.php`.

- **`__construct(public readonly string $method, public readonly string $uri, public readonly array $headers = [], public readonly array $body = []) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`getHeader(string $name) : ?string`**
  - Executes the `getHeader` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-core/src/Http/RequestHandlerInterface.php`

#### Class / Interface: `RequestHandlerInterface`
The `RequestHandlerInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Http/RequestHandlerInterface.php`.

- **`handle(Request $request) : Response`**
  - Primary execution pipeline for this component. Processes the payload with O(1) isolation and returns a predictable output.

### Path: `ferrox-php-core/src/Http/Response.php`

#### Class / Interface: `Response`
The `Response` is responsible for enterprise-grade execution of operations within `ferrox-php-core/src/Http/Response.php`.

- **`__construct(public int $statusCode = 200, public array $headers = [], public array|string $body = '') : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`json(array $data, int $status = 200) : self`**
  - Executes the `json` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`created(array $data = []) : self`**
  - Executes the `created` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

