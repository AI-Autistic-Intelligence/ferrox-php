---
id: secrets
title: Secrets Submodule
---

# Secrets Submodule

## 1. Overview (What does this do?)
The `Secrets` submodule within `ferrox-php-utils` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Secrets` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying secrets logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Secrets` submodule:

### Path: `ferrox-php-utils/src/Secrets/AwsSecretManager.php`

#### Class / Interface: `AwsSecretManager`
The `AwsSecretManager` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Secrets/AwsSecretManager.php`.

- **`__construct(string $region = 'eu-west-1') : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`getSecret(string $secretName) : ?string`**
  - Executes the `getSecret` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-utils/src/Secrets/EnvSecretManager.php`

#### Class / Interface: `EnvSecretManager`
The `EnvSecretManager` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Secrets/EnvSecretManager.php`.

- **`getSecret(string $secretName) : ?string`**
  - Executes the `getSecret` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-utils/src/Secrets/GcpSecretManager.php`

#### Class / Interface: `GcpSecretManager`
The `GcpSecretManager` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Secrets/GcpSecretManager.php`.

- **`__construct(string $projectId) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`getSecret(string $secretName) : ?string`**
  - Executes the `getSecret` domain logic securely. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms.

### Path: `ferrox-php-utils/src/Secrets/SecretFactory.php`

#### Class / Interface: `SecretFactory`
The `SecretFactory` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Secrets/SecretFactory.php`.

- **`create() : SecretManagerInterface`**
  - Instantiates the appropriate SecretManager based on environment configuration. Uses ENV fallback if no specific cloud provider is selected.

### Path: `ferrox-php-utils/src/Secrets/SecretManagerInterface.php`

#### Class / Interface: `SecretManagerInterface`
The `SecretManagerInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Secrets/SecretManagerInterface.php`.

- **`getSecret(string $secretName) : ?string`**
  - Retrieves a secret by its name/key.

