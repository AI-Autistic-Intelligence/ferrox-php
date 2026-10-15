<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Tests\Unit;

use Ferrox\Examples\Eshop\Domain\Model\Order;
use Ferrox\Examples\Eshop\Domain\Model\OrderItem;
use Ferrox\Examples\Eshop\Domain\Model\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderDomainTest extends TestCase
{
    public function testOrderLifecycleAndCalculation(): void
    {
        $order = new Order(
            id: 'ORD-TEST-001',
            customerId: 'CUST-1',
            source: 'ONLINE'
        );

        $this->assertSame(OrderStatus::PENDING, $order->getStatus());
        $this->assertSame(0, $order->getTotalCents());

        $order->addItem(new OrderItem('PROD-A', 'Product Alpha', 2, 1500));
        $order->addItem(new OrderItem('PROD-B', 'Product Beta', 1, 2000));

        $this->assertSame(5000, $order->getTotalCents());
        $this->assertCount(2, $order->getItems());

        $order->markReserved('WH-MILAN');
        $this->assertSame(OrderStatus::RESERVED, $order->getStatus());
        $this->assertSame('WH-MILAN', $order->getAssignedWarehouseId());

        $order->markFulfilled('TX-999');
        $this->assertSame(OrderStatus::FULFILLED, $order->getStatus());
        $this->assertSame('TX-999', $order->getPaymentTransactionId());
    }

    public function testOrderCannotBeFulfilledWithoutBeingReserved(): void
    {
        $this->expectException(\DomainException::class);

        $order = new Order('ORD-ERR-001', 'CUST-2', 'POS');
        $order->markFulfilled('TX-FAIL');
    }
}
