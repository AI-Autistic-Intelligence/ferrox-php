<?php
namespace Ferrox\Utils\Pagination;

readonly class PageRequest
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?string $sortBy = null,
        public string $sortOrder = 'ASC'
    ) {
        if ($this->page < 1) {
            throw new \InvalidArgumentException("Page must be >= 1");
        }
        if ($this->limit < 1 || $this->limit > 1000) {
            throw new \InvalidArgumentException("Limit must be between 1 and 1000");
        }
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
