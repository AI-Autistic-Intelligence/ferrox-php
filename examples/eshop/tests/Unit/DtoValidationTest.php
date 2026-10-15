<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Tests\Unit;

use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Ferrox\Examples\Eshop\Application\DTOs\SubmitOrderDto;
use Ferrox\Validation\ValidationPipe;
use PHPUnit\Framework\TestCase;

final class DtoValidationTest extends TestCase
{
    public function testValidDtoInstantiation(): void
    {
        $dto = new SubmitOrderDto(
            productId: 'PROD-100',
            quantity: 3,
            customerId: 'CUST-007',
            source: 'ONLINE',
            unitPriceCents: 2500,
            currency: 'EUR'
        );

        $this->assertSame('PROD-100', $dto->productId);
        $this->assertSame(3, $dto->quantity);
        $this->assertSame('ONLINE', $dto->source);
        $this->assertSame(2500, $dto->unitPriceCents);
    }
}
