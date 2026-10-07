<?php
namespace Ferrox\Events\Outbox;

use Ferrox\Events\DomainEventInterface;

/**
 * Handles the persistence of DomainEvents into the same SQL transaction of the business logic.
 * Essential to guarantee zero data-loss in distributed microservices.
 */
interface OutboxStoreInterface
{
    public function store(DomainEventInterface $event): void;
    
    /**
     * Retrieves unpublished events for the background worker.
     */
    public function getUnpublishedEvents(int $limit = 50): array;
    
    public function markAsPublished(string $eventId): void;
}
