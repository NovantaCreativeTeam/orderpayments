<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice\Command;

use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentMethod;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use DateTimeImmutable;

class EditOrderInvoiceCommand
{
    /**
     * @var OrderInvoiceId
     */
    private $orderInvoiceId;

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
    private $deliveryDate;

    /**
     * @var string|null
     */
    private $note;

    public function __construct(
        int                        $orderInvoiceId,
        ?OrderInvoicePaymentMethod $paymentMethod,
        ?OrderInvoicePaymentTerm   $paymentTerm,
        string                     $amountType,
        float                      $amount,
        string                     $deliveryDate,
        ?string                    $note = null
    ) {
        $this->orderInvoiceId = new OrderInvoiceId($orderInvoiceId);
        $this->paymentMethod = $paymentMethod;
        $this->paymentTerm = $paymentTerm;
        $this->amountType = $amountType;
        $this->amount = $amount;
        $this->deliveryDate = new DateTimeImmutable($deliveryDate);
        $this->note = $note;
    }

    /**
     * @return OrderInvoiceId
     */
    public function getOrderInvoiceId(): OrderInvoiceId
    {
        return $this->orderInvoiceId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentTerm()
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
    public function getDeliveryDate(): DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }
}
