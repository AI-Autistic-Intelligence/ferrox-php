<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

use Ferrox\Utils\Types\Result;

final class OrderFulfillmentSaga
{
    /** @var SagaStepInterface[] */
    private array $steps = [];

    public function addStep(SagaStepInterface $step): self
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * Executes all registered Saga steps forward.
     * On any failure, runs compensation handlers for already completed steps in reverse order.
     */
    public function execute(array $initialContext): SagaExecutionResult
    {
        $context = $initialContext;
        $completedSteps = [];

        foreach ($this->steps as $step) {
            $result = $step->execute($context);

            if ($result->isErr()) {
                $errorMessage = $result->unwrapErr();
                $compensatedSteps = $this->rollback($completedSteps, $context);

                return SagaExecutionResult::failure(
                    $errorMessage,
                    array_map(static fn (SagaStepInterface $s) => $s->getName(), $completedSteps),
                    $compensatedSteps,
                    $context
                );
            }

            $completedSteps[] = $step;
        }

        return SagaExecutionResult::success(
            array_map(static fn (SagaStepInterface $s) => $s->getName(), $completedSteps),
            $context
        );
    }

    /**
     * @param SagaStepInterface[] $completedSteps
     * @return string[] Compensated step names
     */
    private function rollback(array $completedSteps, array &$context): array
    {
        $compensatedNames = [];
        $reversed = array_reverse($completedSteps);

        foreach ($reversed as $step) {
            $compResult = $step->compensate($context);
            $compensatedNames[] = $step->getName();
            if ($compResult->isErr()) {
                error_log(sprintf(
                    "[SAGA COMPENSATION ERROR] Step %s failed to compensate: %s",
                    $step->getName(),
                    $compResult->unwrapErr()
                ));
            }
        }

        return $compensatedNames;
    }
}
