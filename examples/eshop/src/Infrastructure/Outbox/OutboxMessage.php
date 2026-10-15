<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Outbox;

use Ferrox\Events\DomainEventInterface;

final class OutboxMessage
{
    public readonly string $id;
    public readonly string $eventName;
    public readonly array $payload;
    public readonly string $createdAt;
    private bool $published = false;

    public function __construct(DomainEventInterface $event)
    {
        $this->id = $event->getEventId();
        $this->eventName = $event->getEventName();
        $this->payload = $event->getPayload();
        $this->createdAt = $event->getOccurredOn()->format(\DateTimeInterface::ATOM);
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function markPublished(): void
    {
        $this->published = true;
    }

    public function toJson(): string
    {
        return json_encode([
            'id' => $this->id,
            'eventName' => $this->eventName,
            'payload' => $this->payload,
            'createdAt' => $this->createdAt,
            'published' => $this->published
        ], JSON_THROW_ON_ERROR);
    }
}
