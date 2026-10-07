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
            throw AppError::unauthorized("Invalid token format. Ferrox strictly requires PASETO v4.local.");
        }

        $token = substr($authHeader, 7); // Remove 'Bearer '

        try {
            // Assumes paragonie/paseto is installed and key is provided via env
            $keyHex = \Ferrox\Utils\Env\EnvHelper::getOrThrow('PASETO_V4_LOCAL_KEY');
            $symmetricKey = \ParagonIE\Paseto\Keys\SymmetricKey::fromHex($keyHex);
            
            $parser = (new \ParagonIE\Paseto\Parser())
                ->setKey($symmetricKey)
                ->addRule(new \ParagonIE\Paseto\Rules\NotExpired())
                ->addRule(new \ParagonIE\Paseto\Rules\ValidAt());
            
            $parsedToken = $parser->parse($token);
            
            // Bind the authenticated user context to the request for subsequent layers
            $request->setAttribute('user_id', $parsedToken->getClaims()['sub'] ?? null);
            $request->setAttribute('user_roles', $parsedToken->getClaims()['roles'] ?? []);
            
        } catch (\ParagonIE\Paseto\Exception\PasetoException $e) {
            throw AppError::unauthorized("PASETO token validation failed: " . $e->getMessage());
        }
        
        return $handler->handle($request);
    }
}
