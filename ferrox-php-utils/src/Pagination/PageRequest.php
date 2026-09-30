<?php
namespace Ferrox\Utils\Pagination;

class PageRequest
{
    public function __construct(
        public readonly int $limit = 20,
        public readonly int $offset = 0,
        public readonly ?string $sortBy = null,
        public readonly string $sortDirection = 'ASC'
    ) {
        if ($this->limit <= 0) {
            throw new \InvalidArgumentException("Limit must be strictly greater than 0");
        }
        if ($this->limit > 100) {
            throw new \InvalidArgumentException("Limit cannot exceed 100");
        }
        if ($this->offset < 0) {
            throw new \InvalidArgumentException("Offset cannot be negative");
        }
    }
}
