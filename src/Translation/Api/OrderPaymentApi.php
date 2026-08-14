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
namespace Novanta\OrderPayment\Translation\Api;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Translation\Api\AbstractApi;

class OrderPaymentApi extends AbstractApi
{
    public function getTranslations()
    {
        return [
            'date' => $this->translator->trans('Date', [], 'Modules.Orderpayments.Admin'),
            'payment_date' => $this->translator->trans('Payment date', [], 'Modules.Orderpayments.Admin'),
            'payment_method' => $this->translator->trans('Payment method', [], 'Modules.Orderpayments.Admin'),
            'transaction_id' => $this->translator->trans('Transaction ID', [], 'Modules.Orderpayments.Admin'),
            'invoice_id' => $this->translator->trans('Invoice ID', [], 'Modules.Orderpayments.Admin'),
            'amount' => $this->translator->trans('Amount', [], 'Modules.Orderpayments.Admin'),
            'edit' => $this->translator->trans('Edit', [], 'Modules.Orderpayments.Admin'),
            'delete' => $this->translator->trans('Delete', [], 'Modules.Orderpayments.Admin'),
            'no_payments_found' => $this->translator->trans('No payments found', [], 'Modules.Orderpayments.Admin'),
            'create_payment' => $this->translator->trans('Create new payment', [], 'Modules.Orderpayments.Admin'),
            'edit_payment' => $this->translator->trans('Edit payment', [], 'Modules.Orderpayments.Admin'),
            'new_payment' => $this->translator->trans('New payment', [], 'Modules.Orderpayments.Admin'),
            'save' => $this->translator->trans('Save', [], 'Modules.Orderpayments.Admin'),
            'choose_file' => $this->translator->trans('Choose file', [], 'Modules.Orderpayments.Admin'),
            'payment_document' => $this->translator->trans('Payment document', [], 'Modules.Orderpayments.Admin'),
            'payment_saved' => $this->translator->trans('Payment saved successfully', [], 'Modules.Orderpayments.Notifications'),
            'invoice_saved' => $this->translator->trans('Invoice saved successfully', [], 'Modules.Orderpayments.Notifications'),
            'add_new_payment' => $this->translator->trans('Add new payment', [], 'Modules.Orderpayments.Admin'),
            'add_new_invoice' => $this->translator->trans('Add new invoice', [], 'Modules.Orderpayments.Admin'),
            'required' => $this->translator->trans('This field is required', [], 'Modules.Orderpayments.Notifications'),
            'correct_and_try_again' => $this->translator->trans('There are some errors, please correct them and try again', [], 'Modules.Orderpayments.Admin'),
            'delete_order_payment_confirm' => $this->translator->trans('Are you sure you want to delete this payment?', [], 'Modules.Orderpayments.Admin'),
            'invoice' => $this->translator->trans('Invoice', [], 'Modules.Orderpayments.Admin'),
            'employee' => $this->translator->trans('Employee', [], 'Modules.Orderpayments.Admin'),
            'invoice_number' => $this->translator->trans('Invoice number', [], 'Modules.Orderpayments.Admin'),
            'payment_term' => $this->translator->trans('Payment term', [], 'Modules.Orderpayments.Admin'),
            'total_to_pay' => $this->translator->trans('Total to pay', [], 'Modules.Orderpayments.Admin'),
            'total_paid' => $this->translator->trans('Total paid', [], 'Modules.Orderpayments.Admin'),
            'no_invoices_found' => $this->translator->trans('No invoices found', [], 'Modules.Orderpayments.Admin'),
            'create_invoice' => $this->translator->trans('Create new invoice', [], 'Modules.Orderpayments.Admin'),
            'new_invoice' => $this->translator->trans('New invoice', [], 'Modules.Orderpayments.Admin'),
            'edit_invoice' => $this->translator->trans('Edit invoice', [], 'Modules.Orderpayments.Admin'),
            'delete_order_invoice_confirm' => $this->translator->trans('Are you sure you want to delete this invoice?', [], 'Modules.Orderpayments.Admin'),
            'percentage' => $this->translator->trans('Percentage', [], 'Modules.Orderpayments.Admin'),
            'remaining' => $this->translator->trans('Remaining', [], 'Modules.Orderpayments.Admin'),
            'total_order' => $this->translator->trans('Total order', [], 'Modules.Orderpayments.Admin'),
            'download' => $this->translator->trans('Download', [], 'Modules.Orderpayments.Admin'),
            'invoice_date' => $this->translator->trans('Invoice date', [], 'Modules.Orderpayments.Admin'),
            'invoice_total' => $this->translator->trans('Invoice total', [], 'Modules.Orderpayments.Admin'),

            'card' => $this->translator->trans('card', [], 'Modules.Orderpayments.Admin'),
            'paypal' => $this->translator->trans('paypal', [], 'Modules.Orderpayments.Admin'),
            'wirepayment' => $this->translator->trans('wirepayment', [], 'Modules.Orderpayments.Admin'),
            'satispay' => $this->translator->trans('satispay', [], 'Modules.Orderpayments.Admin'),
            'cash' => $this->translator->trans('cash', [], 'Modules.Orderpayments.Admin'),
            'advance' => $this->translator->trans('advance', [], 'Modules.Orderpayments.Admin'),
            'down' => $this->translator->trans('down', [], 'Modules.Orderpayments.Admin'),
            'total' => $this->translator->trans('total', [], 'Modules.Orderpayments.Admin'),

            // Add other common translations if needed
            'are_sure' => $this->translator->trans('Are you sure?', [], 'Modules.Orderpayments.Admin'),
            'yes' => $this->translator->trans('Yes', [], 'Modules.Orderpayments.Admin'),
            'no' => $this->translator->trans('No', [], 'Modules.Orderpayments.Admin'),
            'cancel' => $this->translator->trans('Cancel', [], 'Modules.Orderpayments.Admin'),
            'error' => $this->translator->trans('Ops... Something went wrong, refresh page and try again', [], 'Modules.Orderpayments.Notifications'),
            'ok' => $this->translator->trans('Operation performed correctly', [], 'Modules.Orderpayments.Notifications'),
        ];
    }
}
