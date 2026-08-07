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

namespace Novanta\OrderPayment\Adapter\OrderPayment\QueryHandler;

use Novanta\OrderPayment\Domain\OrderPayment\Query\GetOrderPayments;
use Novanta\OrderPayment\Domain\OrderPayment\QueryHandler\GetOrderPaymentsHandlerInterface;
use Novanta\OrderPayment\Domain\OrderPayment\QueryResult\EmployeeForViewing;
use Novanta\OrderPayment\Domain\OrderPayment\QueryResult\OrderInvoiceForViewing;
use Novanta\OrderPayment\Domain\OrderPayment\QueryResult\OrderPaymentForViewing;
use Novanta\OrderPayment\Repository\OrderPaymentDocumentRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use Order;
use Employee;
use Validate;

#[AsQueryHandler]
class GetOrderPaymentsHandler implements GetOrderPaymentsHandlerInterface
{
    /**
     * @var OrderPaymentDocumentRepository
     */
    private $repository;

    /**
     * @param OrderPaymentDocumentRepository $repository
     */
    public function __construct(OrderPaymentDocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetOrderPayments $query): array
    {
        $order = new Order($query->getOrderId());
        if (!Validate::isLoadedObject($order)) {
            return [];
        }

        $payments = $order->getOrderPaymentCollection();
        $paymentsData = [];

        foreach ($payments as $payment) {
            $doc = $this->repository->findOneBy(['orderPaymentId' => $payment->id]);
            /** @var \OrderInvoice $invoice */
            $invoice = $payment->getOrderInvoice($order->id);
            
            $employee = $payment->id_employee ? new Employee($payment->id_employee) : null;

            $paymentsData[] = new OrderPaymentForViewing(
                (int)$payment->id,
                (float)$payment->amount,
                (string)$payment->payment_method,
                $payment->transaction_id,
                $invoice ? new OrderInvoiceForViewing((int)$invoice->id, $invoice->getInvoiceNumberFormatted((int)$order->id_lang)) : null,
                $employee ? new EmployeeForViewing((int)$employee->id, $employee->firstname, $employee->lastname) : null,
                $doc ? $doc->getOriginalFilename() : null,
                $doc ? $doc->getOrderPaymentId() : null,
                $payment->date_add
            );
        }

        return $paymentsData;
    }
}
