---
id: strings
title: Strings Submodule
---

# Strings Submodule

## 1. Overview (What does this do?)
The `Strings` submodule within `ferrox-php-utils` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Strings` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying strings logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Strings` submodule:

### Path: `ferrox-php-utils/src/Strings/StringUtils.php`

#### Class / Interface: `StringUtils`
The `StringUtils` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Strings/StringUtils.php`.

- **`toCamelCase(string $string) : string`**
  - Converts a kebab-case or snake_case string into camelCase. Essential for mapping incoming JSON payloads to PHP DTO properties.

- **`toSnakeCase(string $string) : string`**
  - Converts a camelCase string into snake_case. Required for translating PHP object properties to SQL database columns.

- **`toKebabCase(string $string) : string`**
  - Executes the `toKebabCase` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`mask(string $string, int $keepStart, int $keepEnd) : string`**
  - Executes the `mask` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`generateUuidV4() : string`**
  - Generates a standard UUID version 4. Uses cryptographically secure pseudo-random number generator.

- **`generateUuidV7Fallback() : string`**
  - Generates a time-ordered pseudo-UUIDv7. Ferrox Rust natively uses UUID v7 (time-ordered). In PHP, we simulate the sorting capability by appending a timestamp to the prefix. This prevents SQL index fragmentation on highly concurrent inserts.

