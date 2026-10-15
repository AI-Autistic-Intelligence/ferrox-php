<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Events;

final class OrderCreatedEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
        public readonly int $totalCents,
        public readonly string $source
    ) {
        parent::__construct();
    }

    public function getPayload(): array
    {
        return [
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'totalCents' => $this->totalCents,
            'source' => $this->source,
        ];
    }
}
