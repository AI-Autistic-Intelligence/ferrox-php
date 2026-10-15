<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Http\Middleware;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;

final class CorrelationIdMiddleware implements MiddlewareInterface
{
    public const HEADER_NAME = 'X-Correlation-ID';

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $correlationId = $request->headers[self::HEADER_NAME] 
            ?? $request->headers[strtolower(self::HEADER_NAME)] 
            ?? ('corr_' . bin2hex(random_bytes(12)));

        $request->setAttribute('correlationId', $correlationId);

        $response = $handler->handle($request);
        $response->headers[self::HEADER_NAME] = $correlationId;

        return $response;
    }
}
