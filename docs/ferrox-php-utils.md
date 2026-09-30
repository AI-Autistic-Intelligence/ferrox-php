---
id: ferrox-php-utils
title: Utils & Result Monads
sidebar_position: 3
---

# 🧰 Ferrox PHP Utils

## 1. Philosophy / Purpose
In standard PHP, things break unpredictably. A function might return a string, or `null`, or it might throw an Exception. The purpose of `ferrox-php-utils` is to completely eliminate this unpredictability. We use functional programming concepts (Monads) to force you to acknowledge that an operation can fail, ensuring you handle both success and failure explicitly.

## 2. Architectural Layering
Utils exist outside the Onion Architecture layers. They are fundamental primitives used across *all* layers—from the API controllers to the deepest Domain Models.

## 3. Core Concepts (For Neophytes)
Instead of returning `null`, we return an `Option`. Instead of throwing an Exception, we return a `Result`.
- **`Result::ok($value)`**: Everything went fine, here is your data.
- **`Result::err($reason)`**: Something went wrong, here is why.
- **`Option::some($value)`**: I found the user, here they are.
- **`Option::none()`**: I didn't find the user.

## 4. How it Works Under the Hood
The `Result<T, E>` class wraps your data. You cannot access the data directly. You must call `$result->unwrap()`. If you call `unwrap()` on an `err()` result, the application panics (shuts down safely). To handle it properly, you check `if ($result->isOk())` or use `$result->unwrapOr($default)`.

## 5. Why it was designed this way
Exceptions act like invisible `goto` statements. If a database query fails deep inside a repository, the Exception bubbles up and might crash the entire request if no one catches it. By using `Result`, the failure becomes a *value* that must be returned and inspected, mimicking the safety of Rust and Go.

## 6. Anti-Patterns
- **❌ Unwrapping without checking**: Calling `$result->unwrap()` without first checking `$result->isOk()` is identical to ignoring a potential Exception.
- **❌ Returning null**: Never return `null` from any function. Use `Option::none()`.

## 7. Enterprise Usage (For Seniors)
Beyond Monads, the Utils package includes `StringUtils::generateUuidV7Fallback()`. While standard UUIDv4 is random, inserting millions of random strings into a MySQL/PostgreSQL Primary Key causes massive B-Tree index fragmentation. UUIDv7 is time-ordered, solving this performance bottleneck for high-throughput enterprise SaaS. The DateUtils enforce strict UTC strings for seamless Global timezone synchronizations.
