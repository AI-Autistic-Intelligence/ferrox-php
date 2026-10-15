<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Http\Controllers;

use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;
use Ferrox\Examples\Eshop\Application\Commands\CancelOrderCommand;
use Ferrox\Examples\Eshop\Application\Commands\SubmitOrderCommand;
use Ferrox\Examples\Eshop\Application\Commands\SyncPosBatchCommand;
use Ferrox\Examples\Eshop\Application\DTOs\SubmitOrderDto;
use Ferrox\Examples\Eshop\Application\DTOs\SyncPosBatchDto;
use Ferrox\Examples\Eshop\Application\Handlers\CancelOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\GetOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\SubmitOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\SyncPosBatchHandler;
use Ferrox\Examples\Eshop\Application\Queries\GetOrderQuery;

final class OrderController
{
    public function __construct(
        private SubmitOrderHandler $submitOrderHandler,
        private CancelOrderHandler $cancelOrderHandler,
        private GetOrderHandler $getOrderHandler,
        private SyncPosBatchHandler $syncPosBatchHandler
    ) {}

    public function submitOrder(Request $request): Response
    {
        /** @var SubmitOrderDto|null $dto */
        $dto = $request->getAttribute('dto');
        if ($dto === null) {
            $body = $request->body ?? [];
            $dto = new SubmitOrderDto(
                productId: (string)($body['productId'] ?? ''),
                quantity: (int)($body['quantity'] ?? 1),
                customerId: (string)($body['customerId'] ?? 'CUST-ANON'),
                source: (string)($body['source'] ?? 'ONLINE'),
                unitPriceCents: (int)($body['unitPriceCents'] ?? 1999),
                currency: (string)($body['currency'] ?? 'EUR')
            );
        }

        $result = $this->submitOrderHandler->handle(new SubmitOrderCommand($dto));

        if ($result->isErr()) {
            return Response::json([
                'type' => 'https://ferrox.dev/errors/order-failed',
                'title' => 'Order Placement Failed',
                'status' => 409,
                'detail' => $result->unwrapErr(),
                'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
            ], 409);
        }

        return Response::json([
            'success' => true,
            'data' => $result->unwrap()->toArray()
        ], 201);
    }

    public function getOrder(Request $request, string $orderId): Response
    {
        $result = $this->getOrderHandler->handle(new GetOrderQuery($orderId));
        if ($result->isErr()) {
            return Response::json([
                'type' => 'https://ferrox.dev/errors/not-found',
                'title' => 'Order Not Found',
                'status' => 404,
                'detail' => $result->unwrapErr(),
            ], 404);
        }

        return Response::json([
            'success' => true,
            'data' => $result->unwrap()
        ], 200);
    }

    public function cancelOrder(Request $request, string $orderId): Response
    {
        $reason = (string)($request->body['reason'] ?? 'Customer requested cancellation');
        $result = $this->cancelOrderHandler->handle(new CancelOrderCommand($orderId, $reason));

        if ($result->isErr()) {
            return Response::json([
                'type' => 'https://ferrox.dev/errors/cancel-failed',
                'title' => 'Order Cancellation Failed',
                'status' => 400,
                'detail' => $result->unwrapErr()
            ], 400);
        }

        return Response::json([
            'success' => true,
            'data' => $result->unwrap()
        ], 200);
    }

    public function syncPosBatch(Request $request): Response
    {
        $body = $request->body ?? [];
        $dto = new SyncPosBatchDto(
            orders: (array)($body['orders'] ?? []),
            posTerminalId: (string)($body['posTerminalId'] ?? 'POS-DEFAULT')
        );

        $result = $this->syncPosBatchHandler->handle(new SyncPosBatchCommand($dto));

        return Response::json([
            'success' => true,
            'data' => $result->unwrap()
        ], 200);
    }
}
