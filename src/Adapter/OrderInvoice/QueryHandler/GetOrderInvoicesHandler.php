<?php
/**
 * 2007-2026 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2026 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Novanta\OrderPayment\Adapter\OrderInvoice\QueryHandler;

use Novanta\OrderPayment\Adapter\OrderInvoice\Repository\OrderInvoiceRepository;
use Novanta\OrderPayment\Domain\OrderInvoice\Query\GetOrderInvoices;
use Novanta\OrderPayment\Domain\OrderInvoice\QueryHandler\GetOrderInvoicesHandlerInterface;
use Novanta\OrderPayment\Domain\OrderInvoice\QueryResult\OrderInvoiceForViewing;
use Order;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use Validate;

#[AsQueryHandler]
class GetOrderInvoicesHandler implements GetOrderInvoicesHandlerInterface
{
    private OrderInvoiceRepository $orderInvoiceRepository;
    private $languageId;

    public function __construct(
        OrderInvoiceRepository $orderInvoiceRepository,
        $languageId
    ) {
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->languageId = $languageId;
    }

    /**
     * {@inheritdoc}
     * @throws OrderException
     */
    public function handle(GetOrderInvoices $query): array
    {
        $order = new Order($query->getOrderId());
        if (!Validate::isLoadedObject($order)) {
            throw new OrderException('The order is invalid.');
        }

        $invoices = $this->orderInvoiceRepository->getByOrderId(new OrderId($query->getOrderId()));
        $invoicesData = [];

        /** @var \OrderInvoice $invoice */
        foreach ($invoices as $invoice) {

            $orderInvoice = new \OrderInvoice($invoice['id_order_invoice']);

            $totalOrder = $order->total_products_wt + $order->total_shipping_tax_incl + $order->total_wrapping_tax_incl - $order->total_discounts_tax_incl;
            $totalToPay = $totalOrder;
            if($invoice['amount_type'] === 'percentage') {
                $totalToPay = $totalOrder * ($invoice['amount'] / 100);
            } elseif ( $invoice['amount_type'] === 'amount') {
                $totalToPay = $invoice['amount'];
            }

            $invoicesData[] = new OrderInvoiceForViewing(
                (int)$invoice['id_order_invoice'],
                $orderInvoice->getInvoiceNumberFormatted($this->languageId), // ToDo: utilizzare la funzione getInvoiceNumberFormatted della classe ORderInvoice per formattare correttamente il numero
                $invoice['payment_method'],
                $invoice['payment_term'],
                $invoice['amount_type'],
                (float)$invoice['amount'],
                $invoice['note'],
                (float)$invoice['total_paid_tax_incl'],
                $totalToPay,
                $invoice['date_add']
            );
        }

        return $invoicesData;
    }
}
