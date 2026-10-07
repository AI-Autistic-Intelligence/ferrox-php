---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-scheduler` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-scheduler/src/Scheduler.php`

#### Class / Interface: `Scheduler`
The `Scheduler` is responsible for enterprise-grade execution of operations within `ferrox-php-scheduler/src/Scheduler.php`.

- **`runDueTasks() : void`**
  - @var Task[] */ private array $tasks = []; public function schedule(string $cronExpression, callable $callback): void \{ $this-&gt;tasks[] = new Task($cronExpression, $callback); \} /** Called every minute by the server (e.g. Swoole Timer or Crontab)

### Path: `ferrox-php-scheduler/src/Task.php`

#### Class / Interface: `Task`
The `Task` is responsible for enterprise-grade execution of operations within `ferrox-php-scheduler/src/Task.php`.

- **`__construct(string $cronExpression, callable $callback) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`isDue(\DateTimeImmutable $now) : bool`**
  - Executes the `isDue` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

- **`run() : void`**
  - Primary execution pipeline for this component. Processes the payload with O(1) isolation and returns a predictable output.

