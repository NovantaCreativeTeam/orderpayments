<?php

namespace Novanta\OrderPayment\Adapter\OrderPayment\CommandHandler;

use Currency;
use Novanta\OrderPayment\Domain\OrderPayment\Command\AddOrderPaymentCommand;
use Novanta\OrderPayment\Domain\OrderPayment\CommandHandler\AddOrderPaymentHandlerInterface;
use OrderInvoice;
use PrestaShop\PrestaShop\Adapter\Order\AbstractOrderHandler;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use Validate;

#[AsCommandHandler]
class AddOrderPaymentHandler extends AbstractOrderHandler implements AddOrderPaymentHandlerInterface
{

    public function handle(AddOrderPaymentCommand $command)
    {
        $order = $this->getOrder($command->getOrderId());
        $currency = new Currency($command->getPaymentCurrencyId()->getValue());

        if (!Validate::isLoadedObject($currency)) {
            throw new OrderException('The selected currency is invalid.');
        }

        $orderInvoice = null;
        if ($command->getOrderInvoiceId()) {
            $orderInvoice = new OrderInvoice($command->getOrderInvoiceId());
            if (!Validate::isLoadedObject($orderInvoice)) {
                throw new OrderException('The invoice is invalid.');
            }
        }

        $paymentAdded = $order->addOrderPayment(
            (string) $command->getPaymentAmount(),
            $command->getPaymentMethod(),
            $command->getPaymentTransactionId(),
            $currency,
            $command->getPaymentDate()->format('Y-m-d H:i:s'),
            $orderInvoice,
            $command->getEmployeeId()->getValue()
        );

        if (!$paymentAdded) {
            throw new OrderException('An error occurred during payment.');
        }
    }
}
