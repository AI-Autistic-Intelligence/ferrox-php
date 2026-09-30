# FERROX-PHP-UTILS

## 1. Overview
The `ferrox-php-utils` module contains the fundamental cryptographic, temporal, and monadic primitives that power the entire Ferrox PHP ecosystem. It provides the core data structures (Result, Option) necessary to enforce Railway Oriented Programming, ensuring zero-trust null-safety across all enterprise applications.

## 2. Philosophy & Strict Usage
In Ferrox, utility functions are not static "helpers" thrown together randomly. They are strictly typed, memory-safe abstractions. 
For example, returning `null` from a database query is forbidden. You must return an `Option<T>`. Throwing a `UserNotFoundException` for control flow is forbidden. You must return a `Result<T, E>`.

---

## 3. Class & Function Deep Dive

### 📂 `Dates/DateUtils.php`
Time manipulation in enterprise systems is highly sensitive. The `DateUtils` class guarantees that all timestamps strictly adhere to UTC and ISO-8601 to prevent timezone-drift in distributed databases.

- **`nowUtc() : DateTime`**
  - **Purpose**: Generates the current timestamp strictly locked to the UTC timezone. 
  - **Enterprise Usage**: Use this for all `created_at` and `updated_at` properties before persisting to SeaORM/Doctrine. Never use PHP's native `new DateTime()` without a timezone.

- **`formatIso8601(DateTime $date) : string`**
  - **Purpose**: Converts a DateTime object to a strictly formatted ISO-8601 string (`YYYY-MM-DDThh:mm:ss.sssZ`).
  - **Usage**: Mandatory for serializing dates in HTTP JSON responses to frontend clients (React/Vue).

### 📂 `Env/EnvHelper.php`
Standard `getenv()` calls are dangerous because they return strings that can silently evaluate to false. `EnvHelper` enforces strict typings and presence checks.

- **`getOrThrow(string $key) : mixed`**
  - **Purpose**: Fetches an environment variable. If the variable is missing, it **Panics** instantly.
  - **Enterprise Usage**: Used during the `FerroxApp::bootstrap()` phase for critical secrets (e.g., `DATABASE_URL`, `PASETO_SECRET_KEY`). If the secret is missing, the application refuses to boot, preventing a degraded, insecure state.

### 📂 `Strings/StringUtils.php`
- **`toCamelCase(string $string) : string`**
  - **Purpose**: Transforms `kebab-case` or `snake_case` payloads into `camelCase`.
  - **Usage**: Essential for hydrating Validation DTOs from raw HTTP JSON bodies.

- **`generateUuidV7Fallback() : string`**
  - **Purpose**: Generates a time-ordered pseudo-UUIDv7.
  - **Under the Hood**: Standard UUIDv4 is completely random, which causes massive B-Tree fragmentation in SQL databases under high insert loads. UUIDv7 prefixes the string with a Unix timestamp, ensuring sequential clustering on the disk.

### 📂 `Types/Option.php`
The `Option` monad eliminates Null Pointer Exceptions (NPEs).

- **`some(mixed $value) : self`**
  - Wraps a known, existing value.
- **`none() : self`**
  - Explicitly declares the absence of a value.
- **`unwrap() : mixed`**
  - **WARNING**: Extracts the value. If the Option is `None`, this triggers a catastrophic AppError (Panic). Use only when you have mathematically proven the value exists.
- **`unwrapOr(mixed $default) : mixed`**
  - Extracts the value, but provides a safe, deterministic fallback if `None` is encountered.

### 📂 `Types/Result.php`
The `Result` monad replaces `try/catch` blocks for business logic.

- **`ok(mixed $value) : self`**
  - Indicates success and carries the resulting data payload.
- **`err(mixed $error) : self`**
  - Indicates a domain failure (e.g., `InsufficientFunds`) and carries the error payload.
- **`unwrap() : mixed`**
  - Extracts the payload. **WARNING**: If the Result is an Error, this will Panic.
- **`isErr() : bool`**
  - Always check this in your CQRS Handlers. If `true`, short-circuit the execution and return the Error to the CommandBus to trigger a database rollback.
