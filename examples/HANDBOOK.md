# Ferrox-PHP Enterprise E-Shop - The Definitive Handbook

## 1. Executive Summary & Senior Engineering Vision

This handbook is the ultimate reference manual for the **Ferrox-PHP Enterprise E-Shop**.

Historically, PHP has been pigeonholed into simple, synchronous web scripting, suffering from the "Shared-Nothing" architecture. As a Senior Engineer, I architected this application to hijack the traditional PHP lifecycle using **Swoole/FrankenPHP**. By doing so, we elevate PHP into a persistent, memory-resident state capable of interfacing directly with the OS kernel's `epoll`/`kqueue` loops.

This application demonstrates distributed **CQRS**, **Saga Patterns**, **UnitOfWork ACID transactions**, and **Eventual Consistency**—proving that modern PHP can rival Go or Node.js in enterprise ecosystems.

---

## 2. The Domain Problem

In E-Commerce, placing an order requires orchestrating multiple bounded contexts: verifying stock across distributed warehouses, locking inventory, processing payments, and dispatching notifications. If one step fails after another has succeeded, the system will face data corruption (e.g., negative stock, phantom orders). Traditional relational locks block concurrent checkouts. We need a system that processes thousands of orders per second securely and reliably.

---

## 3. Low-Level Architecture & OS-Kernel Interactions

### 3.1 The Resident Memory Model & Coroutines
Under standard FPM, the Zend Engine complies, executes, and destroys memory on every request. 
- **The Kernel Hook**: We bypass this entirely. Swoole keeps the PHP application resident in RAM. When a query is sent to PostgreSQL, Swoole suspends the PHP Coroutine at the C level, registers the file descriptor with the Linux `epoll` reactor, and yields the CPU.
- **Zero Blocking**: The OS handles the TCP wait. When data returns, the `epoll` loop is interrupted, and the exact memory state of the Coroutine is resumed. We multiplex thousands of SQL connections without blocking OS threads.

### 3.2 CQRS & CPU Branch Prediction Optimization
We physically segregate Write operations (Commands) from Read operations (Queries).
- **Mechanical Sympathy**: CPUs heavily rely on Branch Prediction and L1 Instruction caches. A monolithic "Fat Controller" (`if isGet else if isPost`) poisons the CPU cache. By separating `SubmitOrderCommand` and `GetProductQuery`, the CPU pipelines highly predictable, linear instruction paths, slashing branch misprediction penalties at the silicon level.

---

## 4. Application Architecture & Distributed Patterns

### 4.1 Unit of Work & ACID Transactions
The `UnitOfWorkInterface` wraps database boundaries. When the `WarehouseRepository` and `OrderRepository` mutate state during checkout, it happens atomically. An exception triggers a guaranteed rollback, preventing orphaned records.

### 4.2 The Saga Pattern & Fallbacks
When dealing with distributed databases or third-party APIs (Stripe), simple SQL transactions fail. 
- **Compensating Transactions**: If stock is locked locally but the remote payment gateway fails, the Saga orchestrator dispatches a compensating transaction (e.g., `OrderRefundedEvent`) to revert the stock lock.

### 4.3 The Outbox Pattern
To prevent lost events during a kernel panic or power failure, domain events are saved to an `Outbox` table within the same DB transaction as the order. A background PHP thread polls this table and publishes to Kafka/RabbitMQ, ensuring **At-Least-Once** delivery.

---

## 5. Security & Validation

### 5.1 DTO Validation via PHP 8 Attributes
We utilize `#[ValidatedDto(strict: true)]` on payload objects like `SubmitOrderDto`.
- **AST/Reflection Caching**: Because the application is resident in memory, we parse the reflection metadata once at boot time and cache it in a Hash Map. Validations run in `O(1)` time during HTTP requests, avoiding the massive overhead of runtime reflection.

### 5.2 Protection against Memory Leaks
Because the process never dies, developers must break circular references. A cyclical dependency (`Order` -> `User` -> `Order`) will never reduce its `zval` refcount to zero. 
- **Manual GC Toggles**: We explicitly disable `gc_disable()` during request handling to prevent Stop-The-World sweeps, and call `gc_collect_cycles()` manually in background idle ticks.

---

## 6. Programmer's Guide (Developer Workflow)

### 6.1 Environment Setup
```bash
# 1. Install dependencies
composer install

# 2. Start the Swoole Server (Do not use built-in PHP server)
php examples/eshop_app.php
```

### 6.2 Adding a New CQRS Flow
1. **Define the Command**: Create `CancelOrderCommand.php` containing the DTO.
2. **Define the Handler**: Create `CancelOrderHandler.php`. Inject the `OrderRepository`. Return a Monadic `Ferrox\Utils\Types\Result`.
3. **Register**: Bind them in the `CommandBus` during bootstrap.

### 6.3 Using Monadic Error Handling
Never throw Exceptions for business logic (e.g., "Out of Stock").
```php
// GOOD
if ($stock < 0) return Result::err("Stock depleted");

// IN CONTROLLER
$result = $handler->handle($cmd);
if ($result->isErr()) return $this->error($result->unwrapErr(), 400);
```

---

## 7. User & DevOps Handbook (Operations)

### 7.1 Docker & Swoole Deployment
```dockerfile
FROM phpswoole/swoole:8.3-alpine
COPY . /app
WORKDIR /app
# worker_num should precisely match the physical CPU cores
# max_coroutine protects against OOM errors during traffic spikes
CMD ["php", "examples/eshop_app.php", "--worker_num=8", "--max_coroutine=100000"]
```
- **FPM is Forbidden**: Do not deploy this behind PHP-FPM or Apache `mod_php`. It requires a CLI runtime to maintain the event loop.

### 7.2 Native Metrics & Prometheus
The application avoids external agents by natively tracking metrics.
- Scrape `http://localhost:8000/metrics`.
- Key metrics: `ferrox_warehouse_stock`, `ferrox_orders_total`, `ferrox_critical_anomalies_total`.
- Setup Grafana alerts for `ferrox_critical_anomalies_total` to page DevOps immediately if a negative stock state occurs.

---

## 8. Senior Engineering Conclusion

This architecture completely dismantles the preconceived notions of what PHP can achieve. By understanding the interaction between the Zend Engine's internal memory structures and the Linux kernel's non-blocking socket APIs, we transformed a legacy scripting paradigm into a formidable, persistent application server. Sagas, CQRS, and Outboxes are implemented here not as theoretical exercises, but as structural necessities for operating securely and reliably at a massive scale.
