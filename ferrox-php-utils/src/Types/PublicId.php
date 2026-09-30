<?php
namespace Ferrox\Utils\Types;

use Ferrox\Utils\Strings\StringUtils;

/**
 * Type-safe string wrapper used to represent a Public ID across the domain layer,
 * preventing accidental mixups with auto-incremented database IDs.
 */
readonly class PublicId
{
    public function __construct(
        public string $value
    ) {}

    public static function generate(): self
    {
        return new self(StringUtils::generateUuidV4());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
