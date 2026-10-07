<?php
namespace Ferrox\Cqrs;

use Ferrox\Core\Container\Container;
use RuntimeException;

class CommandBus
{
    private array $handlers = [];

    public function __construct(
        private Container $container
    ) {}

    public function registerHandler(string $commandClass, string $handlerClass): void
    {
        $this->handlers[$commandClass] = $handlerClass;
    }

    /**
     * Dispatches a Command to its registered Handler.
     * 
     * @param CommandInterface $command The command to execute.
     * @return mixed The result of the handler's execution.
     * @throws RuntimeException If no handler is registered or the handler is invalid.
     */
    public function dispatch(CommandInterface $command): mixed
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new RuntimeException("No handler registered for command: {$commandClass}");
        }

        $handlerClass = $this->handlers[$commandClass];
        $handler = $this->container->get($handlerClass);

        if (!method_exists($handler, 'handle')) {
            throw new RuntimeException("Handler {$handlerClass} must have a handle() method.");
        }

        return $handler->handle($command);
    }
}
