<?php

namespace Novanta\OrderPayment\Adapter\OrderInvoice\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Repository\AbstractObjectModelRepository;

class OrderInvoiceRepository extends AbstractObjectModelRepository
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
     * @param OrderInvoiceId $id
     * @return false|mixed[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function get(OrderInvoiceId $id)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from($this->dbPrefix . 'order_invoice', 'oi')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_proforma', 'oip', 'oi.id_order_invoice = oip.id_order_invoice')
            ->where('oi.id_order_invoice = :orderInvoiceId')
            ->setParameter('orderInvoiceId', $id->getValue());

        return $qb->executeQuery()->fetchAssociative();
    }

    /**
     * @param OrderId $orderId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByOrderId(OrderId $orderId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('oi.*, oip.payment_method, oip.payment_term, oip.amount_type, oip.amount, oid.id_order_document')
            ->from($this->dbPrefix . 'order_invoice', 'oi')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_proforma', 'oip', 'oi.id_order_invoice = oip.id_order_invoice')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_document', 'oid', 'oi.id_order_invoice = oid.id_order_invoice')
            ->where('oi.id_order = :orderId')
            ->setParameter('orderId', $orderId->getValue());

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param int $paymentId
     * @return array
     * @throws Exception
     */
    public function getByPaymentId(int $paymentId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('oi.*, oip.*')
            ->from($this->dbPrefix . 'order_invoice', 'oi')
            ->innerJoin('oi', $this->dbPrefix . 'order_invoice_payment', 'oipay', 'oi.id_order_invoice = oipay.id_order_invoice')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_proforma', 'oip', 'oi.id_order_invoice = oip.id_order_invoice')
            ->where('oipay.id_order_payment = :paymentId')
            ->setParameter('paymentId', $paymentId);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getOrderInvoiceDocuments(OrderInvoiceId $orderInvoiceId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('oid.*, oi.id_order')
            ->from($this->dbPrefix . 'order_invoice_document', 'oid')
            ->leftJoin('oid', $this->dbPrefix . 'order_document', 'od', 'oid.id_order_document = od.id_order_document')
            ->leftJoin('oid', $this->dbPrefix . 'order_invoice', 'oi', 'oid.id_order_invoice = oi.id_order_invoice')
            ->where('oid.id_order_invoice = :orderInvoiceId')
            ->setParameter('orderInvoiceId', $orderInvoiceId->getValue());

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getOrderInvoiceDocument(OrderInvoiceId $orderInvoiceId, int $documentId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('oid.*, oi.id_order')
            ->from($this->dbPrefix . 'order_invoice_document', 'oid')
            ->leftJoin('oid', $this->dbPrefix . 'order_document', 'od', 'oid.id_order_document = od.id_order_document')
            ->leftJoin('oid', $this->dbPrefix . 'order_invoice', 'oi', 'oid.id_order_invoice = oi.id_order_invoice')
            ->where('oid.id_order_invoice = :orderInvoiceId')
            ->andWhere('oid.id_order_document = :documentId')
            ->setParameter('orderInvoiceId', $orderInvoiceId->getValue())
            ->setParameter('documentId', $documentId);


        return $qb->executeQuery()->fetchAssociative();
    }

    public function deleteOrderInvoiceDocument(OrderInvoiceId $orderInvoiceId, int $orderDocumentId): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->delete($this->dbPrefix . 'order_invoice_document')
            ->where('id_order_invoice = :orderInvoiceId')
            ->andWhere('id_order_document = :orderDocumentId')
            ->setParameter('orderInvoiceId', $orderInvoiceId->getValue())
            ->setParameter('orderDocumentId', $orderDocumentId);

        return $qb->executeStatement();
    }

    public function addOrderInvoiceDocument(OrderInvoiceId $orderInvoiceId, int $orderDocumentId): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert($this->dbPrefix . 'order_invoice_document')
            ->values([
                'id_order_invoice' => $orderInvoiceId->getValue(),
                'id_order_document' => $orderDocumentId,
            ]);

        return $qb->executeStatement();
    }
}