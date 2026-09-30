<?php
namespace Ferrox\Core\Http;

use Ferrox\Cqrs\CommandBus;
use Ferrox\Cqrs\CommandInterface;

/**
 * Base Controller for Ferrox (DRY Principle / 5S Methodology).
 * Standardizes API responses, abstracting away the boilerplate of wrapping CQRS results.
 */
abstract class AbstractController
{
    public function __construct(
        protected CommandBus $commandBus
    ) {}

    /**
     * Executes a CQRS command and returns a standardized JSON Response.
     */
    protected function execute(CommandInterface $command): Response
    {
        $result = $this->commandBus->dispatch($command);
        
        return new Response(200, [
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Standardized error response.
     */
    protected function error(string $message, int $statusCode = 400): Response
    {
        return new Response($statusCode, [
            'success' => false,
            'error' => $message,
        ]);
    }
}
