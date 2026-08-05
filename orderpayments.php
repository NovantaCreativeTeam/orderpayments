<?php
/**
 * 2007-2026 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use Novanta\OrderPayment\Adapter\Install\InstallerFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = dirname(__FILE__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class OrderPayments extends \Module
{
    public function __construct()
    {
        $this->name = 'orderpayments';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Novanta';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Multiple Order Payments', [], 'Modules.Orderpayment.Admin');
        $this->description = $this->trans('Manage multiple payments for a single order and generate proforma invoices.', [], 'Modules.Orderpayment.Admin');

        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '9.9.99'];
    }

    public function install(): bool
    {
        return parent::install() &&
            InstallerFactory::create()->install($this);
    }

    public function uninstall(): bool
    {
        return parent::uninstall() &&
            InstallerFactory::create()->uninstall();
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function hookDisplayAdminOrderMainBottom($params)
    {
        $orderId = (int)$params['id_order'];
        
        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $router = $this->get('router');

        $this->context->smarty->assign([
            'orderId' => $orderId,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/order_main_bottom.tpl');
    }

    public function hookActionAdminControllerSetMedia()
    {
        if ($this->context->controller->controller_name === 'AdminOrders') {
            $this->context->controller->addJS($this->_path.'views/js/order-payment.js');
            if (file_exists($this->local_path.'views/css/order-payment.css')) {
                $this->context->controller->addCSS($this->_path.'views/css/order-payment.css');
            }
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionOrderGridDefinitionModifier($params): void
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition->getColumns()->remove('total_paid_tax_incl');
        $definition->getColumns()->addAfter(
            'customer',
            (new \PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BadgeColumn('total_paid_tax_incl'))
                ->setName($this->trans('Total', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'total_paid_tax_incl',
                    'badge_type' => '',
                    'badge_type_field' => 'payment_status',
                ])
        );
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionOrderGridQueryBuilderModifier($params): void
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        $searchQueryBuilder->leftJoin('o', _DB_PREFIX_ . 'order_payment', 'op', 'o.reference = op.order_reference');
        $searchQueryBuilder->addSelect('IF(SUM(op.amount) >= o.total_paid_tax_incl, "success", IF(SUM(op.amount) > 0, "warning", "danger")) AS payment_status');
    }
}
