<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Commands;

use Ferrox\Cqrs\CommandInterface;

final class CancelOrderCommand implements CommandInterface
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $reason
    ) {}
}
