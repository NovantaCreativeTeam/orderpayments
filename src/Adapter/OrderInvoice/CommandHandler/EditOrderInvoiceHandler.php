<?php

namespace Novanta\OrderPayment\Adapter\OrderInvoice\CommandHandler;

use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceException;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceNotFoundException;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\EditOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler\EditOrderInvoiceHandlerInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use OrderInvoice;
use Db;

#[AsCommandHandler]
class EditOrderInvoiceHandler implements EditOrderInvoiceHandlerInterface
{
    /**
     * @param EditOrderInvoiceCommand $command
     * @throws OrderInvoiceNotFoundException
     * @throws OrderInvoiceException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(EditOrderInvoiceCommand $command): void
    {
        $orderInvoice = new OrderInvoice($command->getOrderInvoiceId()->getValue());

        if ($orderInvoice->id === null) {
            throw new OrderInvoiceNotFoundException($command->getOrderInvoiceId(), sprintf('Order invoice with id "%d" was not found.', $command->getOrderInvoiceId()->getValue()));
        }

        $orderInvoice->delivery_date = pSQL($command->getDeliveryDate()->format('Y-m-d H:i:s'));
        $orderInvoice->note = pSQL($command->getNote());

        if (!$orderInvoice->update()) {
            throw new OrderInvoiceException(sprintf('Could not update order invoice with id "%d".', $command->getOrderInvoiceId()->getValue()));
        }

        $this->updateProformaInfo($command->getOrderInvoiceId()->getValue(), $command);
    }

    /**
     * @param int $orderInvoiceId
     * @param EditOrderInvoiceCommand $command
     * @return void
     */
    private function updateProformaInfo(int $orderInvoiceId, EditOrderInvoiceCommand $command): void
    {
        Db::getInstance()->update(
            'order_invoice_proforma',
            [
                'payment_method' => pSQL($command->getPaymentMethod()?->value),
                'payment_term' => pSQL($command->getPaymentTerm()?->value),
                'amount_type' => pSQL($command->getAmountType()),
                'amount' => (float)$command->getAmount(),
            ],
            'id_order_invoice = ' . (int)$orderInvoiceId
        );
    }
}
