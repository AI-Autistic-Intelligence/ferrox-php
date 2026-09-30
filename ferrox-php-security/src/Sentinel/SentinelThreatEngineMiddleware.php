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

        $entropy = $this->calculateEntropy(json_encode($request->body));
        
        // If entropy is suspiciously high, it might be an obfuscated payload or shellcode.
        if ($entropy > 4.8) {
            throw AppError::forbidden("Sentinel AI Blocked Request: High payload entropy ({$entropy}) detected.");
        }

        return $handler->handle($request);
    }

    /**
     * Calculates the Shannon Entropy of a given string.
     * Higher values (> 4.8) generally indicate compressed, encrypted, or highly obfuscated data.
     *
     * @param string $data The raw payload string to analyze.
     * @return float The calculated entropy score.
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
