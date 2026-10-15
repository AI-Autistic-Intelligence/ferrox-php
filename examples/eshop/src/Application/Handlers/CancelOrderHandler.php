<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Handlers;

use Ferrox\Events\EventDispatcher;
use Ferrox\Examples\Eshop\Application\Commands\CancelOrderCommand;
use Ferrox\Examples\Eshop\Domain\Events\OrderRefundedEvent;
use Ferrox\Examples\Eshop\Domain\Repository\PaymentGatewayInterface;
use Ferrox\Examples\Eshop\Infrastructure\Metrics\EshopMetrics;
use Ferrox\Examples\Eshop\Infrastructure\Outbox\RedisOutboxPublisher;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoOrderRepository;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoWarehouseRepository;
use Ferrox\Utils\Types\Result;

final class CancelOrderHandler
{
    public function __construct(
        private PdoOrderRepository $orderRepo,
        private PdoWarehouseRepository $warehouseRepo,
        private PaymentGatewayInterface $paymentGateway,
        private EventDispatcher $events,
        private RedisOutboxPublisher $outbox
    ) {}

    public function handle(CancelOrderCommand $cmd): Result
    {
        $orderOpt = $this->orderRepo->findOrderEntity($cmd->orderId);
        if ($orderOpt->isNone()) {
            return Result::err("Order {$cmd->orderId} not found");
        }

        $order = $orderOpt->unwrap();

        // Release inventory if assigned
        if ($order->getAssignedWarehouseId() !== null) {
            foreach ($order->getItems() as $item) {
                $this->warehouseRepo->incrementStock($order->getAssignedWarehouseId(), $item->productId, $item->quantity);
            }
        }

        // Refund payment if captured
        if ($order->getPaymentTransactionId() !== null) {
            $this->paymentGateway->refund($order->getPaymentTransactionId(), $order->getTotalCents());
        }

        $order->markRefunded($cmd->reason);
        $this->orderRepo->save($order);

        $event = new OrderRefundedEvent($order->id, $cmd->reason, $order->source);
        $this->events->dispatch($event);
        $this->outbox->record($event);
        $this->outbox->flush();

        EshopMetrics::recordOrderPlaced($order->source, 'cancelled');

        return Result::ok(['orderId' => $order->id, 'status' => 'CANCELLED']);
    }
}
