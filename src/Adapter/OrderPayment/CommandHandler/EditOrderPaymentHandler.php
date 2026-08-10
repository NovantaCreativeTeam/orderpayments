<?php

namespace Novanta\OrderPayment\Adapter\OrderPayment\CommandHandler;

use Db;
use Doctrine\ORM\EntityManagerInterface;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceNotFoundException;
use Novanta\OrderPayment\Domain\OrderPayment\Command\EditOrderPayment;
use Novanta\OrderPayment\Domain\OrderPayment\CommandHandler\EditOrderPaymentHandlerInterface;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentNotFoundException;
use OrderInvoice;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Currency\Exception\CurrencyNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use Validate;
use Currency;

/**
 * Class EditOrderPaymentHandler
 */
#[AsCommandHandler]
class EditOrderPaymentHandler implements EditOrderPaymentHandlerInterface
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param EditOrderPayment $command
     * @throws CurrencyNotFoundException
     * @throws OrderPaymentException
     * @throws OrderPaymentNotFoundException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(EditOrderPayment $command)
    {
        $orderPaymentId = $command->getOrderPaymentId()->getValue();
        $orderPayment = new \OrderPayment($orderPaymentId);

        if (!Validate::isLoadedObject($orderPayment)) {
            throw new OrderPaymentNotFoundException(
                sprintf('OrderPayment object with id "%s" was not found.', $orderPaymentId)
            );
        }

        $currency = new Currency($command->getPaymentCurrencyId()->getValue());
        if (!Validate::isLoadedObject($currency)) {
            throw new CurrencyNotFoundException('The selected currency is invalid.');
        }

        $orderPayment->amount = (string)$command->getPaymentAmount();
        $orderPayment->payment_method = $command->getPaymentMethod();
        $orderPayment->date_add = $command->getPaymentDate()->format('Y-m-d H:i:s');
        $orderPayment->id_currency = $currency->id;
        $orderPayment->conversion_rate = ($currency ? $currency->conversion_rate : 1);
        $orderPayment->transaction_id = $command->getTransactionId();

        $order = \Order::getByReference($orderPayment->order_reference)->getFirst();
        if (!Validate::isLoadedObject($order)) {
            throw new OrderPaymentException(
                sprintf('Order with reference "%s" associated to OrderPayment with id "%s" is not found.', $orderPayment->order_reference, $orderPaymentId)
            );
        }

        if ($orderPayment->id_currency == $order->id_currency) {
            $order->total_paid_real += $orderPayment->amount;
        } else {
            $default_currency = Currency::getDefaultCurrencyId();
            if ($orderPayment->id_currency === $default_currency) {
                $this->total_paid_real += \Tools::ps_round(
                    \Tools::convertPrice((float)$orderPayment->amount, $order->id_currency, false)
                );
            } else {
                $amountInDefaultCurrency = \Tools::convertPrice((float)$orderPayment->amount, $orderPayment->id_currency, false);
                $this->total_paid_real += \Tools::ps_round(
                    \Tools::convertPrice($amountInDefaultCurrency, $order->id_currency, true)
                );
            }
        }

        if (false === $orderPayment->update()) {
            throw new OrderPaymentException(
                sprintf('Failed to update OrderPayment object with id "%s".', $orderPaymentId)
            );
        }

        if (false === $order->update()) {
            throw new OrderPaymentException(
                sprintf('Failed to update OrderPayment object with id "%s".', $orderPaymentId)
            );
        }

        if ($command->getOrderInvoiceId()) {

            $invoice = new OrderInvoice($command->getOrderInvoiceId());
            if(!Validate::isLoadedObject($invoice)) {
                throw new OrderInvoiceNotFoundException(sprintf('Order Invoice with id "%s" cannot be found.', $command->getOrderInvoiceId()));
            }

            $invoice->total_paid_tax_incl = $command->getPaymentAmount();
            $invoice->update();

            $orderInvoices =
                $this->entityManager->getConnection()
                    ->prepare('SELECT oip.* FROM `' . _DB_PREFIX_ . 'order_invoice_payment` oip WHERE oip.id_order_payment = :id_order_payment')
                    ->executeQuery(['id_order_payment' => $orderPayment->id])
                    ->fetchAllAssociative();

            if (!empty($orderInvoices)) {
                foreach ($orderInvoices as $orderInvoice) {
                    $this->entityManager->getConnection()
                        ->prepare('UPDATE `' . _DB_PREFIX_ . 'order_invoice_payment` SET id_order_invoice = :id_order_invoice WHERE id_order_payment = :id_order_payment')
                        ->executeStatement(['id_order_invoice' => $command->getOrderInvoiceId(), 'id_order_payment' => $orderPayment->id]);


                }
            } else {
                $this->entityManager->getConnection()
                    ->prepare('INSERT INTO `' . _DB_PREFIX_ . 'order_invoice_payment` (`id_order_invoice`, `id_order_payment`, `id_order`) VALUES (:id_order_invoice, :id_order_payment, :id_order)')
                    ->executeStatement(['id_order_invoice' => $command->getOrderInvoiceId(), 'id_order_payment' => $orderPayment->id, 'id_order' => $order->id]);
            }
        } else {
            $this->entityManager->getConnection()
                ->prepare('DELETE FROM `' . _DB_PREFIX_ . 'order_invoice_payment` WHERE id_order_payment = :id_order_payment')
                ->executeStatement([ 'id_order_payment' => $orderPayment->id]);
        }

    }
}
