<?php

namespace Ferrox\Security\Sentinel\Algorithms;

class AIGuardrails
{
    private float $blockThreshold;
    private array $forbiddenPatterns = [
        "ignore previous instructions",
        "system prompt",
        "you are a helpful assistant",
        "bypass safety filters"
    ];

    public function __construct(float $blockThreshold = 0.85)
    {
        $this->blockThreshold = $blockThreshold;
    }

    /**
     * Returns true if the payload is safe, false if it trips the guardrails.
     */
    public function checkPayload(string $textInput): bool
    {
        $textLower = strtolower($textInput);
        
        foreach ($this->forbiddenPatterns as $pattern) {
            if (strpos($textLower, $pattern) !== false) {
                return false;
            }
        }

        $riskScore = $this->computeRiskScore($textLower);
        return $riskScore < $this->blockThreshold;
    }

    private function computeRiskScore(string $text): float
    {
        if (strlen($text) > 5000) {
            return 0.9;
        }
        return 0.1;
    }
}
