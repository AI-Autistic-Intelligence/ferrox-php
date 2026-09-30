<?php
namespace Ferrox\Utils\Pagination;

class PageResult
{
    public function __construct(
        public readonly array $data,
        public readonly int $totalItems,
        public readonly int $totalPages,
        public readonly int $currentPage
    ) {}
}
