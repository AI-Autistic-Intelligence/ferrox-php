<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Model;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case AUTHORIZED = 'AUTHORIZED';
    case CAPTURED = 'CAPTURED';
    case REFUNDED = 'REFUNDED';
    case FAILED = 'FAILED';
}

final class Payment
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $orderId,
        public readonly int $amountCents,
        public readonly string $currency,
        private PaymentStatus $status = PaymentStatus::PENDING,
        private ?string $failureReason = null,
        private readonly \DateTimeImmutable $processedAt = new \DateTimeImmutable()
    ) {}

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function markCaptured(): void
    {
        $this->status = PaymentStatus::CAPTURED;
    }

    public function markRefunded(string $reason): void
    {
        $this->status = PaymentStatus::REFUNDED;
        $this->failureReason = $reason;
    }

    public function markFailed(string $reason): void
    {
        $this->status = PaymentStatus::FAILED;
        $this->failureReason = $reason;
    }
}
