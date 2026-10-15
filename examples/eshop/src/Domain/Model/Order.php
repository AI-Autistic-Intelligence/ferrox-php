<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Domain\Model;

final class Order
{
    /** @var OrderItem[] */
    private array $items = [];

    public function __construct(
        public readonly string $id,
        public readonly string $customerId,
        public readonly string $source, // "ONLINE" | "POS" | "API"
        private OrderStatus $status = OrderStatus::PENDING,
        private ?string $assignedWarehouseId = null,
        private ?string $paymentTransactionId = null,
        private ?string $failureReason = null,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {}

    public function addItem(OrderItem $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalCents(): int
    {
        return array_reduce(
            $this->items,
            static fn (int $carry, OrderItem $item): int => $carry + $item->subtotalCents(),
            0
        );
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getAssignedWarehouseId(): ?string
    {
        return $this->assignedWarehouseId;
    }

    public function getPaymentTransactionId(): ?string
    {
        return $this->paymentTransactionId;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function markReserved(string $warehouseId): void
    {
        if ($this->status !== OrderStatus::PENDING) {
            throw new \DomainException("Order {$this->id} cannot be reserved from state {$this->status->value}");
        }
        $this->status = OrderStatus::RESERVED;
        $this->assignedWarehouseId = $warehouseId;
    }

    public function markFulfilled(string $paymentTransactionId): void
    {
        if ($this->status !== OrderStatus::RESERVED) {
            throw new \DomainException("Order {$this->id} cannot be fulfilled from state {$this->status->value}");
        }
        $this->status = OrderStatus::FULFILLED;
        $this->paymentTransactionId = $paymentTransactionId;
    }

    public function markRefunded(string $reason): void
    {
        $this->status = OrderStatus::REFUNDED;
        $this->failureReason = $reason;
    }

    public function markFailed(string $reason): void
    {
        $this->status = OrderStatus::FAILED;
        $this->failureReason = $reason;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'source' => $this->source,
            'status' => $this->status->value,
            'assignedWarehouse' => $this->assignedWarehouseId,
            'paymentTransactionId' => $this->paymentTransactionId,
            'failureReason' => $this->failureReason,
            'totalCents' => $this->getTotalCents(),
            'itemsCount' => count($this->items),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM)
        ];
    }
}
