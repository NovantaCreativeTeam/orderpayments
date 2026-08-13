<?php

namespace Novanta\OrderPayment\Adapter\OrderPayment\CommandHandler;

use Context;
use Currency;
use Db;
use Novanta\OrderCharging\Domain\OrderDocument\Command\UploadDocumentToOrder;
use Novanta\OrderPayment\Domain\OrderPayment\Command\AddOrderPaymentCommand;
use Novanta\OrderPayment\Domain\OrderPayment\CommandHandler\AddOrderPaymentHandlerInterface;
use OrderInvoice;
use OrderPayment;
use PrestaShop\PrestaShop\Adapter\Order\AbstractOrderHandler;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use Tools;
use Validate;

#[AsCommandHandler]
class AddOrderPaymentHandler extends AbstractOrderHandler implements AddOrderPaymentHandlerInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

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

//        $paymentAdded = $order->addOrderPayment(
//            (string) $command->getPaymentAmount(),
//            $command->getPaymentMethod(),
//            $command->getPaymentTransactionId(),
//            $currency,
//            $command->getPaymentDate()->format('Y-m-d H:i:s'),
//            $orderInvoice,
//            $command->getEmployeeId()->getValue()
//        );

        $order_payment = new OrderPayment();
        $order_payment->order_reference = $order->reference;
        $order_payment->id_currency = $currency->id;
        // we kept the currency rate for historization reasons
        $order_payment->conversion_rate = ($currency ? $currency->conversion_rate : 1);
        // if payment_method is define, we used this
        $order_payment->payment_method = $command->getPaymentMethod();
        $order_payment->transaction_id = $command->getPaymentTransactionId();
        $order_payment->amount = (float) $command->getPaymentAmount();
        $order_payment->id_employee = $command->getEmployeeId()->getValue();
        $order_payment->date_add = $command->getPaymentDate()->format('Y-m-d H:i:s');

        if ($order_payment->id_currency == $order->id_currency) {
            $order->total_paid_real += $order_payment->amount;
        } else {
            $default_currency = Currency::getDefaultCurrencyId();
            if ($order_payment->id_currency === $default_currency) {
                $order->total_paid_real += Tools::ps_round(
                    Tools::convertPrice((float) $order_payment->amount, $order->id_currency, false),
                    Context::getContext()->getComputingPrecision()
                );
            } else {
                $amountInDefaultCurrency = Tools::convertPrice((float) $order_payment->amount, $order_payment->id_currency, false);
                $order->total_paid_real += Tools::ps_round(
                    Tools::convertPrice($amountInDefaultCurrency, $order->id_currency, true),
                    Context::getContext()->getComputingPrecision()
                );
            }
        }

        $res = $order_payment->add(null === $order_payment->date_add);
        if (!$res) {
            throw new OrderException('An error occurred during payment.');
        }

        $order->update();

        if (null !== $command->getOrderInvoiceId()) {
            Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'order_invoice_payment` (`id_order_invoice`, `id_order_payment`, `id_order`)
            VALUES(' . (int) $command->getOrderInvoiceId() . ', ' . (int) $order_payment->id . ', ' . (int) $this->id . ')');
        }

        if ($command->getFile()) {
            $documentId = $this->commandBus->handle(new UploadDocumentToOrder(
                $command->getOrderId()->getValue(),
                $command->getFile(),
                [], // localizedNames
                []  // localizeDescriptions
            ));

            Db::getInstance()->insert('order_payment_document', [
                'id_order_payment' => $order_payment->id,
                'id_order_document' => (int) $documentId,
            ]);
        }

        if ($orderInvoice) {
            /** @var array<\OrderPayment> $payments */
            $payments = $order->getOrderPayments();
            foreach ($payments as $payment) {
                $orderInvoice->total_paid_tax_incl += $payment->amount;
            }

            $orderInvoice->update();
        }
    }
}
