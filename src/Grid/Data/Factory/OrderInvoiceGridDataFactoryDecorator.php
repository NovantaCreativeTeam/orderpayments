<?php

namespace Novanta\OrderPayment\Grid\Data\Factory;

use Configuration;
use Context;
use Currency;
use Hook;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentMethod;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Localization\Locale;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OrderInvoiceGridDataFactoryDecorator implements GridDataFactoryInterface
{
    /**
     * @var GridDataFactoryInterface
     */
    private $orderInvoiceDoctrineGridDataFactory;

    private TranslatorInterface $translator;

    private Locale $locale;

    public function __construct(
        GridDataFactoryInterface $orderInvoiceDoctrineGridDataFactory,
        TranslatorInterface $translator,
        Locale $locale
    ) {
        $this->orderInvoiceDoctrineGridDataFactory = $orderInvoiceDoctrineGridDataFactory;
        $this->translator = $translator;
        $this->locale = $locale;
    }

    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $orderInvoiceData = $this->orderInvoiceDoctrineGridDataFactory->getData($searchCriteria);
        $orderInvoiceRecords = $this->applyModifications($orderInvoiceData->getRecords());

        return new GridData(
            $orderInvoiceRecords,
            $orderInvoiceData->getRecordsTotal(),
            $orderInvoiceData->getQuery()
        );
    }

    private function applyModifications(RecordCollectionInterface $orderInvoices)
    {
        $modifiedOrderInvoices = [];
        $defaultCurrencyIso = Currency::getCurrencyInstance(Context::getContext()->currency->id)->iso_code;

        foreach ($orderInvoices as $orderInvoice) {

            $orderInvoice['invoice_number'] = $this->formatInvoiceNumber($orderInvoice['invoice_number'], $orderInvoice['date_add']);

            $totalOrder = (float) $orderInvoice['total_order'];
            $totalToPay = (float) $totalOrder;
            $totalPaid = (float) ($orderInvoice['total_paid'] ?? 0);

            if($orderInvoice['payment_term'] !== OrderInvoicePaymentTerm::TOTAL->value && $orderInvoice['amount_type'] === 'percentage') {
                $totalToPay = $totalOrder * ($orderInvoice['amount'] / 100);
            } elseif ($orderInvoice['payment_term'] !== OrderInvoicePaymentTerm::TOTAL->value && $orderInvoice['amount_type'] === 'amount') {
                $totalToPay = $orderInvoice['amount'];
            }

            $orderInvoice['total_paid_formatted'] = $this->locale->formatPrice($totalPaid, $defaultCurrencyIso);
            $orderInvoice['total_to_pay_formatted'] = $this->locale->formatPrice($totalToPay, $defaultCurrencyIso);
            $orderInvoice['total_order_formatted'] = $this->locale->formatPrice($totalOrder, $defaultCurrencyIso);

            if ($totalPaid >= $totalToPay) {
                $orderInvoice['total_paid_badge_type'] = 'success';
            } elseif ($totalPaid <= 0) {
                $orderInvoice['total_paid_badge_type'] = 'danger';
            } else {
                $orderInvoice['total_paid_badge_type'] = 'warning';
            }



            $orderInvoice['payment_method_formatted'] = $orderInvoice['payment_method'] ? $this->translator->trans(OrderInvoicePaymentMethod::tryFrom($orderInvoice['payment_method'])->value, [], 'Modules.Orderpayments.Admin')  : '';

            if($orderInvoice['payment_term']) {

                $orderInvoice['payment_term_formatted'] = '';
                if($orderInvoice['payment_term'] == OrderInvoicePaymentTerm::ADVANCE->value) {
                    $orderInvoice['payment_term_formatted'] .= ' - ' . $this->translator->trans('Advance', [], 'Modules.Orderpayments.Admin') . ' ' . $orderInvoice['amount'] . ' ' . ($orderInvoice['amount_type'] === 'percentage' ? '%' : $defaultCurrencyIso);
                } elseif($orderInvoice['payment_term'] == OrderInvoicePaymentTerm::DOWN->value) {
                    $orderInvoice['payment_term_formatted'] .= ' - ' . $this->translator->trans('Down', [], 'Modules.Orderpayments.Admin') . ' ' . $orderInvoice['amount'] . ' ' . ($orderInvoice['amount_type'] === 'percentage' ? '%' : $defaultCurrencyIso);
                } else {
                    $orderInvoice['payment_term_formatted'] .= $this->translator->trans('Total', [], 'Modules.Orderpayments.Admin');
                }
            }

            $modifiedOrderInvoices[] = $orderInvoice;
        }

        return new RecordCollection($modifiedOrderInvoices);
    }

    protected function formatInvoiceNumber($number, $dateAdd) {
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;

        $invoice_formatted_number = Hook::exec('actionInvoiceNumberFormatted', [
            get_class($this) => $this,
            'id_lang' => (int) $id_lang,
            'id_shop' => (int) $id_shop,
            'number' => (int) $number,
        ]);

        if (!empty($invoice_formatted_number)) {
            return $invoice_formatted_number;
        }

        $format = '%1$s%2$06d';

        if (Configuration::get('PS_INVOICE_USE_YEAR')) {
            $format = Configuration::get('PS_INVOICE_YEAR_POS') ? '%1$s%3$s/%2$06d' : '%1$s%2$06d/%3$s';
        }

        return sprintf($format, Configuration::get('PS_INVOICE_PREFIX', (int) $id_lang, null, (int) $id_shop), $number, date('Y', strtotime($dateAdd)));
    }
}
