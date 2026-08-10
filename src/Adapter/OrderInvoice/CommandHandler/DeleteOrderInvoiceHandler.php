<?php

namespace Novanta\OrderPayment\Adapter\OrderInvoice\CommandHandler;

use Db;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceException;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceNotFoundException;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\DeleteOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler\DeleteOrderInvoiceHandlerInterface;
use OrderInvoice;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;

#[AsCommandHandler]
class DeleteOrderInvoiceHandler implements DeleteOrderInvoiceHandlerInterface
{
    /**
     * @param DeleteOrderInvoiceCommand $command
     * @throws OrderInvoiceNotFoundException
     * @throws OrderInvoiceException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(DeleteOrderInvoiceCommand $command): void
    {
        $orderInvoiceId = $command->getOrderInvoiceId()->getValue();
        $orderInvoice = new OrderInvoice($orderInvoiceId);

        if ($orderInvoice->id === null) {
            throw new OrderInvoiceNotFoundException(sprintf('Order invoice with id "%d" was not found.', $orderInvoiceId));
        }

        // Cancellazione dati da order_invoice_tax
        Db::getInstance()->delete('order_invoice_tax', 'id_order_invoice = ' . (int)$orderInvoiceId);

        // Cancellazione dati da order_invoice_proforma
        Db::getInstance()->delete('order_invoice_proforma', 'id_order_invoice = ' . (int)$orderInvoiceId);

        // Cancellazione dell'oggetto OrderInvoice
        if (!$orderInvoice->delete()) {
            throw new OrderInvoiceException(sprintf('Could not delete order invoice with id "%d".', $orderInvoiceId));
        }
    }
}
