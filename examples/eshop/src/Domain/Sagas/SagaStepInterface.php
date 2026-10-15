<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

use Ferrox\Utils\Types\Result;

interface SagaStepInterface
{
    public function getName(): string;

    /**
     * Executes the forward action of the step.
     */
    public function execute(array &$context): Result;

    /**
     * Executes the compensating rollback action if a downstream step fails.
     */
    public function compensate(array &$context): Result;
}
