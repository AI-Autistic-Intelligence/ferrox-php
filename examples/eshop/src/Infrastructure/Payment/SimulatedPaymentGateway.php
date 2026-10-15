<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Payment;

use Ferrox\Examples\Eshop\Domain\Repository\PaymentGatewayInterface;
use Ferrox\Observability\Metrics\PrometheusRegistry;
use Ferrox\Utils\Types\Result;

final class SimulatedPaymentGateway implements PaymentGatewayInterface
{
    private bool $simulateFailure = false;
    private array $transactions = [];

    public function setSimulateFailure(bool $simulate): void
    {
        $this->simulateFailure = $simulate;
    }

    public function capture(string $orderId, int $amountCents, string $currency): Result
    {
        if ($this->simulateFailure) {
            PrometheusRegistry::increment('ferrox_payment_failures_total', [
                'currency' => $currency,
                'reason' => 'gateway_declined'
            ]);
            return Result::err("Payment Gateway Authorization Failed for order {$orderId}");
        }

        $txId = 'TX-' . bin2hex(random_bytes(8));
        $this->transactions[$txId] = [
            'orderId' => $orderId,
            'amountCents' => $amountCents,
            'currency' => $currency,
            'status' => 'CAPTURED',
            'createdAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
        ];

        PrometheusRegistry::increment('ferrox_payment_success_total', [
            'currency' => $currency
        ]);

        return Result::ok($txId);
    }

    public function refund(string $transactionId, int $amountCents): Result
    {
        if (!isset($this->transactions[$transactionId])) {
            return Result::err("Transaction {$transactionId} not found");
        }

        $this->transactions[$transactionId]['status'] = 'REFUNDED';
        PrometheusRegistry::increment('ferrox_payment_refunds_total', [
            'currency' => $this->transactions[$transactionId]['currency']
        ]);

        return Result::ok(true);
    }

    public function getTransaction(string $txId): ?array
    {
        return $this->transactions[$txId] ?? null;
    }
}
