---
id: result-monad
title: The Result Monad
sidebar_position: 1
---

# 📦 The Result Monad (`Result<T, E>`)

## 1. Philosophy / Purpose
PHP's exception model is fundamentally flawed for predictable control flow. When a function throws an Exception, it acts as an invisible `goto` statement, ripping through the call stack until it hits a `catch` block (or crashes the app). The `Result` monad forces the developer to handle failures explicitly as standard return values, making the execution path mathematically predictable.

## 2. Architectural Layering
The Result Monad (`Ferrox\Utils\Types\Result`) is an **Abstraction**. It belongs in Layer 7 (Utils/Core Primitives) and is utilized ubiquitously across all layers of the Onion Architecture.

## 3. Core Concepts (For Neophytes)
Instead of throwing an error or returning `null` when something fails, a function returns a `Result` object.
- **`Result::ok($value)`**: Contains the successful data.
- **`Result::err($reason)`**: Contains the error details.

```php
public function divide(int $a, int $b): Result {
    if ($b === 0) return Result::err("Cannot divide by zero");
    return Result::ok($a / $b);
}

$res = divide(10, 0);
if ($res->isErr()) {
    echo "Failed: " . $res->unwrapErr();
}
```

## 4. How it Works Under the Hood
The `Result` class encapsulates a boolean state (`$isOk`) and the inner `$value` or `$error`.
If a developer blindly calls `$res->unwrap()` on a Result that is actually an `Err`, the Monad throws a fatal `RuntimeException` (Panic). This strictness guarantees that no failure can be silently ignored.

## 5. Why it was designed this way
This pattern is lifted directly from Rust (`std::result::Result`) and Go (`val, err := ...`). In memory-resident enterprise systems, an unhandled exception can leave data structures in an inconsistent state or sever database connection pools. By forcing explicit handling, the compiler (or static analyzers like PHPStan) ensures that all edge cases are addressed during development.

## 6. Anti-Patterns
- **❌ Throwing Exceptions for Business Logic**: Never throw an `InsufficientFundsException`. Return `Result::err(OrderError::INSUFFICIENT_FUNDS)`. Exceptions should be reserved ONLY for catastrophic hardware/infrastructure failures (e.g., the database server caught fire).
- **❌ Blind Unwrapping**: Using `$res->unwrap()` without checking `$res->isOk()` first. This defeats the entire purpose of the Monad.

## 7. Enterprise Usage (For Seniors)
When orchestrating complex Sagas (e.g., reserving stock in the warehouse AND charging a credit card), you will chain multiple operations. Using the `Result` monad allows for elegant **Railway Oriented Programming**. If Step 1 returns an `Err`, you can instantly short-circuit and return that `Err` to the CommandBus, which will automatically rollback the SQL UnitOfWork and alert the Dead Letter Queue, ensuring zero dirty reads in the ERP.
