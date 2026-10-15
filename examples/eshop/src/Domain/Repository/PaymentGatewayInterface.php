<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Repository;

use Ferrox\Utils\Types\Result;

interface PaymentGatewayInterface
{
    /**
     * Authorizes and captures a payment.
     * @return Result<string, string> Ok(transactionId) or Err(reason)
     */
    public function capture(string $orderId, int $amountCents, string $currency): Result;

    /**
     * Refunds an authorized/captured payment.
     * @return Result<bool, string>
     */
    public function refund(string $transactionId, int $amountCents): Result;
}
