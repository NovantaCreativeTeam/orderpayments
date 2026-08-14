<?php

namespace Novanta\OrderPayment\Adapter\OrderPayment\CommandHandler;

use Context;
use Currency;
use Db;
use Novanta\OrderCharging\Domain\OrderDocument\Command\UploadDocumentToOrder;
use Novanta\OrderPayment\Adapter\OrderPayment\Repository\OrderPaymentRepository;
use Novanta\OrderPayment\Domain\OrderPayment\Command\AddOrderPaymentCommand;
use Novanta\OrderPayment\Domain\OrderPayment\CommandHandler\AddOrderPaymentHandlerInterface;
use OrderInvoice;
use OrderPayment;
use PrestaShop\PrestaShop\Adapter\Order\AbstractOrderHandler;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use Tools;
use Validate;

#[AsCommandHandler]
class AddOrderPaymentHandler extends AbstractOrderHandler implements AddOrderPaymentHandlerInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;
    private OrderPaymentRepository $orderPaymentRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        OrderPaymentRepository $orderPaymentRepository
    )
    {
        $this->commandBus = $commandBus;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    public function handle(AddOrderPaymentCommand $command)
    {
        $order = $this->getOrder($command->getOrderId());
        $currency = new Currency($command->getPaymentCurrencyId()->getValue());

        if (!Validate::isLoadedObject($currency)) {
            throw new OrderException('The selected currency is invalid.');
        }

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
            VALUES(' . (int) $command->getOrderInvoiceId() . ', ' . (int) $order_payment->id . ', ' . (int) $order_payment->id . ')');
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

        if($command->getOrderInvoiceId()) {
            $this->setInvoiceTotalPaid($command->getOrderInvoiceId());
        }
    }

    protected function setInvoiceTotalPaid($orderInvoiceId): bool
    {
        $orderInvoice = new OrderInvoice($orderInvoiceId);
        if (!Validate::isLoadedObject($orderInvoice)) {
            throw new OrderException('The invoice is invalid.');
        }

        $invoicePayments = $this->orderPaymentRepository->getAllByInvoiceId(new OrderInvoiceId($orderInvoiceId));
        $orderInvoice->total_paid_tax_incl = 0;
        foreach ($invoicePayments as $invoicePayment) {
            $orderInvoice->total_paid_tax_incl += $invoicePayment['amount'];
        }

        return $orderInvoice->update();
    }
}
