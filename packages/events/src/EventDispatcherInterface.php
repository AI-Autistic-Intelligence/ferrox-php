<?php
namespace Ferrox\Events;

interface EventDispatcherInterface
{
    /**
     * Dispatches an event to all registered synchronous listeners.
     */
    public function dispatch(DomainEventInterface $event): void;
}
