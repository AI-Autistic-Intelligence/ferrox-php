<?php
namespace Ferrox\Events;

use RuntimeException;
use Ferrox\Core\Container\Container;
use Ferrox\Events\Outbox\OutboxStoreInterface;

/**
 * Enterprise Event Dispatcher implementing the 5S Methodology (Set in Order & Standardize).
 * Utilizes the Outbox pattern by default to guarantee Delivery (At-Least-Once).
 */
class EventDispatcher implements EventDispatcherInterface
{
    private array $listeners = [];

    public function __construct(
        private Container $container,
        private ?OutboxStoreInterface $outbox = null
    ) {}

    /**
     * Registers a listener for a specific Domain Event.
     */
    public function listen(string $eventClass, string $listenerClass): void
    {
        $this->listeners[$eventClass][] = $listenerClass;
    }

    /**
     * Dispatches an event. If an Outbox is configured, it safely persists the event
     * for asynchronous background processing (DRY and resilient).
     */
    public function dispatch(DomainEventInterface $event): void
    {
        // 1. Transactional Outbox Pattern (Seiketsu - Standardize Resilience)
        if ($this->outbox !== null) {
            $this->outbox->save($event);
            // In a real CQRS system, a separate worker process will read the Outbox
            // and dispatch the event. For this synchronous fallback, we just save it.
            return;
        }

        // 2. Synchronous Fallback (Not recommended for high-scale)
        $this->executeSync($event);
    }

    /**
     * Executes the listeners synchronously.
     */
    public function executeSync(DomainEventInterface $event): void
    {
        $eventClass = get_class($event);
        if (!isset($this->listeners[$eventClass])) {
            return;
        }

        foreach ($this->listeners[$eventClass] as $listenerClass) {
            $listener = $this->container->get($listenerClass);
            if (!method_exists($listener, 'handle')) {
                throw new RuntimeException("Listener {$listenerClass} must have a handle() method.");
            }
            $listener->handle($event);
        }
    }
}
