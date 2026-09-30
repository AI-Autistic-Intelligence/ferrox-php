<?php
namespace Ferrox\Core\Http;

use Ferrox\Core\Container\Container;
use RuntimeException;

/**
 * The core HTTP request Pipeline.
 * Orchestrates Middlewares sequentially. Enforces global exception handling
 * and strictly prevents SQL errors or internal state from leaking to the outside world.
 */
class Pipeline implements RequestHandlerInterface
{
    private array $middlewares = [];
    private int $index = 0;
    
    /**
     * @param Container $container Dependency Injection container to resolve middlewares.
     * @param array $middlewares List of FQCNs of MiddlewareInterface implementations.
     * @param RequestHandlerInterface $fallbackHandler Executed if no middleware returns a response.
     */
    public function __construct(
        private Container $container,
        array $middlewares,
        private RequestHandlerInterface $fallbackHandler
    ) {
        $this->middlewares = $middlewares;
    }

    /**
     * Processes the HTTP Request through the Middleware chain.
     * Guaranteed to catch all internal exceptions and convert them into standard JSON ErrorResponses.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        try {
            if (!isset($this->middlewares[$this->index])) {
                return $this->fallbackHandler->handle($request);
            }

            $middlewareClass = $this->middlewares[$this->index];
            $middleware = $this->container->get($middlewareClass);

            if (!$middleware instanceof MiddlewareInterface) {
                throw new RuntimeException("Middleware {$middlewareClass} must implement MiddlewareInterface.");
            }

            $this->index++;

            return $middleware->process($request, $this);
        } catch (\Ferrox\Core\Errors\AppError $e) {
            // Handled application errors (400, 401, 403, 404, 413, etc)
            return \Ferrox\Core\Errors\ErrorResponse::fromAppError($e);
        } catch (\Throwable $e) {
            // 🔒 WSTG-INPV-005 Safeguard: Prevent raw DB/SQL syntax leakage
            // Any unhandled exception is caught, logged internally, and sanitized.
            error_log("CRITICAL: Unhandled Exception -> " . $e->getMessage());
            return \Ferrox\Core\Errors\ErrorResponse::internal('Internal Server Error');
        }
    }
}
