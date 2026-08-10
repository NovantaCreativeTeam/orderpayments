<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler;

use Novanta\OrderPayment\Domain\OrderInvoice\Command\EditOrderInvoiceCommand;

interface EditOrderInvoiceHandlerInterface
{
    /**
     * @param EditOrderInvoiceCommand $command
     */
    public function handle(EditOrderInvoiceCommand $command): void;
}
