---
id: ferrox-php-security
title: Security & Threat Engine
sidebar_position: 5
---

# 🛡️ Ferrox PHP Security

## 1. Philosophy / Purpose
In the modern web, trusting user input is suicidal. The philosophy of this module is "Zero-Trust". Every single HTTP payload, header, and token is treated as hostile until mathematically proven otherwise. We don't just check for SQL Injection; we check the structural integrity and behavioral fingerprint of the request.

## 2. Architectural Layering
Security sits at Layer 2 (Guards/Sentinel) and Layer 3 (Authentication). It operates *before* the application logic (Layer 4) even knows a request exists. If a threat is detected, the request is terminated at the TCP/HTTP boundary.

## 3. Core Concepts (For Neophytes)
- **SentinelThreatEngine**: An AI-like bouncer at the door. If a user uploads a string that looks like scrambled alien text (shellcode), it blocks them.
- **PasetoAuthGuard**: The system that verifies "Who are you?". We strictly use PASETO tokens instead of the popular JWTs.

## 4. How it Works Under the Hood
The `SentinelThreatEngineMiddleware` implements a mathematical formula called Shannon Entropy (`calculateEntropy`). Standard English text has an entropy around 3.5 to 4.0. Base64 encoded payloads or encrypted shellcode (often used by hackers) spike over 4.8. If `$entropy > 4.8`, the middleware drops the connection.

## 5. Why it was designed this way
Why not JWT? JSON Web Tokens allow the attacker to manipulate the `alg` header (e.g., setting it to "none" or downgrading RS256 to HS256). PASETO (Platform-Agnostic Security Tokens) v4 physically separates local tokens (symmetric) from public tokens (asymmetric) at the protocol level, making cryptographic downgrade attacks impossible by design.

## 6. Anti-Patterns
- **❌ Accepting JWTs**: Never attempt to build a fallback parser for legacy JWTs. Force clients to upgrade to PASETO v4.
- **❌ Logging Raw Payloads**: If Sentinel blocks a highly entropic payload, do not log the raw payload into your standard logs, as it might trigger Log4j-style vulnerabilities or corrupt the log parsers.

## 7. Enterprise Usage (For Seniors)
In an Enterprise SaaS environment (e.g. processing Stripe webhooks or POS offline-syncs), you will encounter heavy traffic. The Security module integrates with `Singleflight` (Anti-Dogpiling). If an attacker attempts a Cache Stampede by hitting the `/login` endpoint 10,000 times a second for the same user, `Singleflight` forces 9,999 of those requests to wait in memory while only 1 hits the database, completely neutralizing the Layer 7 DDoS vector.
