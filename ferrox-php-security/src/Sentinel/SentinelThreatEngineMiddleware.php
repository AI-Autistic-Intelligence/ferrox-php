<?php
namespace Ferrox\Security\Sentinel;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Ferrox\Core\Errors\AppError;

/**
 * Ferrox Layer 2: Sentinel Threat Engine.
 * Evaluates incoming payloads for anomalies, such as high Shannon Entropy
 * indicative of obfuscated shellcode, SQLi, or AI RAG poisoning attempts.
 */
class SentinelThreatEngineMiddleware implements MiddlewareInterface
{
    /**
     * Processes the request through the heuristic engine before it reaches validation.
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     * @throws AppError If the payload is classified as a threat.
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // 🔒 Ferrox Layer 2: Sentinel Threat Engine
        // Evaluates Shannon Entropy, checks for anomalies, SQLi/XSS/RAG poisoning attempts.

        $payloadString = json_encode($request->body) . " " . json_encode($request->query);

        // 1. Shannon Entropy Analysis (Obfuscated Shellcode detection)
        $entropy = $this->calculateEntropy($payloadString);
        if ($entropy > 4.9) {
            $this->flagThreat("High payload entropy ({$entropy}) detected - Possible obfuscated shellcode");
        }

        // 2. Advanced SQLi & XSS Heuristics
        $this->scanForCodeInjection($payloadString);

        // 3. AI / RAG Prompt Injection Poisoning (LLM Security)
        $this->scanForPromptInjection($payloadString);

        // 4. Directory Traversal / LFI
        $this->scanForPathTraversal($request->uri);

        return $handler->handle($request);
    }

    private function flagThreat(string $reason): void
    {
        error_log("[SENTINEL WAF] BLOCK: " . $reason);
        // Extension point: Forward telemetry to infrastructure layer (e.g. Fail2Ban / NGINX / Cloudflare) to drop subsequent requests.
        throw AppError::forbidden("Ferrox Sentinel WAF Blocked Request: Security violation detected.");
    }

    private function scanForCodeInjection(string $payload): void
    {
        $sqliPattern = '/(\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER)\b.*?\b(FROM|INTO|TABLE)\b)|(\'|%27).*?(--|#|\/\*)/i';
        $xssPattern = '/(<(?:script|iframe|img|svg|object|embed).*?(?:src|onload|onerror)=)|(javascript:|vbscript:|data:text\/html)/i';

        if (preg_match($sqliPattern, $payload)) {
            $this->flagThreat("SQL Injection Heuristic matched.");
        }
        if (preg_match($xssPattern, $payload)) {
            $this->flagThreat("XSS / Cross-Site Scripting Heuristic matched.");
        }
    }

    private function scanForPromptInjection(string $payload): void
    {
        $ragPoisoningPattern = '/(ignore previous instructions|disregard|system prompt|you are now|forget everything|bypass|jailbreak)/i';
        
        if (preg_match($ragPoisoningPattern, $payload)) {
            $this->flagThreat("AI/LLM Prompt Injection (RAG Poisoning) attempt detected.");
        }
    }

    private function scanForPathTraversal(string $uri): void
    {
        if (str_contains($uri, '../') || str_contains($uri, '..\\') || str_contains($uri, '/etc/passwd')) {
            $this->flagThreat("Path Traversal / Local File Inclusion attempt detected in URI.");
        }
    }

    /**
     * Calculates the Shannon Entropy of a given string.
     * Higher values (> 4.9) generally indicate compressed, encrypted, or highly obfuscated data.
     */
    private function calculateEntropy(string $data): float
    {
        if (empty($data)) return 0.0;
        
        $len = strlen($data);
        $frequencies = count_chars($data, 1);
        $entropy = 0;
        
        foreach ($frequencies as $count) {
            $p = $count / $len;
            $entropy -= $p * log($p, 2);
        }
        
        return $entropy;
    }
}
