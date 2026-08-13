<?php

namespace Novanta\OrderPayment\Adapter\OrderPayment\CommandHandler;

use Currency;
use Doctrine\ORM\EntityManagerInterface;
use Novanta\OrderPayment\Domain\OrderPayment\Command\DeleteOrderPayment;
use Novanta\OrderPayment\Domain\OrderPayment\CommandHandler\DeleteOrderPaymentHandlerInterface;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentNotFoundException;
use Novanta\OrderPayment\Entity\OrderPaymentDocument;
use Novanta\OrderCharging\Domain\OrderDocument\Command\DeleteOrderDocument;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use Validate;

/**
 * Class DeleteOrderPaymentHandler
 */
#[AsCommandHandler]
class DeleteOrderPaymentHandler implements DeleteOrderPaymentHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommandBusInterface $commandBus;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandBusInterface $commandBus
    ) {
        $this->entityManager = $entityManager;
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     *
     * @param DeleteOrderPayment $command
     * @throws OrderPaymentException
     * @throws OrderPaymentNotFoundException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(DeleteOrderPayment $command)
    {
        $orderPaymentId = $command->getOrderPaymentId()->getValue();
        $orderPayment = new \OrderPayment($orderPaymentId);

        if (!Validate::isLoadedObject($orderPayment)) {
            throw new OrderPaymentNotFoundException(
                sprintf('OrderPayment object with id "%s" was not found.', $orderPaymentId)
            );
        }

        $order = \Order::getByReference($orderPayment->order_reference)->getFirst();
        if (!Validate::isLoadedObject($order)) {
            throw new OrderPaymentException(
                sprintf('Order with reference "%s" associated to OrderPayment with id "%s" is not found.', $orderPayment->order_reference, $orderPaymentId)
            );
        }

        // Ricalcolo totale pagato (sottrazione del pagamento eliminato)
        if ($orderPayment->id_currency == $order->id_currency) {
            $order->total_paid_real -= $orderPayment->amount;
        } else {
            $default_currency = Currency::getDefaultCurrencyId();
            if ($orderPayment->id_currency === $default_currency) {
                $order->total_paid_real -= \Tools::ps_round(
                    \Tools::convertPrice((float)$orderPayment->amount, $order->id_currency, false)
                );
            } else {
                $amountInDefaultCurrency = \Tools::convertPrice((float)$orderPayment->amount, $orderPayment->id_currency, false);
                $order->total_paid_real -= \Tools::ps_round(
                    \Tools::convertPrice($amountInDefaultCurrency, $order->id_currency, true)
                );
            }
        }

        // Cancellazione OrderDocument se associato
        /** @var OrderPaymentDocument $orderPaymentDocument */
        $orderPaymentDocument = $this->entityManager->getRepository(OrderPaymentDocument::class)->findOneBy(['orderPaymentId' => $orderPayment->id]);
        if ($orderPaymentDocument) {
            $this->commandBus->handle(new DeleteOrderDocument(
                (int)$order->id,
                (int)$orderPaymentDocument->getOrderDocumentId()
            ));

            $this->entityManager->remove($orderPaymentDocument);
            $this->entityManager->flush();
        }

        // Cancellazione collegamento con fatture
        $this->entityManager->getConnection()
            ->prepare('DELETE FROM `' . _DB_PREFIX_ . 'order_invoice_payment` WHERE id_order_payment = :id_order_payment')
            ->executeStatement(['id_order_payment' => $orderPayment->id]);

        // Cancellazione pagamento
        if (false === $orderPayment->delete()) {
            throw new OrderPaymentException(
                sprintf('Failed to delete OrderPayment object with id "%s".', $orderPaymentId)
            );
        }

        // Aggiornamento ordine
        if (false === $order->update()) {
            throw new OrderPaymentException(
                sprintf('Failed to update Order with reference "%s".', $orderPayment->order_reference)
            );
        }
    }
}
