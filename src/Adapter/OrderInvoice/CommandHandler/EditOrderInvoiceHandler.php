<?php

namespace Novanta\OrderPayment\Adapter\OrderInvoice\CommandHandler;

use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceException;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceNotFoundException;
use Novanta\OrderCharging\Domain\OrderDocument\Command\DeleteOrderDocument;
use Novanta\OrderCharging\Domain\OrderDocument\Command\UploadDocumentToOrder;
use Novanta\OrderCharging\Domain\OrderDocument\Query\GetOrderDocuments;
use Novanta\OrderCharging\Domain\OrderDocument\QueryResult\ViewableOrderDocument;
use Novanta\OrderPayment\Adapter\OrderInvoice\Repository\OrderInvoiceRepository;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\EditOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler\EditOrderInvoiceHandlerInterface;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\ContextStateManager;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShop\PrestaShop\Core\Language\LanguageRepositoryInterface;
use PrestaShop\PrestaShop\Core\PDF\PDFGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use OrderInvoice;
use Db;

#[AsCommandHandler]
class EditOrderInvoiceHandler implements EditOrderInvoiceHandlerInterface
{
    private Configuration $configuration;
    private PDFGeneratorInterface $pdfGenerator;
    private ContextStateManager $contextStateManager;
    private LanguageRepositoryInterface $languageRepository;
    private CommandBusInterface $commandBus;
    private OrderInvoiceRepository $orderInvoiceRepository;

    public function __construct(
        Configuration $configuration,
        PDFGeneratorInterface $pdfGenerator,
        ContextStateManager $contextStateManager,
        LanguageRepositoryInterface $languageRepository,
        CommandBusInterface $commandBus,
        OrderInvoiceRepository $orderInvoiceRepository
    )
    {
        $this->configuration = $configuration;
        $this->pdfGenerator = $pdfGenerator;
        $this->contextStateManager = $contextStateManager;
        $this->languageRepository = $languageRepository;
        $this->commandBus = $commandBus;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    /**
     * @param EditOrderInvoiceCommand $command
     * @throws OrderInvoiceNotFoundException
     * @throws OrderInvoiceException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(EditOrderInvoiceCommand $command): void
    {
        $orderInvoice = new OrderInvoice($command->getOrderInvoiceId()->getValue());

        if ($orderInvoice->id === null) {
            throw new OrderInvoiceNotFoundException($command->getOrderInvoiceId(), sprintf('Order invoice with id "%d" was not found.', $command->getOrderInvoiceId()->getValue()));
        }

        $orderInvoice->delivery_date = pSQL($command->getDeliveryDate()->format('Y-m-d H:i:s'));
        $orderInvoice->note = pSQL($command->getNote());

        if (!$orderInvoice->update()) {
            throw new OrderInvoiceException(sprintf('Could not update order invoice with id "%d".', $command->getOrderInvoiceId()->getValue()));
        }

        $this->updateProformaInfo($command->getOrderInvoiceId()->getValue(), $command);

        // Remove old PDF document
        $this->deleteOldPdfDocument($orderInvoice);

        $pdfContent = $this->pdfGenerator->generatePDF([$orderInvoice->id]);
        $this->uploadPdfDocument($orderInvoice, $pdfContent);
    }

    /**
     * @param OrderInvoice $orderInvoice
     * @return void
     */
    private function deleteOldPdfDocument(OrderInvoice $orderInvoice): void
    {
        $documents = $this->orderInvoiceRepository->getOrderInvoiceDocuments(new OrderInvoiceId($orderInvoice->id));
        foreach ($documents as $document) {
            $this->commandBus->handle(new DeleteOrderDocument((int)$orderInvoice->id_order, $document['id_order_document']));
            $this->orderInvoiceRepository->deleteOrderInvoiceDocument(new OrderInvoiceId((int)$orderInvoice->id), $document['id_order_document']);
        }
    }

    /**
     * Upload PDF as OrderDocument
     *
     * @param OrderInvoice $orderInvoice
     * @param string $pdfContent
     *
     * @return void
     */
    private function uploadPdfDocument($orderInvoice, string $pdfContent): void
    {
        // Prepare filename
        $fileName = 'order_invoice_' . $orderInvoice->number . '_' . date('YmdHis') . '.pdf';
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempPath, $pdfContent);

        $file = new UploadedFile(
            $tempPath,
            $fileName,
            'application/pdf',
            null,
            true // test mode
        );

        $languages = $this->languageRepository->findAll();
        $localizedNames = [];
        $localizedDescriptions = [];

        foreach ($languages as $language) {
            $localizedNames[$language->getId()] = $this->contextStateManager->getContext()->getTranslator()->trans(
                'Order Invoice %s',
                [$orderInvoice->number],
                'Modules.OrderPayment.Admin',
                $language->getLocale()
            );
            $localizedDescriptions[$language->getId()] = $this->contextStateManager->getContext()->getTranslator()->trans(
                'Order Invoice Document',
                [],
                'Modules.OrderPayment.Admin',
                $language->getLocale()
            );
        }

        $documentId = $this->commandBus->handle(new UploadDocumentToOrder(
            (int)$orderInvoice->id_order,
            $file,
            $localizedNames,
            $localizedDescriptions
        ));

        $this->orderInvoiceRepository->addOrderInvoiceDocument(new OrderInvoiceId((int)$orderInvoice->id), $documentId);
    }

    /**
     * @param int $orderInvoiceId
     * @param EditOrderInvoiceCommand $command
     * @return void
     */
    private function updateProformaInfo(int $orderInvoiceId, EditOrderInvoiceCommand $command): void
    {
        Db::getInstance()->update(
            'order_invoice_proforma',
            [
                'payment_method' => pSQL($command->getPaymentMethod()?->value),
                'payment_term' => pSQL($command->getPaymentTerm()?->value),
                'amount_type' => pSQL($command->getAmountType()),
                'amount' => (float)$command->getAmount(),
            ],
            'id_order_invoice = ' . (int)$orderInvoiceId
        );
    }
}
