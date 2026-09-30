---
id: sentinel
title: Sentinel Submodule
---

# Sentinel Submodule

## 1. Overview (What does this do?)
The `Sentinel` submodule within `ferrox-php-security` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Sentinel` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying sentinel logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Sentinel` submodule:

### Path: `ferrox-php-security/src/Sentinel/SentinelThreatEngineMiddleware.php`

#### Class / Interface: `SentinelThreatEngineMiddleware`
The `SentinelThreatEngineMiddleware` is responsible for enterprise-grade execution of operations within `ferrox-php-security/src/Sentinel/SentinelThreatEngineMiddleware.php`.

- **`process(Request $request, RequestHandlerInterface $handler) : Response`**
  - Processes the request through the heuristic engine before it reaches validation.

- **`flagThreat(string $reason) : void`**
  - Executes the `flagThreat` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`scanForCodeInjection(string $payload) : void`**
  - Executes the `scanForCodeInjection` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`scanForPromptInjection(string $payload) : void`**
  - Executes the `scanForPromptInjection` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`scanForPathTraversal(string $uri) : void`**
  - Executes the `scanForPathTraversal` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`calculateEntropy(string $data) : float`**
  - Calculates the Shannon Entropy of a given string. Higher values (&gt; 4.9) generally indicate compressed, encrypted, or highly obfuscated data.

