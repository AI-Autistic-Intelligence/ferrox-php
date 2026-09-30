# FERROX-PHP-SECURITY

## 1. Overview (What does this do?)
The `ferrox-php-security` component is an essential part of the robust backend development framework, centered around the 7-Layer Onion Request Pipeline. It adapts the stringent, enterprise-grade conventions established by the original Ferrox ecosystem specifically for PHP 8.3+. 
Zero-trust security layer including PASETO v4 Auth and Sentinel Threat Engine.

## 2. Philosophy (Why does it exist?)
The overarching philosophy of `ferrox-php-security` is zero-trust security and maximum decoupling. It exists to solve the common issue of unmaintainable, tightly coupled backend monoliths in standard PHP applications. By enforcing strict boundaries, it prevents developers from taking shortcuts that would compromise the system architecture.

## 3. Target Audience (Who is it for?)
This module is intended for backend engineers, system architects, and technical leads who are building large-scale Enterprise SaaS applications, Data Platforms, or intricate microservice ecosystems. It is meant for teams that prioritize long-term maintainability, strict typing, and robust security policies over "quick-and-dirty" prototyping.

## 4. Architecture (How does it work?)
`ferrox-php-security` integrates seamlessly into the Ferrox-PHP layered architecture. It relies on PHP 8.3+ features like readonly classes, Enums, and Attributes. Under the hood, this module uses highly optimized constructs to achieve O(1) runtime execution speed, operating precisely where it belongs in the strict Onion Architecture.

## 5. Installation / Setup
To get started with `ferrox-php-security`, ensure you have a PHP 8.3+ environment.
```bash
composer require ferrox/ferrox-php-security
```
Ensure that no legacy middleware or global states bypass the built-in pipelines.

## 6. Quickstart (Usage)
Initializing the foundational structure for `ferrox-php-security` is straightforward:
```php
use Ferrox\Security\ExampleComponent;

// Typical enterprise usage involves registering it in the core Container
$container->singleton(ExampleComponent::class, fn() => new ExampleComponent());
```

## 7. Ecosystem Integration
The concepts described in this architectural overview integrate closely with every other component in the ferrox-php ecosystem. `ferrox-php-security` interacts natively with the Dependency Injection container, the Singleflight components, and the Security guards to form a highly resilient application core.

## 8. Path & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within `ferrox-php-security`:

### Path: `ferrox-php-security/src/Auth/PasetoAuthGuard.php`

#### Class / Interface: `PasetoAuthGuard`
The `PasetoAuthGuard` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-security/src/Auth/PasetoAuthGuard.php`.

- **`process(Request $request, RequestHandlerInterface $handler) : Response`**
  - intercepts the HTTP request to validate the PASETO token.

### Path: `ferrox-php-security/src/Sentinel/SentinelThreatEngineMiddleware.php`

#### Class / Interface: `SentinelThreatEngineMiddleware`
The `SentinelThreatEngineMiddleware` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-security/src/Sentinel/SentinelThreatEngineMiddleware.php`.

- **`process(Request $request, RequestHandlerInterface $handler) : Response`**
  - Processes the request through the heuristic engine before it reaches validation.

- **`calculateEntropy(string $data) : float`**
  - Calculates the Shannon Entropy of a given string. Higher values (> 4.8) generally indicate compressed, encrypted, or highly obfuscated data.

