---
id: core
title: Core Submodule
---

# Core Submodule

## 1. Overview (What does this do?)
The `Core` submodule within `ferrox-php-auth` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Core` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying core logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Core` submodule:

### Path: `ferrox-php-auth/src/IdentityService.php`

#### Class / Interface: `IdentityService`
The `IdentityService` is responsible for enterprise-grade execution of operations within `ferrox-php-auth/src/IdentityService.php`.

- **`__construct(private RepositoryInterface $userRepository, private PasetoEngine $pasetoEngine, private MailerInterface $mailer) : mixed`**
  - Initializes a new instance of the class, enforcing strict constructor Dependency Injection (IoC) to guarantee internal memory-safety and immutability.

- **`register(string $email, string $plainPassword, string $name) : string`**
  - Register a new user securely, hashing the password and sending a welcome email.

- **`requestPasswordReset(string $email) : void`**
  - Trigger Password Recovery Flow

- **`connectSso(SsoProviderInterface $provider, string $authCode) : string`**
  - Handle SSO (Single Sign-On) Connection

### Path: `ferrox-php-auth/src/SsoProviderInterface.php`

#### Class / Interface: `SsoProviderInterface`
The `SsoProviderInterface` is responsible for enterprise-grade execution of operations within `ferrox-php-auth/src/SsoProviderInterface.php`.

- **`exchangeCodeForProfile(string $code) : array`**
  - Returns the normalized User Profile by exchanging the OAuth/SAML code.

