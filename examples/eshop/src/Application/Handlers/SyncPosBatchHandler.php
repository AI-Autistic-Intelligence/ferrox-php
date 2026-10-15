<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\Handlers;

use Ferrox\Examples\Eshop\Application\Commands\SubmitOrderCommand;
use Ferrox\Examples\Eshop\Application\Commands\SyncPosBatchCommand;
use Ferrox\Examples\Eshop\Application\DTOs\SubmitOrderDto;
use Ferrox\Examples\Eshop\Infrastructure\Metrics\EshopMetrics;
use Ferrox\Utils\Types\Result;

final class SyncPosBatchHandler
{
    public function __construct(
        private SubmitOrderHandler $submitOrderHandler
    ) {}

    public function handle(SyncPosBatchCommand $command): Result
    {
        $dto = $command->dto;
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($dto->orders as $rawOrder) {
            $orderDto = new SubmitOrderDto(
                productId: (string)($rawOrder['productId'] ?? 'PROD-1'),
                quantity: (int)($rawOrder['quantity'] ?? 1),
                customerId: (string)($rawOrder['customerId'] ?? 'POS-CUSTOMER'),
                source: 'POS',
                unitPriceCents: (int)($rawOrder['unitPriceCents'] ?? 1999),
                currency: (string)($rawOrder['currency'] ?? 'EUR')
            );

            $res = $this->submitOrderHandler->handle(new SubmitOrderCommand($orderDto));
            if ($res->isOk()) {
                $successful++;
                $results[] = [
                    'status' => 'SUCCESS',
                    'order' => $res->unwrap()->toArray()
                ];
            } else {
                $failed++;
                $results[] = [
                    'status' => 'FAILED',
                    'reason' => $res->unwrapErr(),
                    'payload' => $rawOrder
                ];
            }
        }

        EshopMetrics::recordOrderPlaced('POS', "batch_synced_{$successful}_ok_{$failed}_failed");

        return Result::ok([
            'posTerminalId' => $dto->posTerminalId,
            'totalReceived' => count($dto->orders),
            'successful' => $successful,
            'failed' => $failed,
            'results' => $results
        ]);
    }
}
