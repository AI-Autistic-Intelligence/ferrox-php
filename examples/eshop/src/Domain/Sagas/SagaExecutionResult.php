<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

final class SagaExecutionResult
{
    /**
     * @param string[] $completedSteps
     * @param string[] $compensatedSteps
     */
    public function __construct(
        public readonly bool $isSuccess,
        public readonly array $completedSteps,
        public readonly array $compensatedSteps,
        public readonly ?string $errorMessage = null,
        public readonly array $context = []
    ) {}

    public static function success(array $completedSteps, array $context): self
    {
        return new self(
            isSuccess: true,
            completedSteps: $completedSteps,
            compensatedSteps: [],
            errorMessage: null,
            context: $context
        );
    }

    public static function failure(
        string $errorMessage,
        array $completedSteps,
        array $compensatedSteps,
        array $context
    ): self {
        return new self(
            isSuccess: false,
            completedSteps: $completedSteps,
            compensatedSteps: $compensatedSteps,
            errorMessage: $errorMessage,
            context: $context
        );
    }
}
