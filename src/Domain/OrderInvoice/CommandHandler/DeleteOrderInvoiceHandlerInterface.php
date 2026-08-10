<?php

namespace Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler;

use Novanta\OrderPayment\Domain\OrderInvoice\Command\DeleteOrderInvoiceCommand;

interface DeleteOrderInvoiceHandlerInterface
{
    /**
     * @param DeleteOrderInvoiceCommand $command
     */
    public function handle(DeleteOrderInvoiceCommand $command): void;
}
