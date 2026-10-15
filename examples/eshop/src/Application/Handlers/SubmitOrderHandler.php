<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Handlers;

use Ferrox\Events\EventDispatcher;
use Ferrox\Examples\Eshop\Application\Commands\SubmitOrderCommand;
use Ferrox\Examples\Eshop\Application\DTOs\OrderResponseDto;
use Ferrox\Examples\Eshop\Domain\Events\CriticalAnomalyEvent;
use Ferrox\Examples\Eshop\Domain\Events\OrderFulfilledEvent;
use Ferrox\Examples\Eshop\Domain\Events\OrderRefundedEvent;
use Ferrox\Examples\Eshop\Domain\Events\StockDepletedEvent;
use Ferrox\Examples\Eshop\Domain\Model\Order;
use Ferrox\Examples\Eshop\Domain\Model\OrderItem;
use Ferrox\Examples\Eshop\Domain\Model\OrderStatus;
use Ferrox\Examples\Eshop\Domain\Repository\PaymentGatewayInterface;
use Ferrox\Examples\Eshop\Domain\Sagas\OrderFulfillmentSaga;
use Ferrox\Examples\Eshop\Domain\Sagas\SagaStepInterface;
use Ferrox\Examples\Eshop\Infrastructure\Metrics\EshopMetrics;
use Ferrox\Examples\Eshop\Infrastructure\Outbox\RedisOutboxPublisher;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoOrderRepository;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoWarehouseRepository;
use Ferrox\Utils\Types\Result;

final class SubmitOrderHandler
{
    public function __construct(
        private PdoWarehouseRepository $warehouseRepo,
        private PdoOrderRepository $orderRepo,
        private PaymentGatewayInterface $paymentGateway,
        private EventDispatcher $events,
        private RedisOutboxPublisher $outbox
    ) {}

