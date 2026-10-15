<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\DTOs;

use Ferrox\Validation\Attributes\ValidatedDto;

#[ValidatedDto(strict: true)]
final class StockCheckDto
{
    public string $productId;
    public int $quantity;

    public function __construct(string $productId = '', int $quantity = 1)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }
}
