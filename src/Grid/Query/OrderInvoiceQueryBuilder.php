<?php

namespace Novanta\OrderPayment\Grid\Query;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicator;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class OrderInvoiceQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicator
     */
    private $searchCriteriaApplicator;

    public function __construct(
        Connection $connection,
        $dbPrefix,
        DoctrineSearchCriteriaApplicator $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $queryBuilder = $this->getQueryBuilder($searchCriteria->getFilters());

        $queryBuilder->select('
                oi.id_order_invoice,
                oi.id_order,
                oi.number as invoice_number,
                oi.date_add,
                o.total_products_wt + o.total_shipping_tax_incl + o.total_wrapping_tax_incl - o.total_discounts_tax_incl as total_order,
                o.reference as order_reference,
                oid.id_order_document,
                oipr.payment_method,
                oipr.payment_term,
                SUM(op.amount) as total_paid,
                oipr.amount_type,
                oipr.amount as amount
                '
            )
            ->addSelect('NULL as total_paid_formatted')
            ->addSelect('NULL as total_to_pay_formatted')
            ->addSelect('NULL as total_order_formatted')
        ;

        $this->searchCriteriaApplicator->applyPagination($searchCriteria, $queryBuilder);
        $this->searchCriteriaApplicator->applySorting($searchCriteria, $queryBuilder);

        return $queryBuilder;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $queryBuilder = $this->getQueryBuilder($searchCriteria->getFilters());
        $queryBuilder->select('COUNT(oi.id_order_invoice)');

        return $queryBuilder;
    }

    private function getQueryBuilder(array $filters)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->from($this->dbPrefix . 'order_invoice', 'oi')
            ->innerJoin('oi', $this->dbPrefix . 'orders', 'o', 'oi.id_order = o.id_order')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_document', 'oid', 'oi.id_order_invoice = oid.id_order_invoice')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_proforma', 'oipr', 'oi.id_order_invoice = oipr.id_order_invoice')
            ->leftJoin('oi', $this->dbPrefix . 'order_invoice_payment', 'oip', 'oi.id_order_invoice = oip.id_order_invoice')
            ->leftJoin('oip', $this->dbPrefix . 'order_payment', 'op', 'oip.id_order_payment = op.id_order_payment')
            ->groupBy('oi.id_order_invoice')
        ;

        foreach ($filters as $name => $value) {
            if ('id_order_invoice' === $name) {
                $queryBuilder->andWhere('oi.id_order_invoice = :id_order_invoice');
                $queryBuilder->setParameter('id_order_invoice', $value);
                continue;
            }

            if ('invoice_number' === $name) {
                $queryBuilder->andWhere('oi.number LIKE :invoice_number');
                $queryBuilder->setParameter('invoice_number', '%' . $value . '%');
                continue;
            }

            if ('order_reference' === $name) {
                $queryBuilder->andWhere('o.reference LIKE :order_reference');
                $queryBuilder->setParameter('order_reference', '%' . $value . '%');
                continue;
            }

            if ('date_add' === $name) {
                if (isset($value['from'])) {
                    $queryBuilder->andWhere('oi.date_add >= :date_add_from');
                    $queryBuilder->setParameter('date_add_from', $value['from']);
                }
                if (isset($value['to'])) {
                    $queryBuilder->andWhere('oi.date_add <= :date_add_to');
                    $queryBuilder->setParameter('date_add_to', $value['to']);
                }
                continue;
            }

            if ('payment_method' === $name) {
                $queryBuilder->andWhere('oipr.payment_method LIKE :payment_method');
                $queryBuilder->setParameter('payment_method', '%' . $value . '%');
                continue;
            }

            if ('payment_term' === $name) {
                $queryBuilder->andWhere('oipr.payment_term LIKE :payment_term');
                $queryBuilder->setParameter('payment_term', '%' . $value . '%');
                continue;
            }
        }

        return $queryBuilder;
    }
}
