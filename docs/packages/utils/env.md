---
id: env
title: Env Submodule
---

# Env Submodule

## 1. Overview (What does this do?)
The `Env` submodule within `ferrox-php-utils` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Env` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying env logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Env` submodule:

### Path: `ferrox-php-utils/src/Env/EnvHelper.php`

#### Class / Interface: `EnvHelper`
The `EnvHelper` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Env/EnvHelper.php`.

- **`get(string $key, $default = null) : mixed`**
  - Safely retrieves an environment variable or a default value.

- **`getOrThrow(string $key) : mixed`**
  - Strict requirement. Throws if missing.

