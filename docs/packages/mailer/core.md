---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-mailer` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-mailer/src/MailerFactory.php`

#### Class / Interface: `MailerFactory`
The `MailerFactory` is responsible for enterprise-grade execution of operations within `ferrox-php-mailer/src/MailerFactory.php`.

- **`create() : MailerInterface`**
  - Executes the `create` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-mailer/src/MailerInterface.php`

#### Class / Interface: `MailerInterface`
The `MailerInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-mailer/src/MailerInterface.php`.

- **`send(string $to, string $subject, string $htmlBody, array $cc = []) : bool`**
  - Send an email.

