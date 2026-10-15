<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Commands;

use Ferrox\Cqrs\CommandInterface;
use Ferrox\Examples\Eshop\Application\DTOs\SubmitOrderDto;

final class SubmitOrderCommand implements CommandInterface
{
    public function __construct(
        public readonly SubmitOrderDto $dto
    ) {}
}
