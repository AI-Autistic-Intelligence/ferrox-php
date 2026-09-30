# FERROX-PHP-CORE

## 1. Overview
The `ferrox-php-core` component is the beating heart of the Ferrox PHP framework. Unlike standard PHP frameworks that bootstrap on every request, Ferrox is designed for memory-resident engines (Swoole, RoadRunner). The Core module manages the Application Lifecycle, the Native Dependency Injection Container, and the HTTP Pipeline.

## 2. Philosophy & Strict Boundaries
Traditional MVC frameworks allow developers to inject state anywhere, leading to catastrophic memory leaks in long-running processes. `ferrox-php-core` strictly segregates **Boot-time** Singletons from **Runtime** Request Scopes.

---

## 3. Class & Function Deep Dive

### 📂 `App/FerroxApp.php`
The `FerroxApp` orchestrates the boot sequence. It ensures that the dependency graph is resolved exactly once before accepting TCP/HTTP connections.

- **`builder() : self`**
  - **Purpose**: Initializes the immutable AppBuilder. All configurations must be chained here.
- **`withEngine(string $engineClass) : self`**
  - **Purpose**: Binds the execution engine (e.g., `SwooleEngine::class` or `RoadRunnerEngine::class`).
- **`addPipeline(array $middlewares) : self`**
  - **Purpose**: Defines the global 7-Layer HTTP Middleware pipeline. These middlewares execute sequentially on every request.
- **`build() : self`**
  - **Purpose**: Seals the configuration. Once built, the Container is locked, preventing runtime modifications to the dependency graph (Zero-Trust).
- **`start() : void`**
  - **Purpose**: Hands over control to the underlying event loop (Swoole/RR). The PHP process will now stay alive indefinitely.

### 📂 `Container/Container.php`
The PSR-11 compliant Native DI Container. It uses reflection during the Pre-warming phase, caching constructor signatures for O(1) instantiation at runtime.

- **`bind(string $id, $concrete = null) : void`**
  - **Purpose**: Binds an interface to an implementation. This creates a **Factory** (a fresh instance is created per HTTP request).
- **`instance(string $id, object $instance) : void`**
  - **Purpose**: Registers a **Singleton**. Use extreme caution: state stored in a Singleton will leak across different user requests!
- **`resolve(string $concrete) : mixed`**
  - **Purpose**: Recursively resolves all dependencies for a given class.

### 📂 `Decorators/` (Attributes)
Ferrox uses PHP 8 Attributes to declaratively define HTTP boundaries.

- **`#[Controller(string $path)]`**
  - **Purpose**: Marks a class as a boundary interface. In Ferrox, Controllers should contain NO business logic. They only map HTTP to the CommandBus.
- **`#[Get] / #[Post]`**
  - **Purpose**: Maps specific HTTP verbs to class methods.
- **`#[UseGuard(string $guardClass)]`**
  - **Purpose**: Injects a Security Guard (e.g., `PasetoAuthGuard`) that executes *before* the controller method is invoked.
- **`#[RequireRole(string ...$roles)]`**
  - **Purpose**: Enforces RBAC permissions based on the context injected by the Guard.

### 📂 `Errors/AppError.php`
Throwing native `Exception` is considered a legacy anti-pattern. However, when a catastrophic boundary violation occurs, `AppError` is used.

- **`badRequest(string $msg, array $details)`**
  - Triggers a 400 error, automatically mapping the `$details` to the RFC 7807 Problem Details format.
- **`unauthorized(string $msg)` / `forbidden(string $msg)`**
  - Triggers 401/403 responses cleanly.

### 📂 `Http/Pipeline.php`
The implementation of the Chain of Responsibility pattern for HTTP requests.

- **`handle(Request $request) : Response`**
  - **Purpose**: Pushes the request through all registered Middlewares. This pipeline is mathematically guaranteed to never throw an uncaught exception. All panics are caught at the pipeline boundary and sanitized into a safe `ErrorResponse`, satisfying OWASP WSTG-INPV-005.

### 📂 `Security/MandatoryComplianceMiddleware.php`
- **`process(...) : Response`**
  - **Purpose**: A hardcoded, un-removable middleware that asserts HTTP Host header validation to prevent Host Header Injection attacks, and injects strict HSTS, CSP, and X-Frame-Options headers.
