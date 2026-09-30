<?php
namespace Ferrox\Validation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ValidatedDto
{
    public function __construct(
        public bool $strict = true
    ) {}
}
