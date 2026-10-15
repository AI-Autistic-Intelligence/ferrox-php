<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Queries;

final class GetOrderQuery
{
    public function __construct(
        public readonly string $orderId
    ) {}
}
