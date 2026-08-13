
{**
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
 *}

<table>
    <tr>
        <td colspan="6">
            <table id="supplier-info" style="padding: 5px 10px">
                <tbody>
                <tr>
                    <td>
                        <strong>{l s="Spett.le" d='Modules.Orderpayments.Pdf' pdf="true"}</strong><br/>
                        {if $customer.address.company}{$customer.address.company}{else}{$customer.firstname} {$customer.lastname}{/if}
                        <br/>
                        {$customer.address.address1} {$customer.address.address2}<br/>
                        {$customer.address.postcode} {$customer.address.city}<br/>
                        {$customer.address.state_iso} {$customer.address.country}<br/>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
        <td colspan="6"></td>
    </tr>

    <tr>
        <td colspan="12" height="20">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="12">
            <table cellpadding="5" border="1">
                <tr>
                    <td colspan="4">
                        <h3>{l s="Proforma Invoice" d='Modules.Orderpayments.Pdf' pdf="true"}</h3>
                    </td>
                    <td colspan="4">{l s="Document Number" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$invoice.number}
                    </td>
                    <td colspan="4">{l s="Document Date" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$invoice.date_add}
                    </td>
                    <td colspan="4">{l s="Order reference" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$order.reference}
                    </td>
                </tr>
                <tr>
                    <td colspan="3">{l s="Vat Number" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$customer.address.vat_number}</td>
                    <td colspan="3">{l s="Dni" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$customer.address.dni}</td>
                    <td colspan="3">{l s="Phone" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$customer.address.phone}</td>
                    <td colspan="3">{l s="Mobile Phone" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$customer.address.phone_mobile}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="12">
            <table cellpadding="5" border="1">
                <tr>
                    <td>{l s="Payment Method" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$invoice.payment_method}</td>
                    <td>{l s="Payment Term" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                        {$invoice.payment_term} {if $invoice.payment_term !== 'total'}- {$invoice.amount_type} {$invoice.amount}{/if}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {if $invoice.payment_method == 'wirepayment'}
        <tr>
            <td colspan="12">
                <table cellpadding="5" border="1">
                    <tr>
                        <td>{l s="Bankwire Owner" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                            {$invoice.bankwire_owner}</td>
                        <td>{l s="Bankwire Address" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                            {$invoice.bankwire_address}</td>
                        <td>{l s="Bankwire Details" d='Modules.Orderpayments.Pdf' pdf="true"}<br/>
                            {$invoice.bankwire_details}</td>
                    </tr>
                </table>
            </td>

        </tr>
    {/if}
</table>