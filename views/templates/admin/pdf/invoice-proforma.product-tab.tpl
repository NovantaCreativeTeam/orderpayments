
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
        <th class="product header center small reference">{l s='Reference' d='Modules.Orderpayments.Pdf' pdf='true'}</th>
        <th class="product header center small name">{l s='Product' d='Modules.Orderpayments.Pdf' pdf='true'}</th>
        <th class="product header center small qty">{l s='Qty' d='Modules.Orderpayments.Pdf' pdf='true'}</th>
        <th class="product header center small unit-price">{l s='Price' d='Modules.Orderpayments.Pdf' pdf='true'} <br /> <span class="small normal">{l s='(Tax excl.)' d='Modules.Orderpayments.Pdf' pdf='true'}</span></th>
        <th class="product header center small tax-rate">{l s='Tax' d='Modules.Orderpayments.Pdf' pdf='true'}</th>
        <th class="product header center small total">{l s='Total' d='Modules.Orderpayments.Pdf' pdf='true'}</th>
    </tr>
    </thead>
    {foreach $products as $product}
        {cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
        <tr class="product {$bgcolor_class}">
            <td class="product center reference">{$product.reference}</td>
            <td class="product name"><strong>{$product.name}</strong></td>
            <td class="product center qty">{$product.quantity}</td>
            <td class="product center unit-price">{$product.unit_price}</td>
            <td class="product right tax-rate">{$product.tax_rate} %</td>
            <td class="product center total">{$product.full_price}</td>
        </tr>
    {/foreach}
</table>