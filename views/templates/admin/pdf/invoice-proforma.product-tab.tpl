
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

<table class="product" width="100%" cellpadding="4" cellspacing="0">
    <thead>
	<tr>
		<th class="product header small reference">{l s='Reference' d='Shop.Pdf' pdf='true'}</th>
		<th class="product header name">{l s='Product' d='Shop.Pdf' pdf='true'}</th>
		<th class="product header small qty">{l s='Qty' d='Shop.Pdf' pdf='true'}</th>
		<th class="product header-right small price">{l s='Full Price' d='Modules.OrderCharging.Pdf' pdf='true'} <br /> {l s='(Tax excl.)' d='Shop.Pdf' pdf='true'}</th>
        <th class="product header small total">{l s='Total' d='Shop.Pdf' pdf='true'}</th>
	</tr>
	</thead>
    {foreach $products as $product}
        {cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
		<tr class="product {$bgcolor_class}">
            <td class="product center reference">{$product.reference}</td>
            <td class="product name"><strong>{$product.name}</strong></td>
            <td class="product center qty">{$product.quantity}</td>
            <td class="product right price">{$product.full_price}</td>
            <td class="product center total">{$product.full_price}</td>
        </tr>
    {/foreach}
</table>

<br>

<table class="product" width="100%" cellpadding="4" cellspacing="0">
    <tr>
        <td width="50%"></td>
        <td width="50%">
            <table width="100%">
                <tr class="color_line_even">
                    <td class="grey" width="50%">
                        <strong>{l s='Total (Tax excl.)' d='Shop.Pdf' pdf='true'}</strong>
                    </td>
                    <td class="white" width="50%" align="right">
                        {$totals.tax_excluded}
                    </td>
                </tr>
                <tr class="color_line_odd">
                    <td class="grey" width="50%">
                        <strong>{l s='Total Taxes' d='Shop.Pdf' pdf='true'}</strong>
                    </td>
                    <td class="white" width="50%" align="right">
                        {$totals.total_taxes}
                    </td>
                </tr>
                <tr class="color_line_even">
                    <td class="grey" width="50%">
                        <strong>{l s='Total (Tax incl.)' d='Shop.Pdf' pdf='true'}</strong>
                    </td>
                    <td class="white" width="50%" align="right">
                        <strong>{$totals.tax_included}</strong>
                    </td>
                </tr>
                <tr class="color_line_even">
                    <td class="grey" width="50%">
                        <strong>{l s='Total to Pay' d='Shop.Pdf' pdf='true'}</strong>
                    </td>
                    <td class="white" width="50%" align="right">
                        <strong>{$totals.total_to_pay}</strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
