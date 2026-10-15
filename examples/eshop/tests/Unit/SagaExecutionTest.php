<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Tests\Unit;

use Ferrox\Examples\Eshop\Domain\Sagas\OrderFulfillmentSaga;
use Ferrox\Examples\Eshop\Domain\Sagas\SagaStepInterface;
use Ferrox\Utils\Types\Result;
use PHPUnit\Framework\TestCase;

final class SagaExecutionTest extends TestCase
{
    public function testSagaCompletesSuccessfullyWhenAllStepsPass(): void
    {
        $saga = new OrderFulfillmentSaga();
        $executed = [];

        $step1 = new class($executed) implements SagaStepInterface {
            public function __construct(private array &$trace) {}
            public function getName(): string { return 'Step1'; }
            public function execute(array &$context): Result {
                $this->trace[] = 'step1_exec';
                return Result::ok(true);
            }
            public function compensate(array &$context): Result {
                $this->trace[] = 'step1_comp';
                return Result::ok(true);
            }
        };

        $step2 = new class($executed) implements SagaStepInterface {
            public function __construct(private array &$trace) {}
            public function getName(): string { return 'Step2'; }
            public function execute(array &$context): Result {
                $this->trace[] = 'step2_exec';
                return Result::ok(true);
            }
            public function compensate(array &$context): Result {
                $this->trace[] = 'step2_comp';
                return Result::ok(true);
            }
        };

        $saga->addStep($step1)->addStep($step2);
        $result = $saga->execute([]);

        $this->assertTrue($result->isSuccess);
        $this->assertSame(['step1_exec', 'step2_exec'], $executed);
        $this->assertEmpty($result->compensatedSteps);
    }

    public function testSagaTriggersCompensationInReverseOrderOnFailure(): void
    {
        $saga = new OrderFulfillmentSaga();
        $trace = [];

        $step1 = new class($trace) implements SagaStepInterface {
            public function __construct(private array &$trace) {}
            public function getName(): string { return 'Step1'; }
            public function execute(array &$context): Result {
                $this->trace[] = 'step1_exec';
                return Result::ok(true);
            }
            public function compensate(array &$context): Result {
                $this->trace[] = 'step1_comp';
                return Result::ok(true);
            }
        };

        $step2 = new class($trace) implements SagaStepInterface {
            public function __construct(private array &$trace) {}
            public function getName(): string { return 'Step2'; }
            public function execute(array &$context): Result {
                $this->trace[] = 'step2_failed';
                return Result::err('Payment declined by card issuer');
            }
            public function compensate(array &$context): Result {
                $this->trace[] = 'step2_comp';
                return Result::ok(true);
            }
        };

        $saga->addStep($step1)->addStep($step2);
        $result = $saga->execute([]);

        $this->assertFalse($result->isSuccess);
        $this->assertSame('Payment declined by card issuer', $result->errorMessage);
        $this->assertSame(['step1_exec', 'step2_failed', 'step1_comp'], $trace);
        $this->assertSame(['Step1'], $result->compensatedSteps);
    }
}
