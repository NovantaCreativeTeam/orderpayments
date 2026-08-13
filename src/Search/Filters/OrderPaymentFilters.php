<?php

namespace Novanta\OrderPayment\Search\Filters;

use Novanta\OrderPayment\Grid\Definition\Factory\OrderPaymentDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

final class OrderPaymentFilters extends Filters
{
    protected $filterId = OrderPaymentDefinitionFactory::GRID_ID;

    protected $defaults = [
        'limit' => 10,
        'offset' => 0,
        'orderBy' => 'id_order_payment',
        'sortOrder' => 'desc',
        'filters' => [],
    ];

    public static function getDefaults()
    {
        return (new self())->defaults;
    }
}
