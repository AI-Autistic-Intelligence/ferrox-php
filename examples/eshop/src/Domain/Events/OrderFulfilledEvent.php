<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Events;

final class OrderFulfilledEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $warehouseId,
        public readonly string $paymentTransactionId,
        public readonly string $source
    ) {
        parent::__construct();
    }

    public function getPayload(): array
    {
        return [
            'orderId' => $this->orderId,
            'warehouseId' => $this->warehouseId,
            'paymentTransactionId' => $this->paymentTransactionId,
            'source' => $this->source,
        ];
    }
}
