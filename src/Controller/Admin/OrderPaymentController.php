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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Novanta\OrderPayment\Controller\Admin;

use Novanta\OrderCharging\Domain\OrderDocument\Query\DownloadOrderDocument;
use Novanta\OrderPayment\Domain\OrderPayment\Command\AddOrderPaymentCommand;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;
use Novanta\OrderPayment\Domain\OrderPayment\Query\GetOrderPayments;
use Novanta\OrderPayment\Domain\OrderPayment\QueryResult\OrderPaymentForViewing;
use Novanta\OrderPayment\Domain\OrderPayment\Command\EditOrderPayment;
use Novanta\OrderPayment\Domain\OrderPayment\Command\DeleteOrderPayment;
use PrestaShop\PrestaShop\Core\Domain\Order\Payment\Command\AddPaymentCommand;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShop\PrestaShop\Core\Domain\Order\QueryResult\OrderPayment as OrderPaymentResult;
use Novanta\OrderPayment\Entity\OrderPaymentDocument;
use Order;
use Db;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class OrderPaymentController extends PrestaShopAdminController
{
    public function indexAction()
    {
        return 'OrderPaymentController - index Action';
    }

    /**
     * Function to retrieve all payments for an order
     * @param int $orderId
     * @return JsonResponse
     */
    public function getAllAction(int $orderId): JsonResponse
    {
        try {
            $order = new Order($orderId);
            if (!\Validate::isLoadedObject($order)) {
                return new JsonResponse(['error' => 'Order not found'], 404);
            }

            /** @var OrderPaymentForViewing[] $orderPayments */
            $orderPayments = $this->dispatchQuery(new GetOrderPayments($orderId));

            $totalPaid = 0;
            foreach ($orderPayments as $payment) {
                $totalPaid += $payment->getAmount();
            }

            $totalOrder = $order->total_products_wt + $order->total_shipping_tax_incl + $order->total_wrapping_tax_incl - $order->total_discounts_tax_incl;
            $remaining = $totalOrder - $totalPaid;

            return $this->json([
                'payments' => $orderPayments,
                'totalOrder' => (float)$totalOrder,
                'totalPaid' => (float)$totalPaid,
                'remaining' => (float)$remaining,
                'currencySymbol' => $this->getCurrencyContext()->getSymbol(),
                'currencyIsoCode' => $this->getCurrencyContext()->getIsoCode(),
            ]);

        } catch (\Exception $e) {
            return $this->json(['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))], $this->getHttpErrorCode($e));
        }
    }

    public function addAction(Request $request, int $orderId)
    {
        $amount = $request->request->get('amount');
        $paymentMethod = $request->request->get('paymentMethod');
        $date = $request->request->get('date');
        $transactionId = $request->request->get('transactionId');
        $currencyId = $this->getCurrencyContext()->getId();
        $invoiceId = $request->request->get('orderInvoiceId');
        $employeeId = $this->getEmployeeContext()->getEmployee()->getId();
        $file = $request->files->get('document');

        try {
            $this->dispatchCommand(new AddOrderPaymentCommand(
                $orderId,
                (string) $date,
                (string) $paymentMethod,
                (string) $amount,
                (int) $currencyId,
                (int) $employeeId,
                $invoiceId ? (int) $invoiceId : null,
                $transactionId,
                $file
            ));

            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))], $this->getHttpErrorCode($e));
        }
    }

    /**
     * @param Request $request
     * @param int $paymentId
     * @return JsonResponse
     */
    public function editAction(Request $request, int $paymentId): JsonResponse
    {
        $amount = $request->request->get('amount');
        $paymentMethod = $request->request->get('paymentMethod');
        $date = $request->request->get('date');
        $transactionId = $request->request->get('transactionId');
        $currencyId = $this->getCurrencyContext()->getId();
        $invoiceId = $request->request->get('invoiceId');
        $employeeId = $this->getEmployeeContext()->getEmployee()->getId();
        $file = $request->files->get('document') ?? null;

        try {
            $this->dispatchCommand(new EditOrderPayment(
                $paymentId,
                (string) $date,
                (string) $paymentMethod,
                (string) $amount,
                (int) $currencyId,
                (int) $employeeId,
                $invoiceId ? (int) $invoiceId : null,
                $transactionId,
                $file
            ));

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))], $this->getHttpErrorCode($e));
        }
    }

    public function deleteAction(int $paymentId)
    {
        try {
            $this->dispatchCommand(new DeleteOrderPayment($paymentId));

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))], $this->getHttpErrorCode($e));
        }
    }

    public function downloadAction(int $orderId, int $paymentId, int $documentId)
    {
        try {
            // $orderInvoice = $orderInvoiceRepository->getOrderInvoiceDocument(new OrderInvoiceId($orderInvoiceId), $documentId);

            $query = new DownloadOrderDocument($orderId, $documentId);
            $documentData = $this->dispatchQuery($query);

            $response = new Response($documentData['content']);

            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $documentData['filename']
            );

            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', $documentData['mime']);

            return $response;
        } catch (\Exception $e) {
            return $this->json(
                ['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))],
                $this->getHttpErrorCode($e)
            );
        }
    }

    private function getErrorMessages(\Exception $e): array
    {
        return [
                OrderPaymentException::class => $this->trans('', [], 'Modules.Orderpayments.Notifications')
        ];
    }

    private function getHttpErrorCode(\Exception $e): int
    {
        switch (get_class($e)) {
            case OrderPaymentException::class:
                return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
