<?php
namespace Ferrox\Utils\Types;

use RuntimeException;

/**
 * Functional Result Monad inspired by Rust's Result<T, E>.
 * Forces explicit error handling instead of relying on unpredictable try/catch blocks.
 *
 * @template T
 * @template E
 */
readonly class Result
{
    private function __construct(
        public readonly bool $isOk,
        public readonly mixed $value = null,
        public readonly mixed $error = null
    ) {}

    /**
     * @param T $value
     * @return self<T, E>
     */
    public static function ok(mixed $value = null): self
    {
        return new self(true, value: $value);
    }

    /**
     * @param E $error
     * @return self<T, E>
     */
    public static function err(mixed $error): self
    {
        return new self(false, error: $error);
    }

    public function isOk(): bool
    {
        return $this->isOk;
    }

    public function isErr(): bool
    {
        return !$this->isOk;
    }

    /**
     * @return T
     * @throws RuntimeException
     */
    public function unwrap(): mixed
    {
        if ($this->isErr()) {
            throw new RuntimeException("Called unwrap() on an Err value: " . json_encode($this->error));
        }
        return $this->value;
    }
    
    /**
     * @return E
     * @throws RuntimeException
     */
    public function unwrapErr(): mixed
    {
        if ($this->isOk()) {
            throw new RuntimeException("Called unwrapErr() on an Ok value");
        }
        return $this->error;
    }

    /**
     * @param T $default
     * @return T
     */
    public function unwrapOr(mixed $default): mixed
    {
        return $this->isOk ? $this->value : $default;
    }
}
