---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-rpc` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-rpc/src/RpcClientInterface.php`

#### Class / Interface: `RpcClientInterface`
The `RpcClientInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-rpc/src/RpcClientInterface.php`.

- **`call(string $serviceName, string $endpoint, array $payload) : array`**
  - Call an internal microservice method.

