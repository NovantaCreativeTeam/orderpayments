<?php

namespace Novanta\OrderPayment\Grid\Query;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicator;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class OrderPaymentQueryBuilder extends AbstractDoctrineQueryBuilder
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
                op.id_order_payment, 
                op.date_add, 
                op.payment_method, 
                op.transaction_id, 
                op.amount, 
                c.iso_code as currency_iso_code, 
                oi.id_order_invoice, 
                oi.number as invoice_number, 
                o.id_order, 
                o.reference as order_reference,
                IF(cu.company <> \'\', cu.company, CONCAT(cu.firstname, \' \', cu.lastname)) as customer,
                GROUP_CONCAT(DISTINCT(od.product_name)) as products,
                opd.id_order_document'
            )
            ->addSelect('NULL as amount_formatted') // Will be filled by DataFactory
        ;

        $this->searchCriteriaApplicator->applyPagination($searchCriteria, $queryBuilder);
        $this->searchCriteriaApplicator->applySorting($searchCriteria, $queryBuilder);

        return $queryBuilder;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $queryBuilder = $this->getQueryBuilder($searchCriteria->getFilters());
        $queryBuilder->select('COUNT(op.id_order_payment)');

        return $queryBuilder;
    }

    private function getQueryBuilder(array $filters)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->from($this->dbPrefix . 'order_payment', 'op')
            ->innerJoin('op', $this->dbPrefix . 'orders', 'o', 'op.order_reference = o.reference')
            ->innerJoin('op', $this->dbPrefix . 'customer', 'cu', 'o.id_customer = cu.id_customer')
            ->innerJoin('o', $this->dbPrefix . 'order_detail', 'od', 'o.id_order = od.id_order')
            ->leftJoin('op', $this->dbPrefix . 'currency', 'c', 'op.id_currency = c.id_currency')
            ->leftJoin('op', $this->dbPrefix . 'order_payment_document', 'opd', 'op.id_order_payment = opd.id_order_payment')
            ->leftJoin('op', $this->dbPrefix . 'order_invoice_payment', 'oip', 'op.id_order_payment = oip.id_order_payment')
            ->leftJoin('oip', $this->dbPrefix . 'order_invoice', 'oi', 'oip.id_order_invoice = oi.id_order_invoice')
            ->groupBy('op.id_order_payment')
        ;

        foreach ($filters as $name => $value) {
            if ('order_reference' === $name) {
                $queryBuilder->andWhere("o.reference LIKE '%$value%'");
                continue;
            }

            if('customer' === $name) {
                $queryBuilder->add('where',
                    $queryBuilder->expr()->orX(
                        "CONCAT(cu.firstname, ' ', cu.lastname) LIKE :$name",
                        "CONCAT(cu.lastname, ' ', cu.firstname) LIKE :$name",
                        "cu.company LIKE :$name"
                    ));

                $queryBuilder->setParameter($name, '%' . $value . '%');
                continue;
            }

            if ('products' === $name) {
                $queryBuilder->having("group_concat(od.product_name) LIKE :$name");

                $queryBuilder->setParameter($name, '%' . $value . '%');

                continue;
            }

            $queryBuilder->andWhere("$name LIKE '%$value%'");
        }

        return $queryBuilder;
    }
}
