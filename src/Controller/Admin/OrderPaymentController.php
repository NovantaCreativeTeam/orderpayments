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

namespace OrderPayment\Controller\Admin;

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShop\PrestaShop\Core\Domain\Order\QueryResult\OrderPayment as OrderPaymentResult;
use OrderPayment\Entity\OrderPaymentDocument;
use OrderPayment\Repository\OrderPaymentDocumentRepository;
use Order;
use DateTime;
use Tools;
use Db;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class OrderPaymentController extends PrestaShopAdminController
{
    public function indexAction()
    {
        return 'OrderPaymentController - index Action';
    }

    public function getAllAction(int $orderId)
    {
        $order = new Order($orderId);
        if (!\Validate::isLoadedObject($order)) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        $payments = $order->getOrderPaymentCollection();
        $paymentsData = [];
        $totalPaid = 0;

        /** @var OrderPaymentDocumentRepository $repository */
        $repository = $this->get('OrderPayment\Repository\OrderPaymentDocumentRepository');

        foreach ($payments as $payment) {
            $totalPaid += $payment->amount;
            $doc = $repository->findOneBy(['orderPaymentId' => $payment->id]);
            
            $paymentsData[] = [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'date' => $payment->date_add,
                'method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'id_order_invoice' => $payment->id_order_invoice,
                'document' => $doc ? $doc->getOriginalFilename() : null,
                'document_id' => $doc ? $doc->getOrderPaymentId() : null,
            ];
        }

        $totalOrder = $order->getTotalPaid();
        $remaining = $totalOrder - $totalPaid;

        return new JsonResponse([
            'payments' => $paymentsData,
            'totalOrder' => (float)$totalOrder,
            'totalPaid' => (float)$totalPaid,
            'remaining' => (float)$remaining,
            'currencySymbol' => $this->getContext()->currency->symbol,
            'invoices' => array_map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'note' => $invoice->getNote(),
                    'total' => $invoice->total_paid_tax_incl,
                ];
            }, $order->getInvoicesCollection()->all()),
        ]);
    }

    public function addAction(Request $request, int $orderId)
    {
        $order = new Order($orderId);
        if (!\Validate::isLoadedObject($order)) {
             return new JsonResponse(['success' => false, 'message' => 'Order not found'], 404);
        }

        $amount = (float)$request->request->get('amount');
        $paymentMethod = $request->request->get('payment_method');
        $date = $request->request->get('date');
        $transactionId = $request->request->get('transaction_id');
        $idInvoice = (int)$request->request->get('id_invoice');

        $payment = new \OrderPayment();
        $payment->order_reference = $order->reference;
        $payment->amount = $amount;
        $payment->payment_method = $paymentMethod;
        $payment->date_add = $date ? str_replace('T', ' ', $date) : date('Y-m-d H:i:s');
        $payment->transaction_id = $transactionId;
        $payment->id_currency = $order->id_currency;
        $payment->conversion_rate = 1;
        
        if ($payment->add()) {
            if ($idInvoice > 0) {
                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'order_invoice_payment (id_order_invoice, id_order_payment, id_order) VALUES (' . (int)$idInvoice . ', ' . (int)$payment->id . ', ' . (int)$orderId . ')');
            }

            // Handle file upload
            $file = $request->files->get('document');
            if ($file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->guessExtension();
                $newName = md5(uniqid()) . '.' . $extension;
                $uploadDir = $this->getParameter('kernel.project_dir') . '/upload/orderpayment/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $file->move($uploadDir, $newName);

                $doc = new OrderPaymentDocument();
                $doc->setOrderPaymentId($payment->id);
                $doc->setFilename($newName);
                $doc->setOriginalFilename($originalName);

                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($doc);
                $em->flush();
            }

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Error adding payment']);
    }

    /**
     * @param Request $request
     * @param int $paymentId
     * @return JsonResponse
     */
    public function editAction(Request $request, int $paymentId): JsonResponse
    {
        $amount = $request->request->get('amount');
        $paymentMethod = $request->request->get('payment_method');
        $date = $request->request->get('date');
        $transactionId = $request->request->get('transaction_id');
        $idInvoice = $request->request->get('id_invoice');

        try {
            $payment = new \PsOrderPayment($paymentId);
            if (!\Validate::isLoadedObject($payment)) {
                return new JsonResponse(['success' => false, 'message' => 'Payment not found']);
            }

            $payment->amount = (float)$amount;
            $payment->payment_method = $paymentMethod;
            $payment->date_add = $date;
            $payment->transaction_id = $transactionId;
            // Note: id_invoice usually is not directly on OrderPayment in standard PS, 
            // but this depends on how the module is implemented.
            
            if ($payment->update()) {
                return new JsonResponse(['success' => true]);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Error updating payment']);
    }

    public function deleteAction(int $paymentId)
    {
        $payment = new PsOrderPayment($paymentId);
        if (\Validate::isLoadedObject($payment)) {
            $payment->delete();
            
            // Delete associated document
            $repository = $this->get('OrderPayment\Repository\OrderPaymentDocumentRepository');
            $doc = $repository->findOneBy(['orderPaymentId' => $paymentId]);
            if ($doc) {
                $filePath = $this->getParameter('kernel.project_dir') . '/upload/orderpayment/' . $doc->getFilename();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $em = $this->get('doctrine.orm.entity_manager');
                $em->remove($doc);
                $em->flush();
            }

            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false, 'message' => 'Payment not found']);
    }

    public function downloadAction(int $paymentId)
    {
        $repository = $this->get('OrderPayment\Repository\OrderPaymentDocumentRepository');
        $doc = $repository->findOneBy(['orderPaymentId' => $paymentId]);
        
        if (!$doc) {
            throw $this->createNotFoundException('Document not found');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/upload/orderpayment/' . $doc->getFilename();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $doc->getOriginalFilename()
        );

        return $response;
    }
}
