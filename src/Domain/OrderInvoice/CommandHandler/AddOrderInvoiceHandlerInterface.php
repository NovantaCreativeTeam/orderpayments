<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler;

use Novanta\OrderPayment\Domain\OrderInvoice\Command\AddOrderInvoiceCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;

interface AddOrderInvoiceHandlerInterface
{
    /**
     * @param AddOrderInvoiceCommand $command
     * @return OrderInvoiceId
     */
    public function handle(AddOrderInvoiceCommand $command): OrderInvoiceId;
}
