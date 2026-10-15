<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Events;

final class StockDepletedEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $productId,
        public readonly int $requestedQuantity
    ) {
        parent::__construct();
    }

    public function getPayload(): array
    {
        return [
            'productId' => $this->productId,
            'requestedQuantity' => $this->requestedQuantity,
        ];
    }
}