    public function handle(SubmitOrderCommand $command): Result
    {
        $dto = $command->dto;
        $orderId = 'ORD-' . bin2hex(random_bytes(6));
        $totalCents = $dto->quantity * $dto->unitPriceCents;

        $order = new Order(
            id: $orderId,
            customerId: $dto->customerId,
            source: $dto->source
        );
        $order->addItem(new OrderItem(
            productId: $dto->productId,
            productName: "Product {$dto->productId}",
            quantity: $dto->quantity,
            unitPriceCents: $dto->unitPriceCents
        ));

        // Saga Context
        $context = [
            'order' => $order,
            'productId' => $dto->productId,
            'quantity' => $dto->quantity,
            'source' => $dto->source,
            'totalCents' => $totalCents,
            'currency' => $dto->currency,
            'assignedWarehouse' => null,
            'paymentTransactionId' => null,
        ];

        // Build Multi-Warehouse Saga
        $saga = new OrderFulfillmentSaga();

        // STEP 1: Multi-Warehouse Inventory Check & Allocation
        $warehouseRepo = $this->warehouseRepo;
        $events = $this->events;
        $saga->addStep(new class($warehouseRepo, $events) implements SagaStepInterface {
            public function __construct(
                private PdoWarehouseRepository $repo,
                private EventDispatcher $events
            ) {}

            public function getName(): string
            {
                return 'InventoryAllocationStep';
            }

            public function execute(array &$context): Result
            {
                $whOpt = $this->repo->findAvailableWarehouse($context['productId'], $context['quantity']);
                if ($whOpt->isNone()) {
                    $this->events->dispatch(new StockDepletedEvent($context['productId'], $context['quantity']));
                    EshopMetrics::recordStockDepleted($context['productId']);
                    return Result::err("Stock totally depleted across all warehouses.");
                }

                $wh = $whOpt->unwrap();
                $context['assignedWarehouse'] = $wh->id;
                $this->repo->decrementStock($wh->id, $context['productId'], $context['quantity']);
                $context['order']->markReserved($wh->id);
                return Result::ok(true);
            }

            public function compensate(array &$context): Result
            {
                if (!empty($context['assignedWarehouse'])) {
                    $this->repo->incrementStock($context['assignedWarehouse'], $context['productId'], $context['quantity']);
                    EshopMetrics::recordSagaCompensation('InventoryAllocationStep');
                }
                return Result::ok(true);
            }
        });

        // STEP 2: Payment Authorization & Capture
        $paymentGateway = $this->paymentGateway;
        $saga->addStep(new class($paymentGateway) implements SagaStepInterface {
            public function __construct(private PaymentGatewayInterface $gateway) {}

            public function getName(): string
            {
                return 'PaymentProcessingStep';
            }

            public function execute(array &$context): Result
            {
                $payResult = $this->gateway->capture(
                    $context['order']->id,
                    $context['totalCents'],
                    $context['currency']
                );

                if ($payResult->isErr()) {
                    return Result::err($payResult->unwrapErr());
                }

                $context['paymentTransactionId'] = $payResult->unwrap();
                return Result::ok(true);
            }

            public function compensate(array &$context): Result
            {
                if (!empty($context['paymentTransactionId'])) {
                    $this->gateway->refund($context['paymentTransactionId'], $context['totalCents']);
                    EshopMetrics::recordSagaCompensation('PaymentProcessingStep');
                }
                return Result::ok(true);
            }
        });

        // STEP 3: Order Finalization & Outbox
        $orderRepo = $this->orderRepo;
        $outbox = $this->outbox;
        $saga->addStep(new class($orderRepo, $events, $outbox) implements SagaStepInterface {
            public function __construct(
                private PdoOrderRepository $orderRepo,
                private EventDispatcher $events,
                private RedisOutboxPublisher $outbox
            ) {}

            public function getName(): string
            {
                return 'OrderFinalizationStep';
            }

            public function execute(array &$context): Result
            {
                /** @var Order $order */
                $order = $context['order'];
                $order->markFulfilled($context['paymentTransactionId']);
                $this->orderRepo->save($order);

                $event = new OrderFulfilledEvent(
                    orderId: $order->id,
                    warehouseId: $context['assignedWarehouse'],
                    paymentTransactionId: $context['paymentTransactionId'],
                    source: $context['source']
                );

                $this->events->dispatch($event);
                $this->outbox->record($event);
                $this->outbox->flush();

                EshopMetrics::recordOrderPlaced($context['source'], 'fulfilled');

                return Result::ok(true);
            }

            public function compensate(array &$context): Result
            {
                /** @var Order $order */
                $order = $context['order'];
                $order->markRefunded("Saga rollback compensation");
                $this->orderRepo->save($order);

                $event = new OrderRefundedEvent($order->id, "Saga rollback", $context['source']);
                $this->events->dispatch($event);
                $this->outbox->record($event);
                $this->outbox->flush();

                EshopMetrics::recordOrderPlaced($context['source'], 'refunded');
                return Result::ok(true);
            }
        });

        // Run Saga
        $sagaResult = $saga->execute($context);

        if (!$sagaResult->isSuccess) {
            // Persist order as REFUNDED or FAILED for auditability
            $order->markRefunded($sagaResult->errorMessage ?? 'Fulfillment Saga Failed');
            $this->orderRepo->save($order);

            $refundEvent = new OrderRefundedEvent($order->id, $sagaResult->errorMessage ?? 'Fulfillment failure', $dto->source);
            $this->events->dispatch($refundEvent);
            $this->outbox->record($refundEvent);
            $this->outbox->flush();

            EshopMetrics::recordOrderPlaced($dto->source, 'refunded');

            return Result::err($sagaResult->errorMessage ?? 'Fulfillment Saga Failed');
        }

        $responseDto = new OrderResponseDto(
            orderId: $order->id,
            status: $order->getStatus()->value,
            source: $order->source,
            totalCents: $order->getTotalCents(),
            warehouseId: $order->getAssignedWarehouseId(),
            paymentTransactionId: $order->getPaymentTransactionId()
        );

        return Result::ok($responseDto);
    }
}
