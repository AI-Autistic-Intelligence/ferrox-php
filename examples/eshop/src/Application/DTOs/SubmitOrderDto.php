<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\DTOs;

use Ferrox\Validation\Attributes\ValidatedDto;

#[ValidatedDto(strict: true)]
final class SubmitOrderDto
{
    public string $productId;
    public int $quantity;
    public string $customerId;
    public string $source; // "ONLINE" | "POS" | "API"
    public int $unitPriceCents;
    public string $currency;

    public function __construct(
        string $productId = '',
        int $quantity = 1,
        string $customerId = 'CUST-ANON',
        string $source = 'ONLINE',
        int $unitPriceCents = 1999,
        string $currency = 'EUR'
    ) {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->customerId = $customerId;
        $this->source = $source;
        $this->unitPriceCents = $unitPriceCents;
        $this->currency = $currency;
    }
}
