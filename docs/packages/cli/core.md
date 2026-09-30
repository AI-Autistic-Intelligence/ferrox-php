---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-cli` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-cli/src/CliApplication.php`

#### Class / Interface: `CliApplication`
The `CliApplication` is responsible for enterprise-grade execution of operations within `ferrox-php-cli/src/CliApplication.php`.

- **`run(array $argv) : void`**
  - Primary execution pipeline for this component. Processes the payload with O(1) isolation and returns a predictable output.

- **`showHelp() : void`**
  - Executes the `showHelp` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

#### Class / Interface: `from`
The `from` is responsible for enterprise-grade execution of operations within `ferrox-php-cli/src/CliApplication.php`.

- *No explicitly defined methods found (or relies on inheritance/magic methods).*
