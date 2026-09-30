<?php
namespace Ferrox\Core\Errors;

use Exception;

class AppError extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 500,
        public readonly ?string $errorCode = null,
        public readonly array $details = []
    ) {
        parent::__construct($message);
    }

    public static function badRequest(string $message, array $details = []): self
    {
        return new self($message, 400, 'BAD_REQUEST', $details);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self($message, 401, 'UNAUTHORIZED');
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, 403, 'FORBIDDEN');
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return new self($message, 404, 'NOT_FOUND');
    }
}
