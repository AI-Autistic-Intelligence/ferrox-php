---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-broadcasting` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-broadcasting/src/BroadcasterInterface.php`

#### Class / Interface: `to`
The `to` is responsible for enterprise-grade execution of operations within `ferrox-php-broadcasting/src/BroadcasterInterface.php`.

- *No explicitly defined methods found (or relies on inheritance/magic methods).*
#### Class / Interface: `BroadcasterInterface`
The `BroadcasterInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-broadcasting/src/BroadcasterInterface.php`.

- **`broadcast(string $channel, string $event, array $payload) : void`**
  - Broadcast a payload to a specific private or public channel.

