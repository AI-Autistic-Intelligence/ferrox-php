---
id: metrics
title: Metrics Submodule
---

# Metrics Submodule

## 1. Overview (What does this do?)
The `Metrics` submodule within `ferrox-php-observability` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Metrics` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying metrics logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Metrics` submodule:

### Path: `ferrox-php-observability/src/Metrics/PrometheusRegistry.php`

#### Class / Interface: `PrometheusRegistry`
The `PrometheusRegistry` is responsible for enterprise-grade execution of operations within `ferrox-php-observability/src/Metrics/PrometheusRegistry.php`.

- **`increment(string $name, array $labels = [], int $value = 1) : void`**
  - Increments a counter metric.

- **`setGauge(string $name, int|float $value, array $labels = []) : void`**
  - Sets an absolute value for a gauge metric (e.g. current stock, active users).

- **`export() : string`**
  - Exports all registered metrics in the standard Prometheus text format.

- **`formatKey(string $name, array $labels) : string`**
  - Executes the `formatKey` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`buildLabelString(array $labels) : string`**
  - Executes the `buildLabelString` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

