<?php

namespace Novanta\OrderPayment\Search\Filters;

use Novanta\OrderPayment\Grid\Definition\Factory\OrderInvoiceDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

final class OrderInvoiceFilters extends Filters
{
    protected $filterId = OrderInvoiceDefinitionFactory::GRID_ID;

    protected $defaults = [
        'limit' => 10,
        'offset' => 0,
        'orderBy' => 'id_order_invoice',
        'sortOrder' => 'desc',
        'filters' => [],
    ];

    public static function getDefaults()
    {
        return (new self())->defaults;
    }
}
