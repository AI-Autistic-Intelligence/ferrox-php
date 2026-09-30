---
id: types
title: Types Submodule
---

# Types Submodule

## 1. Overview (What does this do?)
The `Types` submodule within `ferrox-php-utils` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Types` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying types logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Types` submodule:

### Path: `ferrox-php-utils/src/Types/Option.php`

#### Class / Interface: `Option`
The `Option` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Types/Option.php`.

- **`__construct(public readonly bool $isSome, public readonly mixed $value = null) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`some(mixed $value) : self`**
  - Wraps a non-null value in an Option monad, explicitly defining the presence of data.

- **`none() : self`**
  - Creates an empty Option monad, explicitly defining the absence of data without relying on ambiguous nulls.

- **`isSome() : bool`**
  - Evaluates if the Option contains a valid value.

- **`isNone() : bool`**
  - Evaluates if the Option is empty (None state).

- **`unwrap() : mixed`**
  - Extracts the contained value. Panics if the Option is None, enforcing strict null-safety.

- **`unwrapOr(mixed $default) : mixed`**
  - Extracts the contained value, or gracefully falls back to the provided default if the Option is None.

### Path: `ferrox-php-utils/src/Types/PublicId.php`

#### Class / Interface: `PublicId`
The `PublicId` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Types/PublicId.php`.

- **`__construct(public string $value) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`generate() : self`**
  - Generates a cryptographically secure, URL-safe Public ID used for external referencing to prevent exposing internal auto-incrementing integer IDs.

- **`__toString() : string`**
  - Casts the Public ID object to its string representation securely.

### Path: `ferrox-php-utils/src/Types/Result.php`

#### Class / Interface: `Result`
The `Result` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Types/Result.php`.

- **`__construct(public readonly bool $isOk, public readonly mixed $value = null, public readonly mixed $error = null) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`ok(mixed $value = null) : self`**
  - Creates a successful Result monad containing the provided value, indicating the operation completed without domain violations.

- **`err(mixed $error) : self`**
  - Creates an error Result monad encapsulating the failure state, avoiding the memory leak risks of throwing exceptions.

- **`isOk() : bool`**
  - Safely asserts if the monad represents a successful state, preventing unhandled runtime panics.

- **`isErr() : bool`**
  - Safely asserts if the monad represents an error state, allowing for explicit error handling pipelines.

- **`unwrap() : mixed`**
  - Extracts the underlying value if successful. Panics (throws a fatal application error) if the Result is an Error, strictly enforcing Railway Oriented Programming.

- **`unwrapErr() : mixed`**
  - Extracts the underlying error if it exists. Panics if the Result is OK.

- **`unwrapOr(mixed $default) : mixed`**
  - Extracts the underlying value if successful, or returns the provided default fallback value, guaranteeing a deterministic state.

