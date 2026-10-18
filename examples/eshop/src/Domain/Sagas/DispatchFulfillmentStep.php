<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Sagas;

use Ferrox\Utils\Types\Result;

/**
 * Step 3: Validates compliance thresholds and initiates physical or digital dispatch.
 * On compensation, voids the dispatch instructions.
 */
final class DispatchFulfillmentStep implements SagaStepInterface
{
    private float $maxNotionalLimit;

    public function __construct(float $maxNotionalLimit = 5_000_000.0)
    {
        $this->maxNotionalLimit = $maxNotionalLimit;
    }

    public function getName(): string
    {
        return 'DispatchFulfillment';
    }

    public function execute(array &$context): Result
    {
        $price = (float) ($context['price'] ?? 0.0);
        $qty = (float) ($context['quantity'] ?? 0.0);
        $notional = $price * $qty;

        if (isset($context['simulate_risk_failure']) && $context['simulate_risk_failure'] === true) {
            $context['compensation_reason'] = 'RISK_COMPLIANCE_REJECTED';
            return Result::err('RISK_COMPLIANCE_REJECTED: Simulated risk rule violation triggered.');
        }

        if ($notional > $this->maxNotionalLimit) {
            $context['compensation_reason'] = "RISK_LIMIT_EXCEEDED: Notional \${$notional} exceeds maximum threshold \${$this->maxNotionalLimit}.";
            return Result::err($context['compensation_reason']);
        }

        $context['fulfillment_dispatched'] = true;
        $context['dispatch_tracking_id'] = 'trk-' . uniqid();

        return Result::ok(true);
    }

    public function compensate(array &$context): Result
    {
        $context['fulfillment_dispatched'] = false;
        $context['dispatch_voided'] = true;

        return Result::ok(true);
    }
}
