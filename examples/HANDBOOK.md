# Enterprise E-Shop CQRS Saga - Deep Kernel-to-Userland Handbook

## 1. Executive Summary

This handbook documents the **Ferrox-PHP Enterprise E-Shop**, the definitive reference architecture for modern PHP.

For decades, PHP was constrained by the "Shared-Nothing" architecture. On every HTTP request, the Web Server (Apache/FPM) boots the Zend Engine, compiles the code into OpCodes, executes, and then entirely destroys the memory space. This prevents connection pooling, stateful websockets, and in-memory event buses. 

As a Senior Engineer, I architected this application to hijack the PHP lifecycle using **Swoole/FrankenPHP**. We elevate PHP into a persistent, resident-memory state, interfacing directly with the OS `epoll` / `kqueue` APIs. This E-Shop proves that PHP can handle distributed **Saga Patterns**, **CQRS**, and **ACID Transactions** with latency profiles rivaling Go or Node.js.

---

## 2. Low-Level Architectural Blueprint

### 2.1 The Resident Memory Model & Coroutines
Instead of blocking OS threads while waiting for a database response, we utilize **Coroutines** (Fiber-based concurrency).
- **The Kernel Interaction**: When the `WarehouseRepository` sends a query to PostgreSQL, the Swoole engine suspends the PHP Coroutine at the C level. It registers the socket file descriptor with the Linux `epoll` reactor and yields the CPU back to the event loop. The OS kernel handles the TCP wait. When data arrives, the kernel interrupts the epoll loop, which immediately resumes the exact memory state of the Coroutine.
- **Memory Lifecycle**: Because the Zend Engine is not destroyed, global variables persist. We rigorously manage state using immutable DTOs and stateless Command Handlers to prevent memory leaks and cross-request data pollution.

### 2.2 CQRS & CPU Branch Prediction Optimization
We strictly separate Commands (Write operations) and Queries (Read operations).
- **Mechanical Sympathy**: CPUs rely on Branch Prediction and L1 Instruction caches. Monolithic "Fat Controllers" have wildly divergent execution paths depending on `if (isPost)` or `if (isGet)`. By physically separating the classes into `SubmitOrderCommand` and `GetProductQuery`, the CPU executes highly predictable, linear instruction paths. This drastically reduces CPU pipeline flushes (branch mispredictions), resulting in raw execution speed at the hardware level.

### 2.3 The Saga Pattern & Distributed Transactions
In a microservice or multi-database environment, standard `BEGIN...COMMIT` SQL locks cannot span across network boundaries without Two-Phase Commit (2PC), which causes catastrophic deadlocks.
- **Eventual Consistency**: We embrace the Saga pattern. If an order is created but the subsequent inventory decrement fails due to network partition, the Event Bus dispatches a compensating transaction (`OrderRefundedEvent`).
- **The Outbox Pattern**: To ensure events are never lost if the PHP process crashes mid-flight, events are written atomically to an Outbox table in the same ACID transaction as the Order. A background kernel thread polls the Outbox and dispatches the events to the message broker, guaranteeing "At-Least-Once" delivery semantics.

---

## 3. Programmer & DevOps Handbook

### 3.1 Advanced Deployment Configuration
Do not run this application under standard PHP-FPM.
```bash
# Dockerfile configuration for Swoole/Alpine
FROM phpswoole/swoole:8.3-alpine

# Tune the Swoole Event Loop
# worker_num should equal CPU cores
# max_coroutine determines the upper limit of simultaneous paused requests
CMD ["php", "examples/eshop_app.php", "--worker_num=8", "--max_coroutine=100000"]
```

### 3.2 DTO Validation at the AST Level
We utilize PHP 8 Attributes (`#[ValidatedDto(strict: true)]`). 
- **Reflection Caching**: PHP Reflection API is notoriously slow because it reads class metadata. We parse these attributes during the application bootstrap phase, compiling the validation rules into a fast-lookup Hash Map in resident memory. When a request hits, the payload is validated in `O(1)` time against the pre-compiled schema.

### 3.3 Memory Profiling & Garbage Collection
Because the application runs indefinitely:
- **Circular References**: Avoid them entirely. If an `Order` object references a `Product` and the `Product` references the `Order`, PHP's reference counter (`zval` refcount) will never hit zero. The Garbage Collector will have to run a cycle to clear it, which "stops the world."
- **Manual GC Toggles**: We explicitly disable cyclic garbage collection (`gc_disable()`) during high-traffic spikes and run it manually (`gc_collect_cycles()`) during OS idle ticks to ensure predictable 99th-percentile latency.

---

## 4. Senior Engineering Philosophy
This architecture dismantles the preconceived notions of what PHP can achieve. By mastering the interaction between the Zend Engine's `zval` memory structures and the Linux kernel's non-blocking socket APIs, we transformed a traditionally synchronous scripting language into a formidable, persistent application server. We implemented enterprise standards—Sagas, CQRS, and Outboxes—not as theoretical design patterns, but as structural necessities for operating at scale.
