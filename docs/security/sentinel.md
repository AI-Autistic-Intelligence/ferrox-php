---
id: sentinel
title: Sentinel Threat Engine
sidebar_position: 1
---

# 🛡️ Sentinel Threat Engine

## 1. Philosophy / Purpose
In a Zero-Trust architecture, input validation (checking if an email is valid) is not enough. Hackers use polymorphic shellcode, SQLi obfuscation, and AI RAG (Retrieval-Augmented Generation) poisoning payloads that bypass standard regex filters. The Sentinel Threat Engine is a heuristic, mathematics-based firewall embedded directly into the PHP application layer.

## 2. Architectural Layering
Sentinel sits at **Layer 2 (Guards)**. It operates immediately after the HTTP connection is accepted, but *before* the JSON payload is decoded or routed to a Controller. If Sentinel trips, the payload never touches the Application Layer.

## 3. Core Concepts (For Neophytes)
Imagine someone tries to submit this as their username: `\x90\x90\x90\x90\xeb\x19\x31\xc0`.
Standard validation might just see it as a string of characters. Sentinel looks at the "randomness" or "chaos" of the string. Normal human text is predictable; encrypted shellcode is highly chaotic. Sentinel blocks chaos.

## 4. How it Works Under the Hood
The `SentinelThreatEngineMiddleware` reads the raw `$request->getBody()`. It executes the `calculateEntropy(string $data): float` method, which calculates the **Shannon Entropy** (Information Theory).

```php
private function calculateEntropy(string $data): float {
    $len = strlen($data);
    $frequencies = count_chars($data, 1);
    $entropy = 0.0;
    foreach ($frequencies as $count) {
        $p = $count / $len;
        $entropy -= $p * log($p, 2);
    }
    return $entropy;
}
```
If `$entropy > 4.8` (indicating high compression/encryption/obfuscation), it throws an `AppError::forbidden('Threat Detected')`.

## 5. Why it was designed this way
WAFs (Web Application Firewalls) like Cloudflare are great, but they operate at the edge and can be bypassed if an attacker finds the origin IP. By embedding a heuristic engine directly into the Ferrox runtime, the application defends itself autonomously, adhering to the "Secure by Default" manifesto of the Rust ecosystem.

## 6. Anti-Patterns
- **❌ Disabling Sentinel for File Uploads**: Base64 encoded files or multipart/form-data will naturally have high entropy. Instead of disabling Sentinel globally, configure route-specific exemptions for binary upload endpoints.
- **❌ Logging the Payload on Failure**: If a payload triggers Sentinel, **do not** log the raw payload using Monolog. The payload might contain terminal escape sequences (Log4j style) designed to exploit the log viewer. Log the IP and the entropy score only.

## 7. Enterprise Usage (For Seniors)
In high-security fashion ERPs, attackers may attempt to poison the AI catalog search by injecting highly dense, adversarial prompt-injection payloads disguised as product reviews. Sentinel's entropy check acts as a first line of defense against RAG Poisoning. For tuning, seniors can adjust the baseline entropy threshold dynamically via the `EnvHelper` based on the specific language of the tenant (e.g., Asian character sets inherently have different baseline entropies than Latin sets).
