<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice\Command;

use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;

class DeleteOrderInvoiceCommand
{
    /**
     * @var OrderInvoiceId
     */
    private $orderInvoiceId;

    public function __construct(int $orderInvoiceId)
    {
        $this->orderInvoiceId = new OrderInvoiceId($orderInvoiceId);
    }

    /**
     * @return OrderInvoiceId
     */
    public function getOrderInvoiceId(): OrderInvoiceId
    {
        return $this->orderInvoiceId;
    }
}
