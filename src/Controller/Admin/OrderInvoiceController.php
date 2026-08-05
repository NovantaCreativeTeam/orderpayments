<?php

namespace Novanta\OrderPayment\Controller\Admin;

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;

class OrderInvoiceController extends PrestaShopAdminController
{
    public function indexAction()
    {
        return 'OrderInvoiceController - Index Action';
    }

    public function getAllAction(int $orderId)
    {
        return 'OrderInvoiceController - getAll Action';
    }

}
