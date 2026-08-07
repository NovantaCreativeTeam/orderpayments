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
            'correct_and_try_again' => $this->translator->trans('There are some errors, please correct them and try again', [], 'Modules.Orderpayments.Admin'),
            'delete_order_payment_confirm' => $this->translator->trans('Are you sure you want to delete this payment?', [], 'Modules.Orderpayments.Admin'),
            'invoice' => $this->translator->trans('Invoice', [], 'Modules.Orderpayments.Admin'),
            'employee' => $this->translator->trans('Employee', [], 'Modules.Orderpayments.Admin'),

            
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
