---
id: ferrox-php-core
title: Core Engine (Pipeline & DI)
sidebar_position: 2
---

# ⚙️ Ferrox PHP Core

## 1. Philosophy / Purpose
The Core Engine is the beating heart of the Ferrox PHP framework. Even in its most basic form, a web application needs to start up, listen for HTTP requests, and route them. The philosophy here is "Fail-Safe Execution". Unlike standard PHP where a missing variable might trigger a Warning and continue, Ferrox Core immediately shuts down at boot time if it detects architectural violations. It is designed to be indestructible.

## 2. Architectural Layering
In the 7-Layer Onion Architecture, the Core Engine sits at the very edge (Layer 1: Entrypoint) and at the very center (Dependency Injection). It wraps the entire application in a protective HTTP Pipeline and orchestrates the creation of all other layers.

## 3. Core Concepts (For Neophytes)
If you are new to Ferrox, think of `FerroxApp` as your building block. You tell it: "Here are my security middlewares, here is my router, now start!".
- **`FerroxApp::builder()`**: Creates the app.
- **`addPipeline(array $middlewares)`**: Adds a list of security checks (like an airport security line) that every request must pass through before hitting your logic.

## 4. How it Works Under the Hood
When you call `build()`, the Core Engine doesn't just start listening. It dynamically constructs a **Chain of Responsibility**. It injects the `Container` (PSR-11 compliant) into the memory space. If it runs under RoadRunner (a Golang web server), it pre-warms all Singleton instances so that subsequent HTTP requests don't have to re-instantiate heavy objects like Database Connections.

## 5. Why it was designed this way
Traditional PHP creates and destroys the entire application on every single HTTP request (Shared-Nothing architecture). This is incredibly slow for Enterprise. By designing a Builder that pre-warms a DI Container, we can achieve Rust/Go-like performance (tens of thousands of requests per second) while writing PHP.

## 6. Anti-Patterns
- **❌ Binding Singletons for User Data**: Never bind user-specific data (like `CurrentUser`) as a Singleton in the `Container`. Since the app stays in memory, User A's data will bleed into User B's request.
- **❌ Bypassing the Pipeline**: Never echo output directly using `echo` or `die()`. It breaks the HTTP Pipeline and the JSON response standard.

## 7. Enterprise Usage (For Seniors)
For advanced deployments, the Core engine provides strict integration with OpenTelemetry and the `WSTG-INPV-005` standard. The `Pipeline::handle()` method automatically sanitizes uncaught exceptions (e.g. `PDOException`) into sterile `ErrorResponse` objects to prevent database schema leaks to attackers. You can map custom Monolog channels via the `Container` using `bind()` to stream structured logs directly to DataDog or ELK.
