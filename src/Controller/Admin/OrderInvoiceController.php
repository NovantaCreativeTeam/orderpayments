<?php

namespace Novanta\OrderPayment\Controller\Admin;

use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceNotFoundException;
use Novanta\OrderCharging\Domain\OrderDocument\Query\DownloadOrderDocument;
use Novanta\OrderPayment\Adapter\OrderInvoice\Repository\OrderInvoiceRepository;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\AddOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\DeleteOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\EditOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentMethod;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm;
use Novanta\OrderPayment\Domain\OrderInvoice\Query\GetOrderInvoices;
use Novanta\OrderPayment\Domain\OrderPayment\Exception\OrderPaymentException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Novanta\OrderPayment\Search\Filters\OrderInvoiceFilters;
use Novanta\OrderPayment\Grid\Definition\Factory\OrderInvoiceDefinitionFactory;

class OrderInvoiceController extends PrestaShopAdminController
{
    public function indexAction(
        Request $request,
        OrderInvoiceFilters $filters,
        #[Autowire(service: 'novanta.orderpayment.grid.order_invoice_grid_factory')] $gridFactory)
    {
        $grid = $gridFactory->getGrid($filters);

        return $this->render(
            '@Modules/orderpayments/views/templates/admin/order_invoice/grid.html.twig',
            [
                'layoutTitle' => $this->trans('Order Invoices', [], 'Modules.Orderpayments.Admin'),
                'orderInvoiceGrid' => $this->presentGrid($grid),
            ]
        );
    }

    public function searchAction(Request $request)
    {
        /** @var \PrestaShopBundle\Component\Grid\ResponseBuilder $responseBuilder */
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        return $responseBuilder->buildSearchResponse(
            $this->get('novanta.orderpayment.grid.definition.factory.order_invoice'),
            $request,
            OrderInvoiceDefinitionFactory::GRID_ID,
            'admin_order_invoices_index'
        );
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
                $data['deliveryDate'],
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

    public function downloadAction(
        int $orderInvoiceId,
        int $documentId,
        #[Autowire(service: 'Novanta\OrderPayment\Adapter\OrderInvoice\Repository\OrderInvoiceRepository')] OrderInvoiceRepository $orderInvoiceRepository,
    ) {
        try {

            $orderInvoice = $orderInvoiceRepository->getOrderInvoiceDocument(new OrderInvoiceId($orderInvoiceId), $documentId);

            $query = new DownloadOrderDocument($orderInvoice['id_order'], $orderInvoice['id_order_document']);
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
        return match (get_class($e)) {
            OrderNotFoundException::class, OrderInvoiceNotFoundException::class => Response::HTTP_NOT_FOUND,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

    }

}
