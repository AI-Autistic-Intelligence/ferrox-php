<?php
namespace Ferrox\Events;

interface DomainEventInterface
{
    public function getEventId(): string;
    public function getOccurredOn(): \DateTimeImmutable;
    public function getEventName(): string;
    
    /**
     * Serializes the event payload for Outbox storage or Message Brokers (Redis/RabbitMQ).
     */
    public function getPayload(): array;
}
