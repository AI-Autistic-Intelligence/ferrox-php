---
id: adapters
title: Adapters Submodule
---

# Adapters Submodule

## 1. Overview (What does this do?)
The `Adapters` submodule within `ferrox-php-mailer` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Adapters` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying adapters logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Adapters` submodule:

### Path: `ferrox-php-mailer/src/Adapters/LogMailerAdapter.php`

#### Class / Interface: `LogMailerAdapter`
The `LogMailerAdapter` is responsible for enterprise-grade execution of operations within `ferrox-php-mailer/src/Adapters/LogMailerAdapter.php`.

- **`send(string $to, string $subject, string $htmlBody, array $cc = []) : bool`**
  - Executes the `send` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

