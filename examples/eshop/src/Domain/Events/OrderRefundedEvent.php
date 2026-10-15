<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Events;

final class OrderRefundedEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $reason,
        public readonly string $source
    ) {
        parent::__construct();
    }

    public function getPayload(): array
    {
        return [
            'orderId' => $this->orderId,
            'reason' => $this->reason,
            'source' => $this->source,
        ];
    }
}
