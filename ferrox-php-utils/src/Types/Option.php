<?php
namespace Ferrox\Utils\Types;

use RuntimeException;

/**
 * Functional Option Monad inspired by Rust's Option<T>.
 * Safely handles the presence or absence of a value, eradicating NullPointerExceptions.
 *
 * @template T
 */
readonly class Option
{
    private function __construct(
        public readonly bool $isSome,
        public readonly mixed $value = null
    ) {}

    /**
     * @param T $value
     * @return self<T>
     */
    public static function some(mixed $value): self
    {
        return new self(true, $value);
    }

    /**
     * @return self<T>
     */
    public static function none(): self
    {
        return new self(false);
    }

    public function isSome(): bool
    {
        return $this->isSome;
    }

    public function isNone(): bool
    {
        return !$this->isSome;
    }

    /**
     * @return T
     * @throws RuntimeException
     */
    public function unwrap(): mixed
    {
        if ($this->isNone()) {
            throw new RuntimeException("Called unwrap() on a None value");
        }
        return $this->value;
    }

    /**
     * @param T $default
     * @return T
     */
    public function unwrapOr(mixed $default): mixed
    {
        return $this->isSome ? $this->value : $default;
    }
}
