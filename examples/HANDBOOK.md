# Enterprise E-Shop Showcase - Programmer & User Handbook

## 1. Executive Summary

This handbook details the architecture, design choices, and operational requirements for the **Ferrox-PHP Enterprise E-Shop**, the official reference application for the PHP ecosystem.

As a Senior Engineer, I architected this application to definitively refute the stigma that PHP is purely for legacy synchronous scripting. This application proves that PHP, when structured correctly with **Ferrox-PHP**, is fully capable of handling advanced enterprise patterns like **CQRS**, **Saga Orchestrations**, **Domain-Driven Design (DDD)**, and **Distributed Acid Transactions**.

---

## 2. Architectural Blueprint

### 2.1 The Domain Problem
In a modern E-Commerce environment, handling order placements involves multiple systems: verifying stock across distributed warehouses, locking inventory, processing payments, and alerting staff. If any step fails, the system must rollback cleanly to prevent phantom orders or negative stock.

### 2.2 System Components
1. **CQRS (Command Query Responsibility Segregation)**
   - **Commands**: Operations that mutate state (e.g., `SubmitOrderCommand`). They do not return data, only `Result::ok()` or `Result::err()`.
   - **Queries**: Operations that read state. Completely separated from Commands to allow divergent scaling (e.g., caching Queries on Redis, executing Commands on Postgres Writer instances).
2. **ACID Unit of Work (`UnitOfWorkInterface`)**
   - Wraps database connections. Ensures that when `WarehouseRepository` and `OrderRepository` mutate data, they do so atomically. If an exception occurs, the entire transaction is rolled back.
3. **The Saga Pattern & Compensating Transactions**
   - Distributed systems cannot rely purely on DB locks. If a warehouse fails to lock stock *after* an order is created, the Saga orchestrator intercepts the failure and executes a compensatory `OrderRefundedEvent` to reverse the state.
4. **Domain Events & Outbox Pattern**
   - The `EventDispatcher` decouples core logic from side effects. For instance, sending an email via `MailerFactory` happens by listening to the `OrderRefundedEvent`, not by embedding mailer logic inside the Order repository.
5. **Data Transfer Objects (DTO) & PHP 8 Attributes**
   - `#[ValidatedDto(strict: true)]` ensures that incoming HTTP JSON payloads are strongly typed and validated (e.g., `SubmitOrderDto`) before they ever reach the Command Bus.
6. **Native Prometheus Observability**
   - `PrometheusRegistry` natively tracks `ferrox_warehouse_stock` and `ferrox_orders_total`.

---

## 3. Programmer Handbook (Developer Guide)

### 3.1 Environment Setup
```bash
# Require PHP 8.3+
composer install

# Boot the application locally using Swoole (for async/persistence)
php examples/eshop_app.php
```

### 3.2 Extending the Command Bus
To add a new feature (e.g., `CancelOrder`):
1. **Create the DTO**: Class `CancelOrderDto`.
2. **Create the Command**: Class `CancelOrderCommand implements CommandInterface`.
3. **Create the Handler**: Class `CancelOrderHandler`. Inject the required Repositories.
4. **Register**: `$bus->registerHandler(CancelOrderCommand::class, CancelOrderHandler::class);`

### 3.3 Adding Domain Events
Domain events should represent something that *has already happened* in the past tense.
- Example: `PaymentFailedEvent`.
- Register the listener:
  ```php
  $events->addListener(PaymentFailedEvent::class, function($event) {
      // Execute side effect (e.g., suspend user account)
  });
  ```

### 3.4 Monadic Error Handling
Avoid throwing `Exceptions` for expected business rule violations (e.g., "Insufficient Stock"). 
Instead, return `Ferrox\Utils\Types\Result`.
```php
if ($stock < $qty) {
    return Result::err("Insufficient Stock");
}
return Result::ok($orderId);
```
Exceptions should be reserved strictly for unpredictable infrastructure failures (e.g., Database Connection Lost).

---

## 4. User & DevOps Handbook (Operations)

### 4.1 Deployment Strategy
This application is designed to be run on **Swoole** or **FrankenPHP**, which keeps the PHP application resident in memory.
- **DO NOT** deploy this via standard PHP-FPM / Apache. Standard FPM kills the process after every request, defeating the purpose of connection pooling and in-memory Event Dispatchers.
- **Docker**: Build a lightweight Alpine container with the Swoole extension installed.

### 4.2 Scraping Metrics
The application exposes a `/metrics` endpoint on the HTTP Server.
Configure your Prometheus `scrape_config`:
```yaml
scrape_configs:
  - job_name: 'ferrox_php_eshop'
    static_configs:
      - targets: ['eshop-app:8000']
```
Set up Grafana alerts for the `ferrox_critical_anomalies_total` metric to page operations if negative stock anomalies occur.

---

## 5. Senior Engineering Decisions

1. **Why CQRS in PHP?** Monolithic MVC applications in PHP quickly become "Fat Controllers" or "Fat Models". CQRS forces single-responsibility. It isolates the complex write-logic (stock locking) from the high-throughput read-logic (product listing), allowing us to scale the database efficiently.
2. **Why Monadic Results?** `try/catch` blocks create hidden control flows. By forcing methods to return `Result<T, E>`, the PHP type system strictly enforces that the caller *must* handle the error case, completely eliminating unhandled domain exceptions.
3. **Why native Prometheus over external agents?** By incrementing metrics directly inside the Repositories and Handlers, we capture business-level telemetry (e.g., "how many orders were refunded due to stock-outs") rather than just CPU/Memory stats. This is crucial for business intelligence.
