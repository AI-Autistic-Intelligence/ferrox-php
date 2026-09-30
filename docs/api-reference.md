---
id: api-reference
title: API Reference
sidebar_position: 2
---

# Ferrox PHP - API Reference

*Auto-generated from PHPDoc.*

## `Ferrox\Core\App\FerroxApp`
The core Application Builder for the Ferrox PHP framework. Enforces strict security configurations, pipeline architectures, and adapter patterns (RoadRunner/Swoole). By design, an app cannot boot if it violates Zero-Trust boundaries.

### `build(): self`
Finalizes the application build process and verifies security invariants.
- **Throws**: `RuntimeException` If mandatory security pipelines or engines are missing.

---

## `Ferrox\Security\Sentinel\SentinelThreatEngineMiddleware`
Ferrox Layer 2: Sentinel Threat Engine. Evaluates incoming payloads for anomalies, such as high Shannon Entropy indicative of obfuscated shellcode, SQLi, or AI RAG poisoning attempts.

### `calculateEntropy(string $data): float`
Calculates the Shannon Entropy of a given string. Higher values (> 4.8) generally indicate compressed, encrypted, or highly obfuscated data.
- **Parameters**: 
  - `data` (string) - The raw payload string to analyze.
- **Returns**: `float` - The calculated entropy score.

---

## `Ferrox\Cqrs\CommandBus`
Core CQRS Command Bus. Responsible for routing Commands to their respective Handlers. In Ferrox, executing business logic directly in controllers is prohibited; all mutations must flow through this Bus to guarantee ACID transactions and Outbox publishing.

### `dispatch(CommandInterface $command): mixed`
Dispatches a Command to its registered Handler.
- **Parameters**:
  - `command` (`CommandInterface`) - The command to execute.
- **Returns**: `mixed` - The result of the handler's execution.
- **Throws**: `RuntimeException` - If no handler is registered or the handler is invalid.
