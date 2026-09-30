import os

docs_dir = "docs/components"
os.makedirs(docs_dir, exist_ok=True)

docs = {
    "di-container.md": """---
id: di-container
title: Native DI Container & App Lifecycle
sidebar_position: 2
---

# 🏗️ Native DI Container & App Lifecycle

## 1. Philosophy / Purpose
In a memory-resident PHP environment (like Swoole or RoadRunner), the application boots exactly once and stays alive in RAM to serve millions of requests. The Native Dependency Injection Container must strictly differentiate between **Singletons** (booted once) and **Request-Scoped** services (instantiated per HTTP request). Its purpose is to guarantee memory safety and zero data-bleeding between tenants.

## 2. Architectural Layering
The Container lives at the very core of **Layer 7 (Infrastructure/Bootstrapping)**. It wire-frames the entire application before Layer 1 (HTTP) even begins accepting connections.

## 3. Core Concepts (For Neophytes)
Instead of writing `new UserService(new UserRepository())` everywhere, you register them in the Container.
```php
$container = new Container();
// Singleton: Shared across all requests (e.g. Database Connection)
$container->singleton(Database::class, fn() => new Database($env));

// Factory: A fresh instance every time it is requested (e.g. Current User Context)
$container->factory(UserContext::class, fn() => new UserContext());
```

## 4. How it Works Under the Hood
The `Ferrox\Core\Container\Container` implements PSR-11 (`ContainerInterface`). 
Under the hood, it uses an internal `array $instances` for singletons and `array $factories` for dynamic resolution.
Crucially, when the `FerroxApp` boots, it performs a **Pre-warming phase**: it eagerly instantiates all singletons so that the first HTTP request doesn't suffer a cold-start penalty. Reflection is used minimally during boot and cached heavily to ensure O(1) retrieval during runtime.

## 5. Why it was designed this way
Traditional PHP frameworks rely heavily on static facades or global state (`App::make()`). In Swoole, global state is shared across concurrent coroutines, leading to catastrophic data leaks (User A seeing User B's data). Ferrox's DI strictly isolates request-scoped dependencies, passing the container instance explicitly down the HTTP Pipeline.

## 6. Anti-Patterns
- **❌ State in Singletons**: Storing `$this->currentUser` inside a Singleton service. This will leak the user to the next HTTP request!
- **❌ Service Locator Pattern**: Injecting the `$container` itself into your controllers/services to pull dependencies dynamically. Always inject the explicit interfaces you need via the constructor.

## 7. Enterprise Usage (For Seniors)
For complex ERP integrations (like Itsperfect), the Container supports **Contextual Binding**. You can bind different implementations of `OutboxStoreInterface` depending on the tenant or environment. During the `FerroxApp::bootstrap()` phase, the system reads the environment variables and injects a `RedisOutboxStore` for high-throughput nodes and a `PostgresOutboxStore` for strict ACID nodes.
""",

    "authentication.md": """---
id: authentication
title: Authentication (PASETO v4 & Guards)
sidebar_position: 3
---

# 🔐 Authentication (PASETO v4 & Guards)

## 1. Philosophy / Purpose
Authentication is the perimeter wall of the system. Ferrox explicitly rejects JWT (JSON Web Tokens) due to their inherent cryptographic design flaws (e.g., algorithmic confusion attacks, "none" algorithm vulnerabilities). Instead, Ferrox strictly mandates **PASETO v4** (Platform-Agnostic Security Tokens) for stateless authentication.

## 2. Architectural Layering
Authentication operates at **Layer 2 (Guards/Security)**. It intercepts requests immediately after the Sentinel Threat Engine, validating identity before the request reaches the CQRS layer.

## 3. Core Concepts (For Neophytes)
PASETO works like a sealed envelope. Only the server can seal it, and only the server can open it.
```php
#[UseGuard(PasetoAuthGuard::class)]
#[RequireRole('admin')]
class CreateOrderController {
    public function handle(Request $req): Response {
        // Only valid PASETO tokens with the 'admin' role reach here
    }
}
```

## 4. How it Works Under the Hood
The `PasetoAuthGuard` implements `MiddlewareInterface`.
When a request arrives with an `Authorization: Bearer <token>`, the Guard parses the `v4.local` (symmetric encryption) or `v4.public` (asymmetric Ed25519 signatures) token.
Unlike JWT, PASETO v4 encrypts the payload using XChaCha20-Poly1305. The Guard extracts the `sub` (User ID) and injects a `SecurityContext` object into the `$request` attributes, passing it down the pipeline.

## 5. Why it was designed this way
JWT asks the token itself how it should be verified (the `alg` header). This is fundamentally insecure. PASETO fixes this by forcing the developer to choose the algorithm version (`v4`) explicitly at the infrastructure level. Ferrox PHP adopts this to achieve Zero-Trust compliance out-of-the-box.

## 6. Anti-Patterns
- **❌ Using JWT**: Completely forbidden in the Ferrox ecosystem.
- **❌ Storing PII in Tokens**: Do not store emails or SSNs in the PASETO payload, even if encrypted. Store only the absolute minimum Claims (e.g., UUID, roles, expiration).
- **❌ Long Expirations**: Setting token TTL to 30 days. Use short-lived PASETO tokens (15 minutes) combined with a highly secure Refresh Token flow backed by Redis.

## 7. Enterprise Usage (For Seniors)
In an enterprise multi-tenant ERP, the `PasetoAuthGuard` does not just validate the signature. It parses the `tenant_id` from the PASETO footer (unencrypted but cryptographically signed metadata). It then dynamically re-binds the database connection string in the DI Container for the scope of that specific HTTP request, ensuring complete database-level tenant isolation.
""",

    "singleflight.md": """---
id: singleflight
title: Circuit Breaker & Singleflight
sidebar_position: 4
---

# 🚦 Circuit Breaker & Singleflight

## 1. Philosophy / Purpose
Under massive traffic spikes, a cache miss can cause a "Thundering Herd" (Cache Stampede), where 10,000 concurrent requests try to execute the exact same heavy SQL query simultaneously, instantly killing the database. Singleflight ensures that only *one* request executes the work, while the other 9,999 requests wait for the result.

## 2. Architectural Layering
Singleflight sits at **Layer 6 (Infrastructure/Data Access)**, usually wrapping Cache Repositories or external HTTP clients (like the Itsperfect API client).

## 3. Core Concepts (For Neophytes)
If 10 users ask for the "Top 100 Products" at the exact same millisecond, you don't query the database 10 times. You query it once, and hand the exact same result to all 10 users.
```php
$result = $singleflight->do('top_products', function() use ($db) {
    return $db->query("SELECT heavy query...");
});
```

## 4. How it Works Under the Hood
The `Ferrox\Data\Concurrency\Singleflight` class maintains a thread-safe map of active keys (via Swoole Table or APCu).
When `do($key, $callback)` is called:
1. It checks if `$key` is currently being executed by another coroutine.
2. If YES, the current coroutine yields/suspends until the first one finishes.
3. If NO, it marks the `$key` as active, executes the `$callback`, stores the result, and wakes up all waiting coroutines, passing them the result.

## 5. Why it was designed this way
In traditional blocking PHP (PHP-FPM), implementing Singleflight is extremely difficult because processes do not share memory natively. In Ferrox PHP (running on RoadRunner/Swoole), we leverage async primitives (Channels/Coroutines) to achieve this natively, providing Go-like concurrency protections to PHP.

## 6. Anti-Patterns
- **❌ Mutating Singleflight Results**: If the callback returns an Object, and you mutate it, you are mutating the same object reference handed to the 9,999 other concurrent requests! Always return immutable structures or clones.
- **❌ Unbounded Callbacks**: Passing a callback to Singleflight that doesn't have a strict timeout. If the database hangs, all 10,000 waiting requests hang forever.

## 7. Enterprise Usage (For Seniors)
When calling external APIs (e.g., fetching inventory from Itsperfect), you wrap the call in BOTH a **Circuit Breaker** and a **Singleflight**.
Singleflight prevents duplicate parallel requests. The Circuit Breaker monitors the failure rate. If Itsperfect goes down (returns 500s), the Circuit Breaker trips open, instantly returning a `Result::err()` for all subsequent requests without even attempting the network call, allowing the ERP to recover.
""",

    "validation.md": """---
id: validation
title: Validation Pipes & DTOs
sidebar_position: 5
---

# 🛡️ Validation Pipes & DTOs

## 1. Philosophy / Purpose
Never trust the client. Never pass raw arrays (`$_POST` or `json_decode`) into your application layer. Ferrox PHP enforces strict, type-safe Data Transfer Objects (DTOs). The Validation Pipe automatically transforms raw JSON into strongly-typed PHP classes and asserts structural constraints before the controller is even invoked.

## 2. Architectural Layering
Validation lives in **Layer 3 (Adapters/Controllers)**. It acts as a shield between the raw HTTP request and the internal CommandBus.

## 3. Core Concepts (For Neophytes)
Instead of manually checking `if (!isset($payload['email']))`, you define a class with PHP 8 Attributes.
```php
#[ValidatedDto]
class CreateUserDto {
    #[Email]
    public string $email;
    
    #[MinLength(8)]
    public string $password;
}
```
The framework automatically instantiates this class, validates it, and passes it to your route.

## 4. How it Works Under the Hood
The `ValidationPipe` hooks into the Request Lifecycle. 
It uses PHP 8 Reflection to read the Attributes of the expected DTO. It then hydrates the DTO using the raw JSON body. Next, it runs the validation rules (using a highly optimized engine). If any rule fails, it throws a `ValidationException` which the global Pipeline catches and translates into a standard `422 Unprocessable Entity` RFC 7807 problem details response.

## 5. Why it was designed this way
Relying on associative arrays `$data['email']` destroys IDE autocompletion, static analysis (PHPStan), and refactoring capabilities. By enforcing DTOs, the shape of the data is guaranteed at runtime, and the Application Layer (CQRS Commands) can safely rely on the types.

## 6. Anti-Patterns
- **❌ Array Access**: Passing an associative array directly to a Command or Domain entity.
- **❌ Complex Logic in DTOs**: A DTO should be a dumb data bag. It should not contain methods that calculate prices or query the database.
- **❌ Validation in the Domain**: Checking if an email string is formatted correctly inside the Domain Entity. The Domain should assume the string is already a valid email, having been cleansed by the Validation Pipe.

## 7. Enterprise Usage (For Seniors)
In an enterprise context, you map DTOs to internal Commands using AutoMappers. Furthermore, validation rules can be dynamically loaded from a centralized Configuration Engine. For example, the `MaxQuantity` allowed in an `OrderDto` might be fetched at runtime from Redis, ensuring that B2B and B2C tenants have different strictness levels applied dynamically within the Validation Pipe.
"""
}

for filename, content in docs.items():
    filepath = os.path.join(docs_dir, filename)
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)

print(f"Generated {len(docs)} deep documentation files.")
