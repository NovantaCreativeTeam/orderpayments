<?php

namespace Novanta\OrderPayment\Grid\Data\Factory;

use Context;
use OrderInvoice;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Localization\Locale;

final class OrderPaymentGridDataFactoryDecorator implements GridDataFactoryInterface
{
    /**
     * @var GridDataFactoryInterface
     */
    private $orderPaymentDoctrineGridDataFactory;

    /**
     * @var Locale
     */
    private $locale;

    public function __construct(
        GridDataFactoryInterface $orderPaymentDoctrineGridDataFactory,
        Locale $locale
    ) {
        $this->orderPaymentDoctrineGridDataFactory = $orderPaymentDoctrineGridDataFactory;
        $this->locale = $locale;
    }

    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $orderPaymentData = $this->orderPaymentDoctrineGridDataFactory->getData($searchCriteria);
        $orderPaymentRecords = $this->applyModifications($orderPaymentData->getRecords());

        return new GridData(
            $orderPaymentRecords,
            $orderPaymentData->getRecordsTotal(),
            $orderPaymentData->getQuery()
        );
    }

    private function applyModifications(RecordCollectionInterface $orderPayments)
    {
        $modifiedOrderPayments = [];

        foreach ($orderPayments as $orderPayment) {
            $orderPayment['amount_formatted'] = $this->locale->formatPrice(
                $orderPayment['amount'],
                $orderPayment['currency_iso_code']
            );

            if ($orderPayment['id_order_invoice']) {
                $orderInvoice = new OrderInvoice($orderPayment['id_order_invoice']);
                $orderPayment['invoice_number'] = $orderInvoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
            } else {
                $orderPayment['invoice_number'] = '---';
            }

            if (!$orderPayment['transaction_id']) {
                $orderPayment['transaction_id'] = '---';
            }

            $modifiedOrderPayments[] = $orderPayment;
        }

        return new RecordCollection($modifiedOrderPayments);
    }
}
