<?php
namespace Ferrox\Utils\Pagination;

/**
 * Standardized Pagination Wrapper for API responses.
 * @template T
 */
readonly class PageResult
{
    public int $totalPages;

    /**
     * @param array<T> $items
     * @param int $total
     * @param int $page
     * @param int $limit
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit
    ) {
        $this->totalPages = (int) ceil($this->total / max(1, $this->limit));
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }
}
