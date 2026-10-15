<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Events;

final class CriticalAnomalyEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $anomalyType,
        public readonly string $description,
        public readonly array $context = []
    ) {
        parent::__construct();
    }

    public function getPayload(): array
    {
        return [
            'anomalyType' => $this->anomalyType,
            'description' => $this->description,
            'context' => $this->context,
        ];
    }
}
