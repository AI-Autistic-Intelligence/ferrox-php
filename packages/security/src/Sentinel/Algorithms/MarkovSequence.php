<?php

namespace Ferrox\Security\Sentinel\Algorithms;

class MarkovSequenceDetector
{
    private array $transitionMatrix;

    public function __construct(array $transitionMatrix)
    {
        $this->transitionMatrix = $transitionMatrix;
    }

    /**
     * Calculates the probability of a given sequence of actions.
     */
    public function scoreSequence(array $sequence): float
    {
        if (count($sequence) < 2) {
            return 1.0;
        }

        $probability = 1.0;
        $len = count($sequence);

        for ($i = 0; $i < $len - 1; $i++) {
            $currentState = $sequence[$i];
            $nextState = $sequence[$i + 1];

            $transitionProb = $this->transitionMatrix[$currentState][$nextState] ?? 0.01;
            $probability *= $transitionProb;
        }

        return $probability;
    }

    public function isAnomalous(array $sequence, float $threshold = 0.001): bool
    {
        return $this->scoreSequence($sequence) < $threshold;
    }
}
