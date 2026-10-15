<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Events;

use Ferrox\Events\DomainEventInterface;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    private readonly string $eventId;
    private readonly \DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->eventId = bin2hex(random_bytes(16));
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getEventName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
