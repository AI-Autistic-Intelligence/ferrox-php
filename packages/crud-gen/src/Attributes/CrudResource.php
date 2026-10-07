<?php
namespace Ferrox\CrudGen\Attributes;

use Attribute;

/**
 * Ferrox Metaprogramming Attribute.
 * Applying this to a domain entity class automatically instructs the CrudGenerator
 * to wire up a highly-secure CQRS pipeline (Create/Update/Delete Commands, Get/List Queries),
 * complete with Validation, Role Guards, and Outbox Event dispatching.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CrudResource
{
    public function __construct(
        public readonly string $basePath,
        public readonly array $allowedRoles = ['ADMIN'], // Default to strictly internal
        public readonly bool $publishEvents = true // Emit "Created", "Updated" Domain Events automatically
    ) {}
}
