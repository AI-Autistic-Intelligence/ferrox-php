<?php
namespace Ferrox\Core\Http;

class Response
{
    public function __construct(
        public int $statusCode = 200,
        public array $headers = [],
        public array|string $body = ''
    ) {}

    public static function json(array $data, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'application/json'], $data);
    }

    public static function created(array $data = []): self
    {
        return self::json($data, 201);
    }
}
