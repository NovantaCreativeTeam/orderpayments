<?php

namespace Novanta\OrderPayment\Controller\Admin;

use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceNotFoundException;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\AddOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\DeleteOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\EditOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentMethod;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm;
use Novanta\OrderPayment\Domain\OrderInvoice\Query\GetOrderInvoices;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderInvoiceController extends PrestaShopAdminController
{
    public function indexAction()
    {
        return 'OrderInvoiceController - Index Action';
    }

    public function addAction(int $orderId, Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();
            
            $command = new AddOrderInvoiceCommand(
                $orderId,
                $data['paymentMethod'] ? OrderInvoicePaymentMethod::from($data['paymentMethod']) : null,
                $data['paymentTerm'] ? OrderInvoicePaymentTerm::from($data['paymentTerm']) : null,
                $data['amountType'],
                (float)$data['amount'],
                $data['shippingDate'],
                $data['note'] ?? null
            );

            $orderInvoiceId = $this->dispatchCommand($command);

            return $this->json([
                'id' => $orderInvoiceId->getValue(),
                'message' => $this->trans('Successful creation.', [], 'Admin.Notifications.Success'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))
            ], $this->getHttpErrorCode($e));
        }
    }

    public function getAllAction(int $orderId): JsonResponse
    {
        try {
            $orderInvoices = $this->dispatchQuery(new GetOrderInvoices($orderId));

            return $this->json($orderInvoices);
        } catch (\Exception $e) {
            return $this->json(['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))], $this->getHttpErrorCode($e));
        }
    }

    public function editAction(int $orderInvoiceId, Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();

            $command = new EditOrderInvoiceCommand(
                $orderInvoiceId,
                $data['paymentMethod'] ? OrderInvoicePaymentMethod::from($data['paymentMethod']) : null,
                $data['paymentTerm'] ? OrderInvoicePaymentTerm::from($data['paymentTerm']) : null,
                $data['amountType'],
                (float)$data['amount'],
                $data['deliveryDate'],
                $data['note'] ?? null
            );

            $this->dispatchCommand($command);

            return $this->json([
                'message' => $this->trans('Successful update.', [], 'Admin.Notifications.Success'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))
            ], $this->getHttpErrorCode($e));
        }
    }

    public function deleteAction(int $orderId, int $orderInvoiceId): JsonResponse
    {
        try {
            $this->dispatchCommand(new DeleteOrderInvoiceCommand($orderInvoiceId));

            return $this->json([
                'message' => $this->trans('Successful deletion.', [], 'Admin.Notifications.Success'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))
            ], $this->getHttpErrorCode($e));
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
        return match (get_class($e)) {
            OrderNotFoundException::class, OrderInvoiceNotFoundException::class => Response::HTTP_NOT_FOUND,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

    }

}
