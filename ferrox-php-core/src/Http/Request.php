<?php
namespace Ferrox\Core\Http;

class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $headers = [],
        public readonly array $body = []
    ) {}

    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        return null;
    }
}
