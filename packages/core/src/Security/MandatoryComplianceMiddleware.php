<?php
namespace Ferrox\Core\Security;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;

class MandatoryComplianceMiddleware implements MiddlewareInterface
{
    private const MAX_BODY_SIZE_BYTES = 2 * 1024 * 1024; // 2MB Hard Limit
    
    public function __construct(
        private array $allowedHosts = ['localhost', '127.0.0.1'] // Injected by Config in reality
    ) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // 🔒 1. Host Header Poisoning Check (WSTG-CONF-008)
        $host = $request->getHeader('Host') ?? '';
        $hostName = explode(':', $host)[0];
        
        if (!in_array($hostName, $this->allowedHosts, true)) {
            throw AppError::badRequest("Invalid Host header. Potential DNS Rebinding / Poisoning blocked.");
        }

        // 🔒 2. Model DoS / Resource Exhaustion Check (OWASP LLM04)
        // Ensures massive payloads don't satiate RAM or AI context windows
        $bodyLength = strlen(json_encode($request->body));
        if ($bodyLength > self::MAX_BODY_SIZE_BYTES) {
            throw new AppError("Payload Too Large. Exceeds Ferrox security threshold.", 413, 'PAYLOAD_TOO_LARGE');
        }

        // Execute the next layers of the Onion
        $response = $handler->handle($request);

        // Enforce Layer 1: Mandatory Security Headers
        $response->headers['Strict-Transport-Security'] = 'max-age=63072000; includeSubDomains; preload';
        $response->headers['Content-Security-Policy'] = "default-src 'none'; frame-ancestors 'none'; sandbox";
        $response->headers['X-Content-Type-Options'] = 'nosniff';
        $response->headers['X-Frame-Options'] = 'DENY';
        
        // Strip technology leaks (usually done at server level, but enforced here too)
        unset($response->headers['X-Powered-By']);
        unset($response->headers['Server']);

        return $response;
    }
}
