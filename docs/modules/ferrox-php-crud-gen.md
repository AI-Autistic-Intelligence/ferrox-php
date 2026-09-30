# FERROX-PHP-CRUD-GEN

## 1. Overview (What does this do?)
The `ferrox-php-crud-gen` component is an essential part of the robust backend development framework, centered around the 7-Layer Onion Request Pipeline. It adapts the stringent, enterprise-grade conventions established by the original Ferrox ecosystem specifically for PHP 8.3+. 
Automated CRUD generation engine utilizing reflection and attributes to avoid boilerplate code.

## 2. Philosophy (Why does it exist?)
The overarching philosophy of `ferrox-php-crud-gen` is zero-trust security and maximum decoupling. It exists to solve the common issue of unmaintainable, tightly coupled backend monoliths in standard PHP applications. By enforcing strict boundaries, it prevents developers from taking shortcuts that would compromise the system architecture.

## 3. Target Audience (Who is it for?)
This module is intended for backend engineers, system architects, and technical leads who are building large-scale Enterprise SaaS applications, Data Platforms, or intricate microservice ecosystems. It is meant for teams that prioritize long-term maintainability, strict typing, and robust security policies over "quick-and-dirty" prototyping.

## 4. Architecture (How does it work?)
`ferrox-php-crud-gen` integrates seamlessly into the Ferrox-PHP layered architecture. It relies on PHP 8.3+ features like readonly classes, Enums, and Attributes. Under the hood, this module uses highly optimized constructs to achieve O(1) runtime execution speed, operating precisely where it belongs in the strict Onion Architecture.

## 5. Installation / Setup
To get started with `ferrox-php-crud-gen`, ensure you have a PHP 8.3+ environment.
```bash
composer require ferrox/ferrox-php-crud-gen
```
Ensure that no legacy middleware or global states bypass the built-in pipelines.

## 6. Quickstart (Usage)
Initializing the foundational structure for `ferrox-php-crud-gen` is straightforward:
```php
use Ferrox\CrudGen\ExampleComponent;

// Typical enterprise usage involves registering it in the core Container
$container->singleton(ExampleComponent::class, fn() => new ExampleComponent());
```

## 7. Ecosystem Integration
The concepts described in this architectural overview integrate closely with every other component in the ferrox-php ecosystem. `ferrox-php-crud-gen` interacts natively with the Dependency Injection container, the Singleflight components, and the Security guards to form a highly resilient application core.

## 8. Path & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within `ferrox-php-crud-gen`:

### Path: `ferrox-php-crud-gen/src/CrudGenerator.php`

#### Class / Interface: `CrudGenerator`
The `CrudGenerator` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-crud-gen/src/CrudGenerator.php`.

- **`generateRoutesForEntity(Container $container, string $entityClass) : void`**
  - In a real application, this scans the `src/Entities` directory, reads #[CrudResource] attributes, and dynamically registers CQRS Handlers and Http Routes into the Container and Pipeline.

### Path: `ferrox-php-crud-gen/src/Attributes/CrudResource.php`

#### Class / Interface: `automatically`
The `automatically` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-crud-gen/src/Attributes/CrudResource.php`.

- *No explicitly defined methods found (or relies on inheritance/magic methods).*
#### Class / Interface: `CrudResource`
The `CrudResource` class provides the architectural boundary and core abstractions mapped directly to `ferrox-php-crud-gen/src/Attributes/CrudResource.php`.

- **`__construct(public readonly string $basePath, public readonly array $allowedRoles = ['ADMIN'], // Default to strictly internal public readonly bool $publishEvents = true // Emit "Created", "Updated" Domain Events automatically) : mixed`**
  - Constructs the object via strict Dependency Injection, enforcing structural immutability.

