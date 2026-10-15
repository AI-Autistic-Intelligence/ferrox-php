<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Model;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case RESERVED = 'RESERVED';
    case PAYMENT_PENDING = 'PAYMENT_PENDING';
    case FULFILLED = 'FULFILLED';
    case REFUNDED = 'REFUNDED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
}
