<?php

namespace Novanta\OrderPayment\Adapter\OrderInvoice\CommandHandler;

use Address;
use Carrier;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceException;
use Novanta\OrderCharging\Domain\OrderDocument\Command\UploadDocumentToOrder;
use Novanta\OrderCharging\Entity\OrderDocument;
use Novanta\OrderCharging\Entity\OrderDocumentLang;
use Novanta\OrderCharging\Entity\PurchaseRequest;
use Novanta\OrderPayment\Adapter\OrderInvoice\Repository\OrderInvoiceRepository;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\AddOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler\AddOrderInvoiceHandlerInterface;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\ContextStateManager;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\Exception\InvoiceException;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use Order;
use OrderInvoice;
use Db;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Language\LanguageRepositoryInterface;
use PrestaShop\PrestaShop\Core\PDF\PDFGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommandHandler]
class AddOrderInvoiceHandler implements AddOrderInvoiceHandlerInterface
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
     * @param AddOrderInvoiceCommand $command
     * @return OrderInvoiceId
     * @throws InvoiceException
     * @throws OrderInvoiceException
     * @throws OrderNotFoundException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws ShopException
     */
    public function handle(AddOrderInvoiceCommand $command): OrderInvoiceId
    {
        $order = new Order($command->getOrderId()->getValue());

        if ($order->id === null) {
            throw new OrderNotFoundException($command->getOrderId(), sprintf('Order with id "%d" was not found.', $command->getOrderId()->getValue()));
        }

        $orderInvoice = new OrderInvoice();
        $orderInvoice->id_order = $order->id;
        $orderInvoice->number = Order::getLastInvoiceNumber() + 1;

        $orderInvoice->total_discount_tax_excl = $order->total_discounts_tax_excl;
        $orderInvoice->total_discount_tax_incl = $order->total_discounts_tax_incl;
        $orderInvoice->total_paid_tax_excl = 0;
        $orderInvoice->total_paid_tax_incl = 0;
        $orderInvoice->total_products = $order->total_products;
        $orderInvoice->total_products_wt = $order->total_products_wt;
        $orderInvoice->total_shipping_tax_excl = $order->total_shipping_tax_excl;
        $orderInvoice->total_shipping_tax_incl = $order->total_shipping_tax_incl;
        $orderInvoice->total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
        $orderInvoice->total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
        $orderInvoice->delivery_date = pSQL($command->getShippingDate()->format('Y-m-d H:i:s'));
        $orderInvoice->note = pSQL($command->getNote());


        if (!$orderInvoice->add()) {
            throw new OrderInvoiceException('Could not create order invoice.');
        }

        $invoice_address = new Address(
            (int) $order->{$this->configuration->get('PS_TAX_ADDRESS_TYPE', null, ShopConstraint::shop($order->id_shop))}
        );
        $carrier = new Carrier((int) $order->id_carrier);
        $taxCalculator = $carrier->getTaxCalculator($invoice_address);

        $orderInvoice->shipping_tax_computation_method = $taxCalculator->computation_method;
        $orderInvoice->saveCarrierTaxCalculator($taxCalculator->getTaxesAmount($orderInvoice->total_shipping_tax_excl));
        
        // Impostazione del numero fattura se abilitate
        if ($this->configuration->get('PS_INVOICE')) {
            $order->invoice_number = $orderInvoice->number;
            $order->update();
        }

        // Valorizzare la tabella order_invoice_proforma
        $this->createProformaInfo($orderInvoice->id, $command);
        $pdfContent = $this->pdfGenerator->generatePDF([$orderInvoice->id]);

        $this->uploadPdfDocument($orderInvoice, $pdfContent);

        return new OrderInvoiceId((int)$orderInvoice->id);
    }

    /**
     * @param int $orderInvoiceId
     * @param AddOrderInvoiceCommand $command
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function createProformaInfo(int $orderInvoiceId, AddOrderInvoiceCommand $command): void
    {
        Db::getInstance()->insert('order_invoice_proforma', [
            'id_order_invoice' => $orderInvoiceId,
            'payment_method' => pSQL($command->getPaymentMethod()?->value),
            'payment_term' => pSQL($command->getPaymentTerm()?->value),
            'amount_type' => pSQL($command->getAmountType()),
            'amount' => (float)$command->getAmount(),
        ]);
    }

    /**
     * Upload PDF as OrderDocument
     *
     * @param OrderInvoice $orderInvoice
     * @param string $pdfContent
     *
     * @return void
     * @throws InvoiceException
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
            true
        );

        $languages = $this->languageRepository->findAll();
        $localizedNames = [];
        $localizedDescriptions = [];

        foreach ($languages as $language) {
            $localizedNames[$language->getId()] = $this->contextStateManager->getContext()->getTranslator()->trans(
                'Order Invoice %s',
                [$orderInvoice->number],
                'Modules.OrderPayment.Admin'
            );
            $localizedDescriptions[$language->getId()] = $this->contextStateManager->getContext()->getTranslator()->trans(
                'Order Invoice Document',
                [],
                'Modules.OrderPayment.Admin'
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
}
