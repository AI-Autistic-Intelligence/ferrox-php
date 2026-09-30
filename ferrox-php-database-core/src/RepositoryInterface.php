<?php
namespace Ferrox\Database\Core;

use Ferrox\Utils\Types\PublicId;
use Ferrox\Utils\Pagination\PageRequest;
use Ferrox\Utils\Pagination\PageResult;

interface RepositoryInterface
{
    public function findById(PublicId|int|string $id): ?object;
    public function findAll(PageRequest $request): PageResult;
    public function save(object $entity): void;
    public function delete(object $entity): void;
}
