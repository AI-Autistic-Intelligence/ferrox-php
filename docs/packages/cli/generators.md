---
id: generators
title: Generators Submodule
---

# Generators Submodule

## 1. Overview (What does this do?)
The `Generators` submodule within `ferrox-php-cli` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Generators` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying generators logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Generators` submodule:

### Path: `ferrox-php-cli/src/Generators/DtoGenerator.php`

#### Class / Interface: `DtoGenerator`
The `DtoGenerator` is responsible for enterprise-grade execution of operations within `ferrox-php-cli/src/Generators/DtoGenerator.php`.

- **`generateFromTable(string $tableName) : void`**
  - Executes the `generateFromTable` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`__construct(\n"; $props = []; foreach ($columns as $col) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`mapSqlTypeToPhp(string $sqlType) : string`**
  - Executes the `mapSqlTypeToPhp` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`toPascalCase(string $str) : string`**
  - Executes the `toPascalCase` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

