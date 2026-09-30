---
id: concurrency
title: Concurrency Submodule
---

# Concurrency Submodule

## 1. Overview (What does this do?)
The `Concurrency` submodule within `ferrox-php-data` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Concurrency` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying concurrency logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Concurrency` submodule:

### Path: `ferrox-php-data/src/Concurrency/Singleflight.php`

#### Class / Interface: `Singleflight`
The `Singleflight` is responsible for enterprise-grade execution of operations within `ferrox-php-data/src/Concurrency/Singleflight.php`.

- **`work(string $key, Closure $callback) : mixed`**
  - @var array&lt;string, mixed&gt; Active requests currently in flight. / private array $flights = []; /** Executes the given closure. If multiple concurrent requests call this method with the same key, only the first one will execute the closure. The others will wait (in a real async environment like Swoole/Coroutine) and receive the same result, dramatically reducing database or API load.

