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
<table width="100%">
    <tr>
        <td>
            <table id="total-tab" class="subtotal" width="100%" cellpadding="4" cellspacing="0">
                <thead>
                <tr>
                    <th class="subtotal subtotal-item header small"></th>
                    <th class="subtotal subtotal-tax header center small"><strong>{l s='Tax' d='Modules.Orderpayments.Pdf' pdf='true'}</strong></th>
                    <th class="subtotal subtotal-total-tax-excl header center small"><strong>{l s='Tax excluded' d='Modules.Orderpayments.Pdf' pdf='true'}</strong></th>
                    <th class="subtotal subtotal-total-tax-incl header center small"><strong>{l s='Tax included' d='Modules.Orderpayments.Pdf' pdf='true'}</strong></th>
                </tr>
                </thead>
                <tbody>
                <tr class="subtotal color_line_even">
                    <td class="subtotal">{l s='Products' d='Modules.Orderpayments.Pdf' pdf='true'}</td>
                    <td class="subtotal center">{$totals.tax_rate|number_format:"0"} %</td>
                    <td class="subtotal right">{$totals.products_tax_excl}</td>
                    <td class="subtotal right">{if $totals.tax_rate == 0}---{else}{$totals.products_tax_incl}{/if}}</td>
                </tr>


                <tr class="subtotal color_line_odd">
                    <td class="subtotal">{l s='Shipping' d='Modules.Orderpayments.Pdf' pdf='true'}</td>
                    <td class="subtotal center">{$totals.tax_rate|number_format:"0"} %</td>
                    <td class="subtotal right">{$totals.shipping_tax_incl}</td>
                    <td class="subtotal right">{if $totals.tax_rate == 0}---{else}{$totals.shipping_tax_excl}{/if}</td>
                </tr>

                {if $totals.discount_amount != 0}
                    <tr class="subtotal color_line_even">
                        <td class="subtotal">{l s='Discounts' d='Modules.Orderpayments.Pdf' pdf='true'}</td>
                        <td class="subtotal center">{$totals.tax_rate|number_format:"0"} %</td>
                        <td class="subtotal right">-{$totals.discount_tax_excl}</td>
                        <td class="subtotal right">{if $totals.tax_rate == 0}---{else}-{$totals.discount_tax_incl}{/if}</td>
                    </tr>
                {/if}

                <tr class="subtotal color_line_odd">
                    <td class="subtotal grey"><strong>{l s='Total' d='Modules.Orderpayments.Pdf' pdf='true'}</strong></td>
                    <td class="subtotal grey center">{$totals.tax_rate|number_format:"0"} %</td>
                    <td class="subtotal grey right"><strong>{$totals.tax_excluded}</strong></td>
                    <td class="subtotal grey right"><strong>{if $totals.tax_rate == 0}---{else}{$totals.tax_included}{/if}</strong></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table class="totals" width="100%" cellpadding="4" cellspacing="0">
                <tr>
                    <td class="right total-item">{l s='Tax amount' d='Modules.Orderpayments.Pdf' pdf='true'}</td>
                    <td class="total-value">{if $totals.tax_rate == 0}---{else}{$totals.total_taxes}{/if}</td>
                </tr>
                <tr>
                    <td class="right total-item"><strong>{l s='Total to Pay' d='Modules.Orderpayments.Pdf' pdf='true'}</strong></td>
                    <td class="total-value total-to-pay"><strong>{$totals.total_to_pay}</strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
