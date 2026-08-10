<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice\Command;

use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use DateTimeImmutable;

class AddOrderInvoiceCommand
{
    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $paymentTerm;

    /**
     * @var string
     */
    private $amountType;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var DateTimeImmutable
     */
    private $shippingDate;

    /**
     * @var string|null
     */
    private $note;

    public function __construct(
        int $orderId,
        string $paymentMethod,
        string $paymentTerm,
        string $amountType,
        float $amount,
        string $shippingDate,
        ?string $note = null
    ) {
        $this->orderId = new OrderId($orderId);
        $this->paymentMethod = $paymentMethod;
        $this->paymentTerm = $paymentTerm;
        $this->amountType = $amountType;
        $this->amount = $amount;
        $this->shippingDate = new DateTimeImmutable($shippingDate);
        $this->note = $note;
    }

    /**
     * @return OrderId
     */
    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentTerm(): string
    {
        return $this->paymentTerm;
    }

    /**
     * @return string
     */
    public function getAmountType(): string
    {
        return $this->amountType;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getShippingDate(): DateTimeImmutable
    {
        return $this->shippingDate;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }
}
