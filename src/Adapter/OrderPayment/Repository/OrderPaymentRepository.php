<?php

namespace Novanta\OrderPayment\Adapter\OrderPayment\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Repository\AbstractObjectModelRepository;

class OrderPaymentRepository extends AbstractObjectModelRepository
{
    private Connection $connection;
    private string $dbPrefix;

    public function __construct(
        Connection $connection,
        string     $dbPrefix,
    )
    {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
    }

    /**
     * @param OrderId $orderId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAllByInvoiceId(OrderInvoiceId $orderInvoiceId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('op.*')
            ->from($this->dbPrefix . 'order_payment', 'op')
            ->innerJoin('op', $this->dbPrefix . 'order_invoice_payment', 'oip', 'op.id_order_payment = oip.id_order_payment')
            ->where('oip.id_order_invoice = :orderInvoiceId')
            ->setParameter('orderInvoiceId', $orderInvoiceId->getValue());

        return $qb->executeQuery()->fetchAllAssociative();
    }
}