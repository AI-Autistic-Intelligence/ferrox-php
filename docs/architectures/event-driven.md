---
id: event-driven
title: Event-Driven Architecture
---

# Event-Driven Architecture

## 1. Philosophy / Purpose
Domain Events and Event Dispatchers... The purpose of this component is to ensure enterprise-grade stability and zero-trust security in Ferrox PHP. It strictly rejects the legacy paradigms of standard PHP (like global state and silent failures) in favor of deterministic, compile-time-like safety.

## 2. Architectural Layering
This component sits precisely where it belongs in the strict Onion Architecture. It interfaces deeply with the underlying runtime (Swoole/RoadRunner) to guarantee zero memory bleeding between requests.

## 3. Core Concepts (For Neophytes)
If you are coming from Laravel or Symfony, you must unlearn certain habits. In Ferrox PHP, everything is explicit.
```php
// Example of strict Ferrox PHP implementation
public function execute(): Result {
    // Explicit handling over implicit throwing
    return Result::ok('Success');
}
```

## 4. How it Works Under the Hood
Ferrox PHP relies heavily on PHP 8.3+ features like readonly classes, Enums, and Attributes. Under the hood, this module uses highly optimized Reflection (cached during the pre-warming phase) to achieve O(1) runtime execution speed.

## 5. Why it was designed this way
In a memory-resident environment, a single unhandled exception or static state mutation can corrupt the memory space for thousands of concurrent users. We designed this to explicitly isolate state and force the developer to handle edge cases via Monads (Result/Option).

## 6. Anti-Patterns
- **❌ Legacy Global State**: Never use `global` or `static` variables for request-specific data.
- **❌ Implicit Failures**: Do not throw Exceptions for business logic (e.g., "UserNotFound"). Return a `Result::err()`.
- **❌ Bypassing the Pipeline**: Never echo output directly. Always return a proper HTTP Response object.

## 7. Enterprise Usage (For Seniors)
In massively scalable ERP systems like Itsperfect, this component orchestrates complex distributed transactions. By integrating tightly with OpenTelemetry, every action is logged with a consistent `TraceId`. Seniors can extend the internal Engine by injecting custom implementations via the native DI Container during the Application Bootstrap phase, enabling multi-tenant database connection pooling on the fly.
