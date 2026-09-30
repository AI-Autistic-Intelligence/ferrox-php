---
id: dates
title: Dates Submodule
---

# Dates Submodule

## 1. Overview (What does this do?)
The `Dates` submodule within `ferrox-php-utils` encapsulates the localized logic required for enterprise-grade execution of this domain boundary.

## 2. Philosophy (Why does it exist?)
By isolating `Dates` into its own distinct submodule, Ferrox enforces the Single Responsibility Principle and guarantees that modifying dates logic will not inadvertently corrupt other decoupled systems.

## 3. API & Function Reference
Below is the highly detailed documentation extracted and inferred directly from the codebase for every path, class, and single function within the `Dates` submodule:

### Path: `ferrox-php-utils/src/Dates/DateUtils.php`

#### Class / Interface: `DateUtils`
The `DateUtils` is responsible for enterprise-grade execution of operations within `ferrox-php-utils/src/Dates/DateUtils.php`.

- **`nowUtc() : DateTime`**
  - Returns the current time strictly in UTC. All databases in Ferrox MUST store dates in UTC.

- **`formatIso8601(DateTime $date) : string`**
  - Formats a given DateTime object to ISO-8601 string representation. Used for JSON payload serialization.

- **`toGmtString(DateTime $date) : string`**
  - Converts a given UTC DateTime to a formatted GMT string. Required by Ferrox Rust standards for specific Frontend/Header compatibilities.

