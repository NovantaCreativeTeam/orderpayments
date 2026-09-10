<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */


use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentMethod;
use Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HTMLTemplateInvoiceProforma extends HTMLTemplate
{
    private $orderInvoice;
    private $customer;
    private $context;
    private $contextStateManager;
    private $priceFormatter;
    private $orderDetails;

    public function __construct($orderInvoice, $smarty)
    {
        $this->smarty = $smarty;
        $this->orderInvoice = $orderInvoice;
        $this->order = $this->getOrder(new OrderId($orderInvoice['id_order']));

        // Load customer
        $this->customer = new \Customer($this->order->id_customer);

        // Setup context
        $legacyContext = new PrestaShop\PrestaShop\Adapter\LegacyContext();
        $this->contextStateManager = new PrestaShop\PrestaShop\Adapter\ContextStateManager($legacyContext);
        $this->contextStateManager
            ->setCustomer($this->customer)
            ->setLanguage($this->customer->getAssociatedLanguage())
            ->setCurrency(new Currency($this->order->id_currency));

        $this->context = $this->contextStateManager->getContext();

        // Price formatter
        $this->priceFormatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();

        // Load request products
        $this->orderDetails = $this->order->getOrderDetailList();
    }

    /**
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->assignCommonHeaderData();

        $contextLocale = Tools::getContextLocale($this->context);
        $currencyIsoCode = Currency::getIsoCodeById((int)$this->context->currency->id);

        // Filter only requested products
        $products = [];
        $total_tax_excl = 0;
        $total_taxes = 0;

        $address = new Address($this->order->id_address_invoice);
        if (!Validate::isLoadedObject($address)) {
            $address = Address::initialize();
        }

        foreach ($this->orderDetails as $orderDetail) {

            $quantity = (int)$orderDetail['product_quantity'];

            // Calculate taxes for this line using store default taxes
            $product = new Product($orderDetail['product_id']);
            $id_tax_rules_group = $product->getIdTaxRulesGroup();
            $tax_manager = TaxManagerFactory::getManager($address, $id_tax_rules_group);
            $tax_calculator = $tax_manager->getTaxCalculator();

            $line_taxes = 0;

            if ($tax_calculator instanceof TaxCalculator) {
                $line_taxes = $tax_calculator->getTaxesTotalAmount($orderDetail['total_price_tax_excl']);
            }

            $total_tax_excl += $orderDetail['total_price_tax_excl'];
            $total_taxes += $line_taxes;

            $products[] = [
                'name' => $orderDetail['product_name'],
                'reference' => !empty($productSupplierData) && array_key_exists('product_supplier_reference', $productSupplierData) ? $productSupplierData['product_supplier_reference'] : $orderDetail['product_reference'],
                'unit_price' => $contextLocale->formatPrice($orderDetail['unit_price_tax_excl'], $currencyIsoCode),
                'full_price' => $contextLocale->formatPrice($orderDetail['total_price_tax_excl'], $currencyIsoCode),
                'quantity' => $quantity,
                'tax_rate' => $tax_calculator->getTotalRate()
            ];
        }

        $total_to_pay = $total_tax_excl + $total_taxes;
        if($this->orderInvoice['payment_term'] !== \Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm::TOTAL->value) {
            if($this->orderInvoice['amount_type'] === 'percentage') {
                $total_to_pay = ($total_tax_excl + $total_taxes) * ($this->orderInvoice['amount'] / 100);
            } elseif ($this->orderInvoice['amount_type'] === 'amount') {
                $total_to_pay = $this->orderInvoice['amount'];
            }
        }

        $payment_description = $this->contextStateManager->getContext()->getTranslator()->trans('Order', [], 'Admin.Global') . ' ' . $this->order->reference;
        if($this->orderInvoice['payment_term'] === \Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm::ADVANCE->value) {
            $payment_description .= ' - ' . $this->contextStateManager->getContext()->getTranslator()->trans('Advance', [], 'Admin.Global');
        } elseif($this->orderInvoice['payment_term'] === \Novanta\OrderPayment\Domain\OrderInvoice\OrderInvoicePaymentTerm::DOWN->value) {
            $payment_description .= ' - ' . $this->contextStateManager->getContext()->getTranslator()->trans('Down', [], 'Admin.Global');
        }



        $orderInvoiceObj = new OrderInvoice($this->orderInvoice['id_order_invoice']);

        // Assign variables to template
        $this->smarty->assign([
            'invoice' => [
                'id' => $this->orderInvoice['id_order_invoice'],
                'number' => $orderInvoiceObj->getInvoiceNumberFormatted($this->contextStateManager->getContext()->language->id),
                'notes' => $this->orderInvoice['note'],
                'date_add' => (new DateTime($this->orderInvoice['date_add']))->format(\Context::getContext()->language->date_format_lite),
                'payment_method' => $this->orderInvoice['payment_method'],
                'payment_term' => $this->orderInvoice['payment_term'],
                'payment_method_description' => OrderInvoicePaymentMethod::tryFrom($this->orderInvoice['payment_method']) ? $this->contextStateManager->getContext()->getTranslator()->trans($this->orderInvoice['payment_method'], [], 'Modules.Orderpayments.Admin') : $this->orderInvoice['payment_method'],
                'payment_term_description' => OrderInvoicePaymentTerm::tryFrom($this->orderInvoice['payment_term']) ? $this->contextStateManager->getContext()->getTranslator()->trans($this->orderInvoice['payment_term'], [], 'Modules.Orderpayments.Admin') : $this->orderInvoice['payment_term'],
                'payment_description' => $payment_description,
                'amount_type' => $this->orderInvoice['amount_type'],
                'amount' => $this->orderInvoice['amount'],
                'delivery_date' => (new DateTime($this->orderInvoice['delivery_date']))->format(\Context::getContext()->language->date_format_lite),
                'bankwire_owner' => Configuration::get('BANK_WIRE_OWNER'),
                'bankwire_details' => nl2br(Configuration::get('BANK_WIRE_DETAILS') ?: ''),
                'bankwire_address' => nl2br(Configuration::get('BANK_WIRE_ADDRESS') ?: ''),
            ],
            'products' => $products,
            'totals' => [
                'tax_excluded' => $contextLocale->formatPrice($total_tax_excl, $currencyIsoCode),
                'tax_included' => $contextLocale->formatPrice($total_tax_excl + $total_taxes, $currencyIsoCode),
                'total_taxes' => $contextLocale->formatPrice($total_taxes, $currencyIsoCode),
                'total_to_pay' => $contextLocale->formatPrice($total_to_pay, $currencyIsoCode)
            ],
            'order' => [
                'reference' => $this->order->reference,
                'date_add' => (new DateTime($this->order->date_add))->format(\Context::getContext()->language->date_format_lite),
                'shipping_address' => $this->order->id_address_invoice ? $this->customer->getSimpleAddress($this->order->id_address_invoice) : [],
            ],
            'customer' => [
                'firstname' => $this->customer->firstname,
                'lastname' => $this->customer->lastname,
                'email' => $this->customer->email,
                'invoice_address' => $this->order->id_address_invoice ? $this->customer->getSimpleAddress($this->order->id_address_invoice) : [],
                'delivery_address' => $this->order->id_address_delivery ? $this->customer->getSimpleAddress($this->order->id_address_delivery) : [],
            ],
        ]);

        // Load template parts
        $tpls = [
            'style_tab' => $this->smarty->fetch($this->getTemplate('invoice-proforma.style-tab')),
            'header_tab' => $this->smarty->fetch($this->getTemplate('invoice-proforma.header-tab')),
            'product_tab' => $this->smarty->fetch($this->getTemplate('invoice-proforma.product-tab')),
            'footer_tab' => $this->smarty->fetch($this->getTemplate('invoice-proforma.footer-tab')),
        ];

        $this->smarty->assign($tpls);

        return $this->smarty->fetch($this->getTemplate('invoice-proforma'));
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return 'proforma_invoice_' . $this->orderInvoice['number'] . '.pdf';
    }

    /**
     * @return string
     */
    public function getBulkFilename(): string
    {
        return $this->getFilename();
    }

    /**
     * @param $template_name
     * @return false|string
     */
    protected function getTemplate($template_name): false|string
    {
        $template = false;
        $default_template = rtrim(_PS_PDF_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $template_name . '.tpl';
        $module_template = _PS_MODULE_DIR_ . 'orderpayments/views/templates/admin/pdf/' . $template_name . '.tpl';
        $overridden_template = $this->shop->theme->getDirectory() . DIRECTORY_SEPARATOR . 'modules/orderpayments/views/templates/admin/pdf/' . $template_name . '.tpl';

        if (file_exists($overridden_template)) {
            $template = $overridden_template;
        } elseif (file_exists($module_template)) {
            $template = $module_template;
        } elseif (file_exists($default_template)) {
            $template = $default_template;
        }

        return $template;
    }

    /**
     * Get the full price of the product
     *
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param bool $useTax
     *
     * @return float
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getFullPrice(int $idProduct, int $idProductAttribute = 0, bool $useTax = false): float
    {
        $product = new Product($idProduct, false, $this->context->language->id);
        $basePrice = (float)$product->price;

        $impact = 0;
        if ($idProductAttribute) {
            $combination = new Combination($idProductAttribute);
            $impact = (float)$combination->price;
        }

        return $basePrice + $impact;
    }

    /**
     * @param OrderId $orderId
     * @return Order
     * @throws OrderNotFoundException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getOrder(OrderId $orderId): Order
    {
        $order = new \Order($orderId->getValue());
        if (!\Validate::isLoadedObject($order)) {
            throw new OrderNotFoundException($orderId, \sprintf('Unable to create charging, order with id %d cannot be loaded', $orderId->getValue()));
        }

        return $order;
    }


}
