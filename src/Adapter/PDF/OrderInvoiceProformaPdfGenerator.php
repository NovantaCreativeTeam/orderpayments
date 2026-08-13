<?php

namespace Novanta\OrderPayment\Adapter\PDF;

use Context;
use Novanta\OrderPayment\Adapter\OrderInvoice\Repository\OrderInvoiceRepository;
use PDF;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\PDF\PDFGeneratorInterface;

class OrderInvoiceProformaPdfGenerator implements PDFGeneratorInterface
{
    private OrderInvoiceRepository $orderInvoiceRepository;

    public function __construct(
        OrderInvoiceRepository $orderInvoiceRepository,

    )
    {
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    /**
     * @param array $orderInvoiceId
     * @return string
     * @throws CoreException
     */
    public function generatePDF(array $orderInvoiceId)
    {
        if (count($orderInvoiceId) !== 1) {
            throw new CoreException(sprintf('"%s" supports generating invoice for single order only.', self::class));
        }

        $orderInvoiceId = reset($orderInvoiceId);
        $orderInvoice = $this->orderInvoiceRepository->get(new OrderInvoiceId((int)$orderInvoiceId));

        $pdf = new PDF([$orderInvoice], 'InvoiceProforma', Context::getContext()->smarty);
        $pdf->pdf_renderer->setFont('manrope', '', 9, '', false);
        $pdf->pdf_renderer->setFont('manropeb', '', 9, '', false);

        return $pdf->render(false);
    }


}
