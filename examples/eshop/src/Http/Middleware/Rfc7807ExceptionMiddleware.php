<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Http\Middleware;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Throwable;

final class Rfc7807ExceptionMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (\DomainException $e) {
            return $this->buildProblemResponse(
                type: 'https://ferrox.dev/errors/domain-conflict',
                title: 'Domain Rule Conflict',
                status: 409,
                detail: $e->getMessage(),
                path: $request->uri ?? '/api'
            );
        } catch (\InvalidArgumentException $e) {
            return $this->buildProblemResponse(
                type: 'https://ferrox.dev/errors/bad-request',
                title: 'Bad Request Parameter',
                status: 400,
                detail: $e->getMessage(),
                path: $request->uri ?? '/api'
            );
        } catch (Throwable $e) {
            return $this->buildProblemResponse(
                type: 'https://ferrox.dev/errors/internal-server-error',
                title: 'Internal Server Error',
                status: 500,
                detail: $e->getMessage(),
                path: $request->uri ?? '/api'
            );
        }
    }

    private function buildProblemResponse(
        string $type,
        string $title,
        int $status,
        string $detail,
        string $path,
        array $invalidParams = []
    ): Response {
        $body = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $path,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        if (!empty($invalidParams)) {
            $body['invalidParams'] = $invalidParams;
        }

        return new Response(
            statusCode: $status,
            headers: [
                'Content-Type' => 'application/problem+json',
                'X-Content-Type-Options' => 'nosniff'
            ],
            body: $body
        );
    }
}
