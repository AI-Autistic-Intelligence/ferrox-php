<?php
namespace Ferrox\Core\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class UseGuard
{
    public function __construct(
        public readonly string $guardClass
    ) {}
}
