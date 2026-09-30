<?php
namespace Ferrox\Gateway;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Security\Paseto\PasetoEngine;
use RuntimeException;

/**
 * Edge Authentication Middleware.
 * Validates PASETO tokens at the Gateway level, offloading Auth from underlying microservices.
 */
class EdgeAuthMiddleware implements MiddlewareInterface
{
    private PasetoEngine $pasetoEngine;

    public function __construct(PasetoEngine $pasetoEngine)
    {
        $this->pasetoEngine = $pasetoEngine;
    }

    public function process(array $request, RequestHandlerInterface $handler): array
    {
        $authHeader = $request['headers']['authorization'] ?? null;
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new RuntimeException("401 Unauthorized at Edge Gateway");
        }

        $token = substr($authHeader, 7);
        $claims = $this->pasetoEngine->verifyToken($token);

        if (!$claims) {
            throw new RuntimeException("401 Invalid PASETO Token at Edge Gateway");
        }

        // Inject claims into request so downstream microservices know who the user is
        $request['headers']['x-ferrox-user-id'] = $claims['sub'];
        $request['headers']['x-ferrox-roles'] = implode(',', $claims['roles'] ?? []);

        return $handler->handle($request);
    }
}
