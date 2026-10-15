<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\DTOs;

final class OrderResponseDto
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $status,
        public readonly string $source,
        public readonly int $totalCents,
        public readonly ?string $warehouseId = null,
        public readonly ?string $paymentTransactionId = null,
        public readonly ?string $failureReason = null
    ) {}

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'status' => $this->status,
            'source' => $this->source,
            'totalCents' => $this->totalCents,
            'warehouseId' => $this->warehouseId,
            'paymentTransactionId' => $this->paymentTransactionId,
            'failureReason' => $this->failureReason,
        ];
    }
}
