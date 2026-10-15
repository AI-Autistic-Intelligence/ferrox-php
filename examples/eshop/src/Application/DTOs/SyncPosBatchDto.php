<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Application\DTOs;

use Ferrox\Validation\Attributes\ValidatedDto;

#[ValidatedDto(strict: true)]
final class SyncPosBatchDto
{
    /** @var array Array of raw order payloads */
    public array $orders;
    public string $posTerminalId;

    public function __construct(
        array $orders = [],
        string $posTerminalId = 'POS-DEFAULT'
    ) {
        $this->orders = $orders;
        $this->posTerminalId = $posTerminalId;
    }
}
