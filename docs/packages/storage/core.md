---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-storage` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-storage/src/StorageFactory.php`

#### Class / Interface: `StorageFactory`
The `StorageFactory` is responsible for enterprise-grade execution of operations within `ferrox-php-storage/src/StorageFactory.php`.

- **`create() : StorageInterface`**
  - Executes the `create` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-storage/src/StorageInterface.php`

#### Class / Interface: `StorageInterface`
The `StorageInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-storage/src/StorageInterface.php`.

- **`put(string $path, string $contents, array $options = []) : string`**
  - Store a file and return its unique path/URI.

- **`get(string $path) : ?string`**
  - Retrieve a file's contents.

- **`delete(string $path) : bool`**
  - Delete a file.

- **`getPresignedUrl(string $path, int $expiresInSeconds = 3600) : string`**
  - Get a temporary/presigned secure URL for client download (Zero-Trust).

