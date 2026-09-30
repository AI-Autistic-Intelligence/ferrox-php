<?php
namespace Ferrox\Core\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RequireRole
{
    public readonly array $roles;

    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }
}
