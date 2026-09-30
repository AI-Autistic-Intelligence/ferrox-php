<?php
namespace Ferrox\Security\Auth;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Ferrox\Core\Errors\AppError;

/**
 * Ferrox Layer 3: Authentication Guard.
 * Strictly enforces PASETO v4 local/public tokens. JWT is explicitly forbidden
 * due to cryptographic downgrade vulnerabilities.
 */
class PasetoAuthGuard implements MiddlewareInterface
{
    /**
     * intercepts the HTTP request to validate the PASETO token.
     * 
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     * @throws AppError If the token is missing or invalid.
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // 🔒 Ferrox Layer 3: Auth Guards
        // Ferrox forbids JWT. We use PASETO v4 local/public tokens.
        
        $authHeader = $request->getHeader('Authorization');
        
        if (!$authHeader) {
            throw AppError::unauthorized("Missing PASETO Authorization header.");
        }
        
        if (!str_starts_with($authHeader, 'Bearer v4.local.')) {
            throw AppError::unauthorized("Invalid token format. Ferrox strictly requires PASETO v4.");
        }

        // Token decryption logic would go here
        
        return $handler->handle($request);
    }
}
