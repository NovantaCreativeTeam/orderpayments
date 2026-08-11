<?php

namespace Novanta\OrderPayment\Adapter\OrderInvoice\CommandHandler;

use Address;
use Carrier;
use Novanta\OrderCharging\Domain\OrderCharging\Exception\OrderInvoiceException;
use Novanta\OrderPayment\Domain\OrderInvoice\Command\AddOrderInvoiceCommand;
use Novanta\OrderPayment\Domain\OrderInvoice\CommandHandler\AddOrderInvoiceHandlerInterface;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\Exception\InvoiceException;
use PrestaShop\PrestaShop\Core\Domain\Order\Invoice\ValueObject\OrderInvoiceId;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use Order;
use OrderInvoice;
use Db;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

#[AsCommandHandler]
class AddOrderInvoiceHandler implements AddOrderInvoiceHandlerInterface
{
    private Configuration $configuration;

    public function __construct(
        Configuration $configuration
    )
    {
        $this->configuration = $configuration;
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
        $orderInvoice->number = 0;

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
            $order->setLastInvoiceNumber($orderInvoice->id, $order->id_shop);
        }

        // Valorizzare la tabella order_invoice_proforma
        $this->createProformaInfo($orderInvoice->id, $command);

        return new OrderInvoiceId((int)$orderInvoice->id);
    }

    /**
     * @param int $orderInvoiceId
     * @param AddOrderInvoiceCommand $command
     * @return void
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
}
